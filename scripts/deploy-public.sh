#!/usr/bin/env bash
#
# deploy-public.sh
# ----------------------------------------------------------------------------
# Sinkronkan SELURUH isi public/ ke document root hosting cPanel.
#
# Latar belakang
# --------------
# Di cPanel, document root (~/<user>/public_html) TIDAK berada di dalam folder
# aplikasi, sehingga public/ tidak otomatis tersaji. Dua konsekuensinya:
#
#   1. public_html tidak bisa diganti jadi symlink penuh tanpa merusak
#      renewal SSL (.well-known/acme-challenge), php.ini, dan error_log
#      yang dikelola cPanel dan hanya ada di document root.
#   2. public/ dan public_html mudah berbeda tanpa disadari.
#
# Script ini menutup celah nomor 2 dengan menyalin isi public/ secara
# deterministik, lalu memverifikasi hasilnya.
#
# Catatan desain penting
# ----------------------
# Penghapusan file TIDAK memakai "hapus semua yang tidak ada di repo". Itu
# berbahaya karena document root berisi file milik cPanel. Sebagai gantinya
# script menyimpan manifest (.deploy-public.manifest) berisi daftar nama yang
# pernah disinkronkan, dan hanya menghapus nama yang:
#   - tercatat di manifest versi sebelumnya, dan
#   - sudah tidak ada di public/
# Dengan begitu file milik cPanel tidak pernah tersentuh, bahkan bila nama
# kebetulan sama.
#
# Pakai:
#   bash scripts/deploy-public.sh            # sinkronkan
#   bash scripts/deploy-public.sh --dry-run  # hanya tampilkan rencana
#   bash scripts/deploy-public.sh --verify   # hanya verifikasi, tidak menulis
#
# Keluar dengan kode 0 bila sukses, selainnya kode bukan 0 dan pesan jelas.
# ----------------------------------------------------------------------------

set -Eeuo pipefail

# --- Konfigurasi -------------------------------------------------------------

APP_DIR="${APP_DIR:-$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)}"
DOC_DIR="${DOC_DIR:-/home/cp2ujcb5545/public_html}"

SRC="$APP_DIR/public"
MANIFEST="$DOC_DIR/.deploy-public.manifest"
MANIFEST_PREV="$MANIFEST.previous"

DRY_RUN=0
VERIFY_ONLY=0

# Nama yang TIDAK boleh disalin dan TIDAK boleh dihapus dari document root.
# Milik cPanel atau milik verifikasi domain, bukan aset aplikasi.
KEEP_NAMES=(
  ".well-known"
  "php.ini"
  "error_log"
  "Read-Assist"
  ".deploy-backups"
  ".deploy-public.manifest"
  ".deploy-public.manifest.previous"
)

# Nama yang di document root harus berupa symlink ke folder aplikasi,
# bukan salinan. Dipelihara ulang setiap sinkronisasi.
SYMLINK_NAMES=(
  "build"
  "storage"
  "favicon.ico"
)

# --- Utilitas ----------------------------------------------------------------

RED=$'\033[31m'; GRN=$'\033[32m'; YLW=$'\033[33m'; DIM=$'\033[2m'; BLD=$'\033[1m'; RST=$'\033[0m'
[ -t 1 ] || { RED=""; GRN=""; YLW=""; DIM=""; BLD=""; RST=""; }

info()  { printf '%s\n' "$*"; }
head1() { printf '\n%s%s%s\n' "$BLD" "$*" "$RST"; }
ok()    { printf '  %sOK%s   %s\n' "$GRN" "$RST" "$*"; }
warn()  { printf '  %sWARN%s %s\n' "$YLW" "$RST" "$*"; }
err()   { printf '  %sFAIL%s %s\n' "$RED" "$RST" "$*" >&2; }
step()  { printf '  %s->%s   %s\n' "$DIM" "$RST" "$*"; }

die() { err "$*"; exit 1; }

on_err() {
  local code=$? line=${1:-?}
  err "Script berhenti pada baris $line (kode keluar $code)."
  printf '  Repo public/ dan document root mungkin tidak sinkron lagi.\n' >&2
  printf '  Jalankan: bash scripts/deploy-public.sh --verify\n' >&2
  exit "$code"
}
trap 'on_err $LINENO' ERR

# True bila nama ada di KEEP_NAMES.
is_kept() {
  local n="$1" k
  for k in "${KEEP_NAMES[@]}"; do [ "$n" = "$k" ] && return 0; done
  return 1
}

# True bila nama di document root harus berupa symlink, bukan salinan.
is_symlink_managed() {
  local n="$1" k
  for k in "${SYMLINK_NAMES[@]}"; do [ "$n" = "$k" ] && return 0; done
  return 1
}

# --- Argumentasi -------------------------------------------------------------

for arg in "$@"; do
  case "$arg" in
    --dry-run)  DRY_RUN=1 ;;
    --verify)   VERIFY_ONLY=1 ;;
    -h|--help)  sed -n '2,/^set -Eeuo/p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//; $d'; exit 0 ;;
    *)          die "Argumen tidak dikenal: '$arg' (gunakan --dry-run atau --verify)" ;;
  esac
done

# --- 1. Validasi -------------------------------------------------------------

head1 "1. Validasi"

[ -d "$SRC" ]  || die "Folder sumber tidak ditemukan: $SRC"
[ -d "$DOC_DIR" ] || die "Document root tidak ditemukan: $DOC_DIR"
[ -w "$DOC_DIR" ] || die "Document root tidak bisa ditulis: $DOC_DIR (periksa izin)"

[ -f "$SRC/index.php" ]   || die "public/index.php hilang; sinkronisasi dibatalkan agar document root tidak kehilangan front controller."
[ -f "$SRC/.htaccess" ]   || die "public/.htaccess hilang; sinkronisasi dibatalkan."
[ -f "$SRC/robots.txt" ]  || die "public/robots.txt hilang; sinkronisasi dibatalkan."

# Document root harus di dalam home, bukan /
case "$DOC_DIR" in
  /|/home|/usr|/etc|/var) die "Document root mencurigakan: $DOC_DIR" ;;
esac

ok "sumber      : $SRC"
ok "documentroot: $DOC_DIR"
ok "home        : $HOME"
ok "user        : $(id -un)"

if [ -n "$(command -v rsync 2>/dev/null)" ]; then
  ok "metode      : rsync ($(rsync --version | head -1 | awk '{print $3}'))"
  SYNC_ENGINE="rsync"
else
  ok "metode      : cp/tar (rsync tidak terpasang di host)"
  SYNC_ENGINE="builtin"
fi

# --- 2. Rencanakan -----------------------------------------------------------

head1 "2. Rencana sinkronisasi"

# Direktori kerja sementara. Sengaja memakai file biasa, bukan process
# substitution (<(...)), karena /dev/fd tidak selalu tersedia pada shell
# hosting cPanel dan script akan gagal di sana.
TMPDIR_RUN=$(mktemp -d "${TMPDIR:-/tmp}/deploy-public.XXXXXX") || die "gagal membuat direktori sementara"
cleanup() { rm -rf -- "$TMPDIR_RUN"; }
trap 'cleanup' EXIT
trap 'on_err $LINENO' ERR

# Daftar isi public/ tingkat atas, tanpa symlink yang dipelihara terpisah.
LIST_RAW="$TMPDIR_RUN/raw"
LIST_ENTRIES="$TMPDIR_RUN/entries"
: > "$LIST_RAW"
find "$SRC" -mindepth 1 -maxdepth 1 -printf '%f\n' 2>/dev/null > "$LIST_RAW"
while read -r n; do
  [ -n "$n" ] || continue
  is_kept "$n" && continue
  is_symlink_managed "$n" && continue
  printf '%s\n' "$n"
done < "$LIST_RAW" | sort > "$LIST_ENTRIES"

SRC_ENTRIES=()
while read -r n; do [ -n "$n" ] && SRC_ENTRIES+=("$n"); done < "$LIST_ENTRIES"

COPIED=(); UNCHANGED=(); SKIPPED=()

for name in "${SRC_ENTRIES[@]}"; do
  s="$SRC/$name"; d="$DOC_DIR/$name"
  if [ -L "$d" ]; then
    # Target docroot berupa symlink, jadi perbandingan isi harus mengikuti target.
    if cmp -s "$s" "$d" 2>/dev/null; then UNCHANGED+=("$name"); else COPIED+=("$name"); fi
  elif [ -d "$d" ] && [ -d "$s" ]; then
    if diff -qr "$s" "$d" >/dev/null 2>&1; then UNCHANGED+=("$name"); else COPIED+=("$name"); fi
  elif [ -f "$d" ] && [ -f "$s" ] && cmp -s "$s" "$d"; then
    UNCHANGED+=("$name")
  else
    COPIED+=("$name")
  fi
done

printf '  akan diperbarui : %d\n' "${#COPIED[@]}"
for n in "${COPIED[@]}";   do printf '    %s%s%s\n' "$BLD" "$n" "$RST"; done
printf '  sudah sama      : %d\n' "${#UNCHANGED[@]}"
for n in "${UNCHANGED[@]}"; do printf '    %s%s%s\n' "$DIM" "$n" "$RST"; done
printf '  symlink upkeep  : %s\n' "${SYMLINK_NAMES[*]}"
printf '  dilindungi      : %s\n' "${KEEP_NAMES[*]}"

# Nama yang tercatat manifest lama tapi sudah hilang dari public/.
STALE=()
if [ -f "$MANIFEST" ]; then
  while read -r n; do
    [ -n "$n" ] || continue
    is_kept "$n" && continue
    [ -e "$SRC/$n" ] && continue
    [ -e "$DOC_DIR/$n" ] || continue
    STALE+=("$n")
  done < "$MANIFEST"
fi
printf '  akan dihapus    : %d\n' "${#STALE[@]}"
for n in "${STALE[@]}"; do printf '    %s%s%s\n' "$YLW" "$n" "$RST"; done

if [ "$DRY_RUN" -eq 1 ]; then
  head1 "Dry run selesai, tidak ada yang ditulis"
  exit 0
fi

if [ "$VERIFY_ONLY" -eq 1 ]; then
  head1 "Mode verifikasi: melewati penulisan"
else
  # --- 3. Backup file yang akan berubah ------------------------------------

  head1 "3. Backup"
  if [ "${#COPIED[@]}" -gt 0 ] || [ "${#STALE[@]}" -gt 0 ]; then
    BAK="$DOC_DIR/.deploy-backups/$(date +%Y%m%d-%H%M%S)"
    mkdir -p "$BAK"
    for n in "${COPIED[@]}" "${STALE[@]}"; do
      [ -e "$DOC_DIR/$n" ] || [ -L "$DOC_DIR/$n" ] || continue
      cp -a "$DOC_DIR/$n" "$BAK/" 2>/dev/null || warn "gagal backup: $n"
    done
    ok "backup: $BAK"
  else
    step "tidak ada perubahan, backup dilewati"
  fi

  # --- 4. Sinkronisasi ------------------------------------------------------

  head1 "4. Sinkronisasi"

  # 4a. Hapus file usang (hanya yang ada di manifest lama).
  for n in "${STALE[@]}"; do
    rm -rf -- "${DOC_DIR:?}/$n"
    step "dihapus: $n"
  done

  # 4b. Salin isi public/ dengan mempertahankan permission.
  for name in "${SRC_ENTRIES[@]}"; do
    s="$SRC/$name"; d="$DOC_DIR/$name"

    if [ "$SYNC_ENGINE" = "rsync" ]; then
      rsync -a --checksum -- "$s" "$d" || die "rsync gagal pada: $name"
    else
      rm -rf -- "${d:?}"
      cp -a -- "$s" "$d" || die "cp gagal pada: $name"
    fi
  done
  ok "disalin ${#SRC_ENTRIES[@]} entri"

  # 4c. Perbarui symlink.
  for name in "${SYMLINK_NAMES[@]}"; do
    target="$APP_DIR/public/$name"
    link="$DOC_DIR/$name"
    if [ ! -e "$target" ]; then
      warn "symlink dilewati, target tidak ada: public/$name"
      continue
    fi
    if [ -L "$link" ] && [ "$(readlink "$link")" = "$target" ]; then
      step "symlink sudah benar: $name"
      continue
    fi
    if [ -e "$link" ] || [ -L "$link" ]; then rm -rf -- "$link"; fi
    ln -s "$target" "$link" || die "gagal membuat symlink: $name"
    step "symlink diperbarui: $name -> $target"
  done

  # 4d. Permission final: direktori 755, file 644, symlink dibiarkan.
  find "$DOC_DIR" -type d -not -path "*/.well-known*" -not -path "*/.deploy-backups*" \
    -exec chmod 755 {} + 2>/dev/null || true
  find "$DOC_DIR" -type f -not -path "*/.well-known*" -not -path "*/.deploy-backups*" \
    -exec chmod 644 {} + 2>/dev/null || true
  ok "permission: direktori 755, file 644"

  # 4e. Tulis manifest untuk run berikutnya.
  [ -f "$MANIFEST" ] && cp -a -- "$MANIFEST" "$MANIFEST_PREV" 2>/dev/null || true
  printf '%s\n' "${SRC_ENTRIES[@]}" > "$MANIFEST"
  chmod 644 "$MANIFEST"
  ok "manifest ditulis: ${#SRC_ENTRIES[@]} entri"
fi

# --- 5. Verifikasi -----------------------------------------------------------

head1 "5. Verifikasi"

FAILURES=0

# 5a. Cocokkan byte-per-byte isi repo vs document root.
CHECK=(
  "index.php" ".htaccess" ".user.ini" "robots.txt"
  "favicon.png" "favicon.svg"
  "logo.png" "logo-horizontal.png" "logo-horizontal.svg" "logo-horizontal-hc.svg"
  "manifest.json" "sw.js"
)

for name in "${CHECK[@]}"; do
  s="$SRC/$name"; d="$DOC_DIR/$name"
  if [ -L "$d" ]; then
    if cmp -s "$s" "$d" 2>/dev/null; then ok "$name (symlink, isi cocok)"
    else err "$name: isi symlink berbeda"; FAILURES=$((FAILURES+1)); fi
  elif [ -f "$d" ] && cmp -s "$s" "$d"; then ok "$name ($(stat -c %s "$d") byte)"
  else err "$name: tidak ada atau berbeda"; FAILURES=$((FAILURES+1)); fi
done

# 5b. Symlink harus valid dan menunjuk ke target yang ada.
for name in "${SYMLINK_NAMES[@]}"; do
  link="$DOC_DIR/$name"
  if [ -L "$link" ]; then
    if [ -e "$link" ]; then ok "symlink $name valid -> $(readlink "$link")"
    else err "symlink $name rusak (target hilang): $(readlink "$link")"; FAILURES=$((FAILURES+1)); fi
  else
    err "symlink $name hilang di document root"; FAILURES=$((FAILURES+1))
  fi
done

# 5c. Aset hasil build Vite wajib ada. Manifest ditulis pretty-print, jadi
#     pola grep harus mentoleransi spasi setelah tanda titik dua.
if [ -f "$DOC_DIR/build/manifest.json" ]; then
  ok "build/manifest.json ada"
  ASSET_LIST="$TMPDIR_RUN/assets"
  grep -o '"file"[[:space:]]*:[[:space:]]*"[^"]*"' "$DOC_DIR/build/manifest.json" \
    | sed 's/.*"file"[[:space:]]*:[[:space:]]*"//; s/"$//' | sort -u > "$ASSET_LIST"
  MISSING=0; TOTAL=0
  while read -r rel; do
    [ -n "$rel" ] || continue
    TOTAL=$((TOTAL+1))
    if [ ! -f "$DOC_DIR/build/$rel" ]; then
      err "build/$rel hilang dari server"
      MISSING=$((MISSING+1))
    fi
  done < "$ASSET_LIST"
  if [ "$MISSING" -eq 0 ]; then
    ok "seluruh $TOTAL aset rujukan manifest ada"
  else
    err "$MISSING dari $TOTAL aset hilang; jalankan npm run build lalu unggah public/build"
    FAILURES=$((FAILURES+1))
  fi
else
  err "build/manifest.json hilang; aset front-end tidak akan termuat"
  FAILURES=$((FAILURES+1))
fi

# 5d. Berkas milik cPanel harus utuh.
for name in ".well-known" "php.ini"; do
  [ -e "$DOC_DIR/$name" ] && ok "berkas cPanel aman: $name" \
    || warn "berkas cPanel tidak ada: $name (boleh absen bila memang tidak dipakai)"
done

# 5e. Sisa perbedaan yang tidak disengaja.
DOC_TOP="$TMPDIR_RUN/doc-top"
KNOWN="$TMPDIR_RUN/known"
find "$DOC_DIR" -mindepth 1 -maxdepth 1 -printf '%f\n' 2>/dev/null | sort > "$DOC_TOP"
{ printf '%s\n' "${SRC_ENTRIES[@]}"; printf '%s\n' "${SYMLINK_NAMES[@]}"; printf '%s\n' "${KEEP_NAMES[@]}"; } \
  | sort -u > "$KNOWN"
LEFT=$(comm -23 "$DOC_TOP" "$KNOWN" 2>/dev/null || true)
if [ -n "$LEFT" ]; then
  printf '  %sinfo%s entri ekstra di document root (di luar daftar yang dikelola):\n' "$DIM" "$RST"
  printf '%s\n' "$LEFT" | sed 's/^/       /'
fi

head1 "Ringkasan"
if [ "$FAILURES" -eq 0 ]; then
  ok "public/ dan public_html SINKRON"
  exit 0
fi
err "verifikasi gagal: $FAILURES masalah"
exit 1
