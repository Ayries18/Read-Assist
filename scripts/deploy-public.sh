#!/usr/bin/env bash
#
# deploy-public.sh
# ----------------------------------------------------------------------------
# Sinkronkan SELURUH isi public/ ke document root hosting cPanel.
#
# Kenapa script ini ada
# ---------------------
# Di cPanel, document root (~/<user>/public_html) berada DI LUAR folder aplikasi,
# sehingga public/ tidak otomatis tersaji. Document root juga tidak boleh
# diganti symlink penuh, karena .well-known/acme-challenge, php.ini, dan
# error_log hanya ada di sana dan dikelola cPanel.
#
# Akibatnya public/ dan public_html bisa berbeda tanpa disadari. Script ini
# menutup celah tersebut secara deterministik, lalu memverifikasi hasilnya.
#
# Prinsip
# -------
# 1. Deletion berbasis MANIFEST, bukan "hapus yang tidak ada di repo". Hanya
#    nama yang tercatat di manifest sebelumnya yang boleh dihapus, jadi
#    berkas milik cPanel tidak pernah tersentuh.
# 2. Berkas milik cPanel dilindungi dua lapis: tidak pernah masuk daftar
#    salin, dan salin/pemindahan menolak apa pun yang namanya protected.
# 3. Manifest ditulis setelah sinkron, bukan sebelum.
# 4. Deploy dianggap gagal bila health check tidak lolos.
#
# Pakai
# -----
#   bash scripts/deploy-public.sh              # sinkronkan + verifikasi + health check
#   bash scripts/deploy-public.sh --dry-run    # rencana saja, tidak menulis apa pun
#   bash scripts/deploy-public.sh --verify     # verifikasi tanpa menulis
#   bash scripts/deploy-public.sh --rollback latest
#   bash scripts/deploy-public.sh --list-backups
#
# Keluar 0 bila sukses. Selain itu keluar bukan 0 dengan pesan jelas.
# ----------------------------------------------------------------------------

set -Eeuo pipefail

# --- Konfigurasi -------------------------------------------------------------

APP_DIR="${APP_DIR:-$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)}"
DOC_DIR="${DOC_DIR:-/home/cp2ujcb5545/public_html}"
HEALTH_BASE_URL="${HEALTH_BASE_URL:-https://readassist.web-id.id}"

SRC="$APP_DIR/public"
MANIFEST="$DOC_DIR/.deploy-public.manifest.json"
BACKUP_ROOT="$DOC_DIR/.deploy-backups"

DRY_RUN=0
VERIFY_ONLY=0
ROLLBACK_ARG=""
LIST_BACKUPS=0

# Halaman yang wajib hidup setelah deploy.
HEALTH_PATHS=("/" "/login" "/register" "/katalog-audio")

# Milik cPanel atau milik verifikasi domain. TIDAK PERNAH disalin, ditulis,
# dipindahkan, atau dihapus oleh script ini.
PROTECTED_NAMES=(
  ".well-known"
  "php.ini"
  "error_log"
  "Read-Assist"
  ".deploy-backups"
  ".deploy-public.manifest"
  ".deploy-public.manifest.json"
)

# Di document root harus berupa symlink ke folder aplikasi, bukan salinan.
SYMLINK_NAMES=("build" "storage" "favicon.ico")

# Berkas yang isinya harus identik antara repo dan document root.
CHECK_FILES=(
  "index.php" ".htaccess" ".user.ini" "robots.txt"
  "favicon.png" "favicon.svg"
  "logo.png" "logo-horizontal.png" "logo-horizontal.svg" "logo-horizontal-hc.svg"
  "manifest.json" "sw.js"
)

PHP_BIN=""
for c in php /opt/alt/php85/usr/bin/php /usr/local/bin/php /usr/bin/php; do
  if command -v "$c" >/dev/null 2>&1; then PHP_BIN="$(command -v "$c")"; break; fi
done

# --- Utilitas ----------------------------------------------------------------

RED=''; GRN=''; YLW=''; DIM=''; BLD=''; RST=''
if [ -t 1 ]; then
  RED=$'\033[31m'; GRN=$'\033[32m'; YLW=$'\033[33m'; DIM=$'\033[2m'; BLD=$'\033[1m'; RST=$'\033[0m'
fi

head1() { printf '\n%s%s%s\n' "$BLD" "$*" "$RST"; }
ok()    { printf '  %sOK%s   %s\n'   "$GRN" "$RST" "$*"; }
warn()  { printf '  %sWARN%s %s\n'   "$YLW" "$RST" "$*"; }
err()   { printf '  %sFAIL%s %s\n'   "$RED" "$RST" "$*" >&2; }
step()  { printf '  %s->%s   %s\n'   "$DIM" "$RST" "$*"; }
note()  { printf '       %s%s%s\n'   "$DIM" "$*" "$RST"; }
die()   { err "$*"; exit 1; }

WORK=""
cleanup() { [ -n "$WORK" ] && rm -rf -- "$WORK"; return 0; }

on_err() {
  local code=$? line=${1:-?}
  err "Berhenti di baris $line (kode $code)."
  printf '  Sinkronisasi mungkin setengah jadi.\n' >&2
  printf '  Periksa: bash scripts/deploy-public.sh --verify\n' >&2
  printf '  Pulihkan: bash scripts/deploy-public.sh --rollback latest\n' >&2
  cleanup
  exit "$code"
}
trap 'on_err $LINENO' ERR
trap 'cleanup' EXIT

# True bila nama darf masuk daftar protected.
is_protected() {
  local n="$1" p
  for p in "${PROTECTED_NAMES[@]}"; do [ "$n" = "$p" ] && return 0; done
  return 1
}

is_symlink_managed() {
  local n="$1" s
  for s in "${SYMLINK_NAMES[@]}"; do [ "$n" = "$s" ] && return 0; done
  return 1
}

# Gerbang wajib sebelum operasi file apa pun. Dipanggil dari dalam loop salin
# juga, supaya perubahan logika lain tidak bisa melewati proteksi ini.
guard_not_protected() {
  local n="$1" where="$2"
  if is_protected "$n"; then
    err "$where mencoba menyentuh berkas protected: '$n'"
    printf '  Nama protected: %s\n' "${PROTECTED_NAMES[*]}" >&2
    cleanup
    exit 1
  fi
}

# --- Argumentasi -------------------------------------------------------------

MODE="sync"
ROLLBACK_ARG=""

for arg in "$@"; do
  case "$MODE" in
    expect-rollback)
      ROLLBACK_ARG="$arg"
      MODE="sync"
      ;;
    *)
      case "$arg" in
        --dry-run)      DRY_RUN=1 ;;
        --verify)       VERIFY_ONLY=1 ;;
        --list-backups) LIST_BACKUPS=1 ;;
        --rollback)     MODE="expect-rollback" ;;
        -h|--help)      sed -n '3,/^set -Eeuo/p' "${BASH_SOURCE[0]}" | sed 's/^#\{1,\} \{0,1\}//; $d'; exit 0 ;;
        *)              die "Argumen tidak dikenal: '$arg'" ;;
      esac
      ;;
  esac
done

if [ "$MODE" = "expect-rollback" ]; then
  die "--rollback butuh argumen, contoh: --rollback latest"
fi

# --- Mode khusus: daftar backup & rollback ------------------------------------

if [ "$LIST_BACKUPS" -eq 1 ]; then
  head1 "Riwayat backup"
  if [ -d "$BACKUP_ROOT" ]; then
    find "$BACKUP_ROOT" -mindepth 1 -maxdepth 1 -type d -printf '%f\n' 2>/dev/null \
      | sort -r | while read -r d; do
        printf '  %-18s %s berkas\n' "$d" "$(find "$BACKUP_ROOT/$d" -mindepth 1 -maxdepth 1 2>/dev/null | wc -l | tr -d ' ')"
      done
  else
    note "belum ada backup"
  fi
  exit 0
fi

if [ -n "$ROLLBACK_ARG" ]; then
  [ "$ROLLBACK_ARG" = "latest" ] || die "argumen --rollback yang didukung hanya 'latest'"

  WORK="$(mktemp -d "${TMPDIR:-/tmp}/deploy-public.XXXXXX")" || die "gagal membuat direktori sementara"

  head1 "Rollback"
  [ -f "$MANIFEST" ] || die "manifest tidak ditemukan: $MANIFEST"
  [ -n "$PHP_BIN" ] || die "php tidak ditemukan, tidak bisa membaca manifest"

  BAK_PATH="$("$PHP_BIN" -r '
    $j = json_decode(file_get_contents($argv[1]), true);
    echo $j["backup_path"] ?? "";
  ' "$MANIFEST")"

  [ -n "$BAK_PATH" ] || die "manifest tidak memuat backup_path; tidak ada yang bisa dipulihkan"
  [ -d "$BAK_PATH" ] || die "direktori backup tidak ditemukan: $BAK_PATH"

  ok "manifest : $MANIFEST"
  ok "commit   : $("$PHP_BIN" -r '$j=json_decode(file_get_contents($argv[1]),true); echo $j["commit"] ?? "?";' "$MANIFEST")"
  ok "backup   : $BAK_PATH"

  # Nama yang dipulihkan. Yang ada di backup dikembalikan, yang tidak ada di
  # backup berarti file itu BARU dibuat deploy sebelumnya, jadi dihapus.
  RESTORED=0; PURGED=0
  for n in "$BAK_PATH"/* "$BAK_PATH"/.[!.]*; do
    [ -e "$n" ] || continue
    base="$(basename "$n")"
    guard_not_protected "$base" "Rollback"
    if [ -L "$n" ] || [ -d "$n" ]; then
      rm -rf -- "${DOC_DIR:?}/$base"
      cp -a -- "$n" "${DOC_DIR:?}/$base" || die "gagal memulihkan: $base"
    else
      cp -a -- "$n" "${DOC_DIR:?}/$base" || die "gagal memulihkan: $base"
    fi
    step "dipulihkan: $base"
    RESTORED=$((RESTORED+1))
  done

  # Hapus entri yang tercatat di manifest sebagai 'added' dan tidak ada di backup.
  for n in $("$PHP_BIN" -r '
    $j = json_decode(file_get_contents($argv[1]), true);
    echo implode("\n", $j["added"] ?? []);
  ' "$MANIFEST"); do
    [ -n "$n" ] || continue
    guard_not_protected "$n" "Rollback"
    if [ -e "$DOC_DIR/$n" ] || [ -L "$DOC_DIR/$n" ]; then
      rm -rf -- "${DOC_DIR:?}/$n"
      step "dihapus (baru di deploy sebelumnya): $n"
      PURGED=$((PURGED+1))
    fi
  done

  find "$DOC_DIR" -type d -not -path "*/.well-known*" -not -path "*/.deploy-backups*" \
    -exec chmod 755 {} + 2>/dev/null || true
  find "$DOC_DIR" -type f -not -path "*/.well-known*" -not -path "*/.deploy-backups*" \
    -exec chmod 644 {} + 2>/dev/null || true

  ok "dipulihkan $RESTORED berkas, dihapus $PURGED berkas baru"
  printf '  CATATAN: ini mengembalikan aset statis saja.\n'
  printf '  Bila deploy gagal karena perubahan kode, jalankan: git revert <commit>\n'
  exit 0
fi

# --- 1. Validasi -------------------------------------------------------------

head1 "1. Validasi"

[ -d "$SRC" ]        || die "Folder sumber tidak ditemukan: $SRC"
[ -d "$DOC_DIR" ]    || die "Document root tidak ditemukan: $DOC_DIR"
[ -w "$DOC_DIR" ]    || die "Document root tidak bisa ditulis: $DOC_DIR"
[ -n "$PHP_BIN" ]    || die "php tidak ditemukan; manifest dan health check butuh PHP"
[ -f "$SRC/index.php" ]  || die "public/index.php hilang; dibatalkan agar document root tidak kehilangan front controller."
[ -f "$SRC/.htaccess" ]  || die "public/.htaccess hilang; dibatalkan."
[ -f "$SRC/robots.txt" ] || die "public/robots.txt hilang; dibatalkan."

# Document root harus di dalam home dan bukan direktori sistem.
case "$DOC_DIR" in
  /|/home|/usr|/etc|/var|/root|"$HOME") die "Document root mencurigakan: $DOC_DIR" ;;
esac
case "$DOC_DIR" in
  "$HOME"/*) : ;;
  *) die "Document root harus berada di dalam \$HOME; got: $DOC_DIR" ;;
esac

DOC_REAL="$(readlink -f "$DOC_DIR")"
[ -d "$DOC_REAL" ] || die "Document root tidak bisa diresolve: $DOC_REAL"

ok "sumber       : $SRC"
ok "document root: $DOC_DIR (real: $DOC_REAL)"
ok "user         : $(id -un)"
ok "php          : $PHP_BIN"
ok "health base  : $HEALTH_BASE_URL"
if command -v rsync >/dev/null 2>&1; then
  SYNC_ENGINE="rsync"
  ok "sync engine : rsync ($(rsync --version 2>/dev/null | head -1 | awk '{print $3}'))"
else
  SYNC_ENGINE="cp"
  ok "sync engine : cp -a (rsync tidak terpasang)"
fi

# --- 2. Pre-flight proteksi cPanel (Task 4) ----------------------------------

head1 "2. Proteksi berkas cPanel"

# Nama di document root yang dipastikan milik cPanel dan harus bertahan.
PROTECTED_PRESENT=()
for n in "${PROTECTED_NAMES[@]}"; do
  if [ -e "$DOC_DIR/$n" ] || [ -L "$DOC_DIR/$n" ]; then
    PROTECTED_PRESENT+=("$n")
  fi
done

# Wajib ada untuk hosting cPanel. Bila hilang, jangan lanjut: mungkin document
# root salah, dan melanjutkan berisiko merusak renewal SSL.
[ -d "$DOC_DIR/.well-known" ] || die ".well-known tidak ada di document root. Hentikan deploy: cek bahwa DOC_DIR benar."
[ -f "$DOC_DIR/php.ini" ]      || die "php.ini tidak ada di document root. Hentikan deploy: cek bahwa DOC_DIR benar."

ok ".well-known ada dan akan dilindungi"
ok "php.ini ada dan akan dilindungi"
if [ -e "$DOC_DIR/error_log" ]; then ok "error_log ada dan akan dilindungi"; fi
note "protected: ${PROTECTED_NAMES[*]}"

# Tolak bila public/ sendiri memuat nama protected; itu berarti repo dan
# document root akan bertabrakan.
for n in "${PROTECTED_NAMES[@]}"; do
  [ -e "$SRC/$n" ] && die "public/ memuat '$n' yang ada di daftar protected. Hapus dari repo."
done
ok "tidak ada nama protected di public/"

# --- 3. Rencanakan -----------------------------------------------------------

head1 "3. Rencana"

WORK="$(mktemp -d "${TMPDIR:-/tmp}/deploy-public.XXXXXX")" || die "gagal membuat direktori sementara"

# Daftar entri public/ yang akan disalin: buang protected dan symlink yang
# dippedelihara terpisah.
ENTRIES_FILE="$WORK/entries"
find "$SRC" -mindepth 1 -maxdepth 1 -printf '%f\n' 2>/dev/null | sort | while read -r n; do
  [ -n "$n" ] || continue
  is_protected "$n" && continue
  is_symlink_managed "$n" && continue
  printf '%s\n' "$n"
done > "$ENTRIES_FILE"

SRC_ENTRIES=()
while read -r n; do [ -n "$n" ] && SRC_ENTRIES+=("$n"); done < "$ENTRIES_FILE"

ADDED=(); CHANGED=(); UNCHANGED=()

for name in "${SRC_ENTRIES[@]}"; do
  s="$SRC/$name"; d="$DOC_DIR/$name"
  if [ ! -e "$d" ] && [ ! -L "$d" ]; then
    ADDED+=("$name")
  elif [ -d "$d" ] && [ -d "$s" ]; then
    if diff -qr "$s" "$d" >/dev/null 2>&1; then UNCHANGED+=("$name"); else CHANGED+=("$name"); fi
  elif [ -L "$d" ]; then
    if cmp -s "$s" "$d" 2>/dev/null; then UNCHANGED+=("$name"); else CHANGED+=("$name"); fi
  elif [ -f "$d" ] && [ -f "$s" ] && cmp -s "$s" "$d"; then
    UNCHANGED+=("$name")
  else
    CHANGED+=("$name")
  fi
done

# Symlink yang perlu dibuat atau diarahkan ulang.
SYMLINK_FIX=(); SYMLINK_OK=()
for name in "${SYMLINK_NAMES[@]}"; do
  target="$APP_DIR/public/$name"
  link="$DOC_DIR/$name"
  if [ ! -e "$target" ]; then
    warn "symlink dilewati, target tidak ada: public/$name"
    continue
  fi
  if [ -L "$link" ] && [ "$(readlink "$link")" = "$target" ] && [ -e "$link" ]; then
    SYMLINK_OK+=("$name")
  else
    SYMLINK_FIX+=("$name")
  fi
done

# Nama di manifest sebelumnya yang sudah hilang dari public/ dan masih di docroot.
# Manifest dibaca lewat file, bukan process substitution, karena /dev/fd tidak
# selalu tersedia pada shell hosting cPanel.
REMOVED=()
PREV_ENTRIES="$WORK/prev-entries"
if [ -f "$MANIFEST" ]; then
  "$PHP_BIN" -r '
    $j = json_decode(file_get_contents($argv[1]), true);
    echo implode("\n", $j["synced_entries"] ?? []);
  ' "$MANIFEST" > "$PREV_ENTRIES" 2>/dev/null || : > "$PREV_ENTRIES"
else
  : > "$PREV_ENTRIES"
fi

while read -r n; do
  [ -n "$n" ] || continue
  is_protected "$n" && continue
  is_symlink_managed "$n" && continue
  [ -e "$SRC/$n" ] && continue
  { [ -e "$DOC_DIR/$n" ] || [ -L "$DOC_DIR/$n" ]; } || continue
  REMOVED+=("$n")
done < "$PREV_ENTRIES"

printf '  %sfile baru%s        : %d\n' "$BLD" "$RST" "${#ADDED[@]}"
for n in "${ADDED[@]}";    do printf '    + %s\n' "$n"; done
printf '  %sfile berubah%s     : %d\n' "$BLD" "$RST" "${#CHANGED[@]}"
for n in "${CHANGED[@]}";  do printf '    ~ %s\n' "$n"; done
printf '  %sfile dihapus%s     : %d\n' "$YLW" "$RST" "${#REMOVED[@]}"
for n in "${REMOVED[@]}";  do printf '    - %s\n' "$n"; done
printf '  %ssymlink diperbaiki%s: %d\n' "$BLD" "$RST" "${#SYMLINK_FIX[@]}"
for n in "${SYMLINK_FIX[@]}"; do printf '    > %s -> %s\n' "$n" "$APP_DIR/public/$n"; done
printf '  symlink sudah benar : %d (%s)\n' "${#SYMLINK_OK[@]}" "${SYMLINK_OK[*]:-tidak ada}"
printf '  tidak berubah       : %d\n' "${#UNCHANGED[@]}"
for n in "${UNCHANGED[@]}"; do printf '    = %s\n' "$DIM$n$RST"; done

TOTAL_CHANGE=$(( ${#ADDED[@]} + ${#CHANGED[@]} + ${#REMOVED[@]} + ${#SYMLINK_FIX[@]} ))
printf '  %stotal perubahan    : %d%s\n' "$BLD" "$TOTAL_CHANGE" "$RST"

if [ "$DRY_RUN" -eq 1 ]; then
  head1 "Dry run selesai, tidak ada yang ditulis"
  exit 0
fi

# --- 4. Sinkronisasi ---------------------------------------------------------

if [ "$VERIFY_ONLY" -eq 1 ]; then
  head1 "4. Verifikasi (mode baca)"
else
  head1 "4. Backup"
  if [ "$TOTAL_CHANGE" -gt 0 ]; then
    BAK="$BACKUP_ROOT/$(date +%Y%m%d-%H%M%S)"
    mkdir -p "$BAK" || die "gagal membuat $BAK"
    for n in "${CHANGED[@]}" "${REMOVED[@]}" "${SYMLINK_FIX[@]}"; do
      [ -e "$DOC_DIR/$n" ] || [ -L "$DOC_DIR/$n" ] || continue
      guard_not_protected "$n" "Backup"
      cp -a -- "$DOC_DIR/$n" "$BAK/" 2>/dev/null || warn "gagal backup: $n"
    done
    ok "backup: $BAK"
  else
    BAK=""
    step "tidak ada perubahan, backup dilewati"
  fi

  head1 "5. Sinkronisasi"

  for n in "${REMOVED[@]}"; do
    guard_not_protected "$n" "Penghapusan"
    rm -rf -- "${DOC_DIR:?}/$n"
    step "dihapus: $n"
  done

  for name in "${SRC_ENTRIES[@]}"; do
    guard_not_protected "$name" "Penyalinan"
    s="$SRC/$name"; d="$DOC_DIR/$name"
    if [ "$SYNC_ENGINE" = "rsync" ]; then
      rsync -a --checksum -- "$s" "$d" || die "rsync gagal pada: $name"
    else
      rm -rf -- "${d:?}"
      cp -a -- "$s" "$d" || die "cp gagal pada: $name"
    fi
  done
  ok "disalin ${#SRC_ENTRIES[@]} entri"

  for name in "${SYMLINK_FIX[@]}"; do
    guard_not_protected "$name" "Symlink"
    target="$APP_DIR/public/$name"; link="$DOC_DIR/$name"
    { [ -e "$link" ] || [ -L "$link" ]; } && rm -rf -- "$link"
    ln -s "$target" "$link" || die "gagal membuat symlink: $name"
    step "symlink: $name -> $target"
  done
  ok "symlink: ${#SYMLINK_FIX[@]} diperbaiki, ${#SYMLINK_OK[@]} sudah benar"

  find "$DOC_DIR" -type d -not -path "*/.well-known*" -not -path "*/.deploy-backups*" \
    -exec chmod 755 {} + 2>/dev/null || true
  find "$DOC_DIR" -type f -not -path "*/.well-known*" -not -path "*/.deploy-backups*" \
    -exec chmod 644 {} + 2>/dev/null || true
  ok "permission: direktori 755, file 644"
fi

# --- 6. Verifikasi -----------------------------------------------------------

head1 "6. Verifikasi sinkronisasi"

FAILURES=0

for name in "${CHECK_FILES[@]}"; do
  s="$SRC/$name"; d="$DOC_DIR/$name"
  if [ -L "$d" ]; then
    if cmp -s "$s" "$d" 2>/dev/null; then ok "$name (symlink, isi cocok)"
    else err "$name: isi symlink berbeda"; FAILURES=$((FAILURES+1)); fi
  elif [ -f "$d" ] && cmp -s "$s" "$d"; then
    ok "$name ($(stat -c %s "$d") byte)"
  else
    err "$name: tidak ada atau berbeda"; FAILURES=$((FAILURES+1))
  fi
done

for name in "${SYMLINK_NAMES[@]}"; do
  link="$DOC_DIR/$name"
  if [ -L "$link" ] && [ -e "$link" ]; then
    ok "symlink $name valid -> $(readlink "$link")"
  else
    err "symlink $name hilang atau rusak"; FAILURES=$((FAILURES+1))
  fi
done

# Aset hasil build Vite. Manifest ditulis pretty-print, jadi pola grep harus
# mentoleransi spasi setelah tanda titik dua.
if [ -f "$DOC_DIR/build/manifest.json" ]; then
  ok "build/manifest.json ada"
  ASSET_LIST="$WORK/assets"
  grep -o '"file"[[:space:]]*:[[:space:]]*"[^"]*"' "$DOC_DIR/build/manifest.json" \
    | sed 's/.*"file"[[:space:]]*:[[:space:]]*"//; s/"$//' | sort -u > "$ASSET_LIST"
  MISSING=0; TOTAL=0
  while read -r rel; do
    [ -n "$rel" ] || continue
    TOTAL=$((TOTAL+1))
    [ -f "$DOC_DIR/build/$rel" ] || { err "build/$rel hilang"; MISSING=$((MISSING+1)); }
  done < "$ASSET_LIST"
  if [ "$MISSING" -eq 0 ]; then
    ok "seluruh $TOTAL aset rujukan manifest ada"
  else
    err "$MISSING dari $TOTAL aset hilang; jalankan npm run build lalu unggah public/build"
    FAILURES=$((FAILURES+1))
  fi
else
  err "build/manifest.json hilang; aset front-end tidak termuat"
  FAILURES=$((FAILURES+1))
fi

# Protected harus masih utuh SETELAH semua operasi.
head1 "7. Proteksi cPanel pasca-sinkronisasi"
for n in ".well-known" "php.ini"; do
  if [ -e "$DOC_DIR/$n" ]; then
    ok "$n masih ada"
  else
    err "$n HILANG!_RESTORE SEGERA dari backup cPanel"
    FAILURES=$((FAILURES+1))
  fi
done
[ -e "$DOC_DIR/error_log" ] && ok "error_log masih ada"

# --- 8. Health check ---------------------------------------------------------

head1 "8. Health check"

cat > "$WORK/health.php" <<'PHPEOF'
<?php
// Health check memakai cURL. file_get_contents untuk https:// gagal di PHP CLI
// beberapa host cPanel (allow_url_fopen On, tetapi ssl_verify tidak bisa
// menemukan CA bundle), sementara cURL di host yang sama berfungsi. Karena itu
// cURL jadi jalur utama dan file_get_contents hanya cadangan.
$base = rtrim($argv[1], '/');
$paths = array_slice($argv, 2);
$fail = 0;

$errorPattern = '/(Fatal error|Parse error|Uncaught|Whoops, looks like'
    .'|Laravel\\\\Exceptions|Read-Assist: folder aplikasi)/i';

foreach ($paths as $path) {
    $code = '000';
    $body = false;
    $note = '';

    if (function_exists('curl_init')) {
        $ch = curl_init($base.$path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => 0,
            CURLOPT_USERAGENT      => 'readassist-deploy-healthcheck',
        ]);
        $body = curl_exec($ch);
        if ($body === false) {
            $note = ' curl: '.curl_error($ch);
        } else {
            $code = (string) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => [
            'method'          => 'GET',
            'timeout'         => 25,
            'ignore_errors'   => true,
            'follow_location' => 0,
        ]]);
        $body = @file_get_contents($base.$path, false, $ctx);
        if (isset($http_response_header[0])
            && preg_match('#^HTTP/\S+\s+(\d{3})#', $http_response_header[0], $m)) {
            $code = $m[1];
        }
    }

    if (is_string($body) && preg_match($errorPattern, $body, $m2)) {
        $note = ' ISI: '.trim(substr($m2[0], 0, 40));
    }

    $good = ($code === '200' && $note === '');
    printf("  %s %-16s %s%s\n", $good ? 'OK  ' : 'FAIL', $path, $code, $note);
    if (! $good) {
        $fail++;
    }
}

exit($fail === 0 ? 0 : 1);
PHPEOF

if HEALTH_OUT="$("$PHP_BIN" "$WORK/health.php" "$HEALTH_BASE_URL" "${HEALTH_PATHS[@]}" 2>&1)"; then
  printf '%s\n' "$HEALTH_OUT"
  ok "semua health check lolos"
  HEALTH_OK=1
else
  printf '%s\n' "$HEALTH_OUT"
  HEALTH_OK=0
  err "health check gagal; deploy dianggap GAGAL"
  printf '  Periksa: tail -50 %s\n' "$DOC_DIR/error_log"
  printf '  Pulihkan: bash scripts/deploy-public.sh --rollback latest\n'
fi

# --- 9. Manifest JSON --------------------------------------------------------

if [ "$VERIFY_ONLY" -eq 0 ]; then
  head1 "9. Manifest deployment"

  # Commit diambil langsung dari git, bukan lewat subproses PHP, supaya tidak
  # bergantung pada PATH yang diwarisi PHP dan tidak melempar exit code 255.
  COMMIT="$(git -C "$APP_DIR" rev-parse HEAD 2>/dev/null || true)"
  [ -n "$COMMIT" ] || COMMIT="unknown"
  [ -n "$COMMIT" ] || warn "commit tidak terbaca; deploy tetap dilanjutkan"

  cat > "$WORK/manifest.php" <<'PHPEOF'
<?php
$out    = $argv[1];
$src    = $argv[2];
$doc    = $argv[3];
$commit = $argv[4];
$bak    = $argv[5];
$health = $argv[6] === '1';
$engine = $argv[7];

// Nama dikirim sebagai satu string dipisah baris baru. String kosong harus
// dibuang, kalau tidak akan muncul sebagai entri array berisi string kosong.
$list = function (string $s): array {
    $parts = $s === '' ? [] : explode("\n", $s);

    return array_values(array_filter($parts, fn ($v) => $v !== ''));
};

$checksums = [];
foreach (['index.php', '.htaccess', '.user.ini', 'robots.txt', 'manifest.json', 'sw.js',
          'favicon.png', 'favicon.svg', 'logo.png', 'logo-horizontal.png',
          'logo-horizontal.svg', 'logo-horizontal-hc.svg'] as $f) {
    $p = $doc.'/'.$f;
    if (is_file($p)) { $checksums[$f] = hash_file('sha256', $p); }
}

$symlinks = [];
foreach (['build', 'storage', 'favicon.ico'] as $n) {
    $p = $doc.'/'.$n;
    $symlinks[$n] = [
        'is_symlink' => is_link($p),
        'target'     => is_link($p) ? readlink($p) : null,
        'valid'      => is_link($p) && file_exists($p),
    ];
}

$entries = array_values(array_diff(scandir($src), ['.', '..']));
sort($entries);
$entries = array_values(array_filter($entries, function ($n) {
    return ! in_array($n, ['build', 'storage', 'favicon.ico', '.well-known',
                           'php.ini', 'error_log', 'Read-Assist',
                           '.deploy-backups', '.deploy-public.manifest.json'], true);
}));

$data = [
    'schema'            => 1,
    'commit'            => $commit,
    'deployed_at'       => date('c'),
    'app_dir'           => $src,
    'doc_dir'           => $doc,
    'sync_engine'       => $engine,
    'changed_count'     => (int) ($argv[8] ?? 0),
    'added'             => $list($argv[9] ?? ''),
    'changed'           => $list($argv[10] ?? ''),
    'removed'           => $list($argv[11] ?? ''),
    'synced_entries'    => $entries,
    'symlinks_verified' => $symlinks,
    'protected_present' => array_values(array_filter(
        ['.well-known', 'php.ini', 'error_log'],
        fn ($n) => file_exists($doc.'/'.$n)
    )),
    'checksums_sha256'  => $checksums,
    'backup_path'       => $bak !== '' ? $bak : null,
    'health_check_ok'   => $health,
];

file_put_contents($out, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
PHPEOF

  ADDED_CSV=$(printf '%s\n' "${ADDED[@]:-}")
  CHANGED_CSV=$(printf '%s\n' "${CHANGED[@]:-}")
  REMOVED_CSV=$(printf '%s\n' "${REMOVED[@]:-}")

  "$PHP_BIN" "$WORK/manifest.php" "$MANIFEST" "$SRC" "$DOC_REAL" "$COMMIT" "${BAK:-}" \
    "$HEALTH_OK" "$SYNC_ENGINE" "$TOTAL_CHANGE" \
    "$ADDED_CSV" "$CHANGED_CSV" "$REMOVED_CSV" || die "gagal menulis manifest"

  chmod 644 "$MANIFEST"
  ok "manifest : $MANIFEST"
  ok "commit   : $COMMIT"
  note "sha-256 dicatat untuk seluruh berkas yang diverifikasi byte-per-byte"
fi

# --- Ringkasan ---------------------------------------------------------------

head1 "Ringkasan"
if [ "$FAILURES" -ne 0 ]; then
  err "verifikasi sinkronisasi gagal: $FAILURES masalah"
  exit 1
fi
if [ "$HEALTH_OK" -ne 1 ]; then
  err "deploy GAGAL karena health check tidak lolos"
  exit 1
fi

ok "public/ dan public_html SINKRON"
ok "berkas cPanel terlindungi"
ok "health check lolos"
exit 0
