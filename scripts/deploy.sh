#!/usr/bin/env bash
#
# deploy.sh
# ----------------------------------------------------------------------------
# Orkestrator deployment Read-Assist: git -> cache -> sinkron public -> health.
#
# Mengapa script ini ada
# ----------------------
# Sebelumnya `git pull` hanya berupa satu baris manual di DEPLOY.md, tanpa
# validasi exit code. Akibatnya pernah terjadi: pull gagal karena ada perubahan
# lokal di server,namun proses tetap berjalan, dan server Ends up menjalankan
# commit LAMA padahal origin sudah punya commit baru. Deploy terlihat sukses
# padahal tidak menjalankan kode yang seharusnya.
#
# Script ini membuat setiap langkah fail-fast. Kalau git tidak sehat, deploy
# berhenti SEBELUM menyentuh public_html, jadi document root tidak pernah
#Condong ke build yang tidak sesuai dengan commit di server.
#
# Pemeriksaan yang dijalankan:
#   1. working tree bersih        (git diff --quiet, git diff --cached --quiet)
#   2. fetch origin berhasil
#   3. git pull --ff-only berhasil
#   4. HEAD == origin/master
#   5. tidak sedang detached HEAD
#
# Pakai:
#   bash scripts/deploy.sh
#
# Environment:
#   APP_DIR            default: folder induk script/..
#   DOC_DIR            default: /home/cp2ujcb5545/public_html
#   HEALTH_BASE_URL    default: https://readassist.web-id.id
# ----------------------------------------------------------------------------

set -Eeuo pipefail

APP_DIR="${APP_DIR:-$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)}"
DOC_DIR="${DOC_DIR:-/home/cp2ujcb5545/public_html}"
HEALTH_BASE_URL="${HEALTH_BASE_URL:-https://readassist.web-id.id}"
BRANCH="${BRANCH:-master}"
PHP_BIN="${PHP_BIN:-$(command -v php || echo /opt/alt/php85/usr/bin/php)}"

STEP=0
TOTAL_STEPS=5

# --- Output ------------------------------------------------------------------

if [ -t 1 ]; then
  RED=$'\033[31m'; GRN=$'\033[32m'; YLW=$'\033[33m'; DIM=$'\033[2m'; BLD=$'\033[1m'; RST=$'\033[0m'
else
  RED=''; GRN=''; YLW=''; DIM=''; BLD=''; RST=''
fi

say()  { printf '%s\n' "$*"; }
info() { printf '  %s\n' "$*"; }
ok()   { printf '  %sOK%s    %s\n' "$GRN" "$RST" "$*"; }
warn() { printf '  %sWARN%s  %s\n' "$YLW" "$RST" "$*"; }
bad()  { printf '  %sGAGAL%s %s\n' "$RED" "$RST" "$*"; }
sub()  { printf '       %s%s%s\n' "$DIM" "$*" "$RST"; }

banner() {
  printf '\n%s%s%s\n' "$BLD" "============================================================" "$RST"
  printf '%s Read-Assist deployment%s\n' "$BLD" "$RST"
  printf '  aplikasi : %s\n' "$APP_DIR"
  printf '  docroot  : %s\n' "$DOC_DIR"
  printf '  waktu    : %s\n' "$(date '+%Y-%m-%d %H:%M:%S %Z')"
  printf '%s%s%s\n\n' "$BLD" "============================================================" "$RST"
}

step() {
  STEP=$((STEP+1))
  printf '\n%s[%d/%d] %s%s\n' "$BLD" "$STEP" "$TOTAL_STEPS" "$1" "$RST"
}

# Berhenti dengan pesan. Argumen pertama ringkasan, sisanya detail.
abort() {
  local headline="$1"; shift
  printf '\n%s%s%s\n' "$RED" "============================================================" "$RST"
  printf '%sERROR: %s%s\n' "$RED" "$headline" "$RST"
  printf '%s%s%s\n' "$RED" "============================================================" "$RST"
  for line in "$@"; do printf '%s\n' "$line"; done
  printf '\n%sDeployment dibatalkan. public_html tidak disentuh.%s\n' "$RED" "$RST"
  exit 1
}

# Jalankan git, tangkap output, dan hentikan dengan pesan bila gagal.
git_or_die() {
  local headline="$1"; shift
  local out rc
  set +e
  out="$(git -C "$APP_DIR" "$@" 2>&1)"
  rc=$?
  set -e
  if [ $rc -ne 0 ]; then
    [ -n "$out" ] && printf '%s\n' "$out" | sed 's/^/         /'
    abort "$headline" "$@"
  fi
  printf '%s' "$out"
}

# --- 0. Validasi dasar ------------------------------------------------------

banner

[ -d "$APP_DIR/.git" ] || abort "bukan folder git" "Ditemukan: $APP_DIR/.git tidak ada."
git -C "$APP_DIR" rev-parse --git-dir >/dev/null 2>&1 \
  || abort "bukan folder git" "rev-parse --git-dir gagal di $APP_DIR."

# --- 1. Git safety -----------------------------------------------------------

step "Git safety"

CURRENT_BRANCH="$(git -C "$APP_DIR" rev-parse --abbrev-ref HEAD 2>/dev/null || echo '?')"
if [ "$CURRENT_BRANCH" = "HEAD" ]; then
  abort "sedang detached HEAD" \
    "Server berjalan pada commit lepas, bukan branch." \
    "Jalankan: git -C $APP_DIR checkout $BRANCH" \
    "Lalu ulangi deploy."
fi

HEAD_BEFORE="$(git -C "$APP_DIR" rev-parse HEAD)"

info "branch aktif    : $CURRENT_BRANCH"
info "HEAD sebelum    : $HEAD_BEFORE"

# 1a. Working tree harus bersih. Ini pemeriksaan yang mencegah bug yang pernah
#     terjadi: pull ditolak karena ada modifikasi lokal, lalu deploy tetap jalan.
DIRTY=0
git -C "$APP_DIR" diff --quiet || DIRTY=1
git -C "$APP_DIR" diff --cached --quiet || DIRTY=1

if [ "$DIRTY" -eq 1 ]; then
  bad "working tree tidak bersih"
  sub "Berkas berikut berubah di server:"
  git -C "$APP_DIR" status --porcelain | grep -E '^ ?[MADRCU]|^[MADRCU]|^\?\?' | sed 's/^/         /' || true
  printf '\n'
  abort "working tree tidak bersih" \
    "Server masih memiliki perubahan lokal." \
    "Deployment dibatalkan." \
    "" \
    "Perbaiki salah satu:" \
    " -buang  : git -C $APP_DIR checkout -- <file>" \
    "  commit : git -C $APP_DIR add -A && git -C $APP_DIR commit -m '...'" \
    "" \
    "Jangan pernah memakai git reset --hard di server produksi."
else
  ok "working tree bersih"
fi

# 1b. File untracked bukan alasan membatalkan deploy, tapi harus terlihat.
UNTRACKED="$(git -C "$APP_DIR" status --porcelain 2>/dev/null | grep '^??' || true)"
if [ -n "$UNTRACKED" ]; then
  warn "ada berkas untracked (tidak membatalkan deploy)"
  printf '%s\n' "$UNTRACKED" | sed 's/^/         /'
fi

# 1c. Fetch dulu supaya perbandingan dengan origin/master memakai data terbaru.
info "fetch origin ..."
git_or_die "git fetch origin gagal." fetch --prune origin >/dev/null
ok "fetch origin berhasil"

# 1d. Pull. Ini langkah yang harus fail-fast.
info "pull --ff-only $BRANCH ..."
PULL_OUT="$(git_or_die "git pull gagal." pull --ff-only origin "$BRANCH")"
[ -n "$PULL_OUT" ] && printf '%s\n' "$PULL_OUT" | sed 's/^/         /'
ok "pull berhasil"

# 1e. HEAD harus sama dengan origin/master. Kalau tidak, server berjalan kode
#     yang tidak sama dengan yang ada di remote.
HEAD_AFTER="$(git -C "$APP_DIR" rev-parse HEAD)"
ORIGIN_MASTER="$(git -C "$APP_DIR" rev-parse "origin/$BRANCH")"

info "HEAD sesudah    : $HEAD_AFTER"
info "origin/$BRANCH : $ORIGIN_MASTER"

if [ "$HEAD_AFTER" != "$ORIGIN_MASTER" ]; then
  abort "HEAD tidak sama dengan origin/$BRANCH" \
    "HEAD            : $HEAD_AFTER" \
    "origin/$BRANCH : $ORIGIN_MASTER" \
    "" \
    "Server tidak menjalankan kode yang sama dengan remote." \
    "Jalankan manual: git -C $APP_DIR log --oneline HEAD..origin/$BRANCH"
fi
ok "HEAD == origin/$BRANCH"

if [ "$HEAD_BEFORE" != "$HEAD_AFTER" ]; then
  ok "server diperbarui: ${HEAD_BEFORE:0:7} -> ${HEAD_AFTER:0:7}"
else
  ok "server sudah pada commit terbaru"
fi

# --- 2. Cache aplikasi -------------------------------------------------------

step "Cache aplikasi"

artisan() {
  "$PHP_BIN" "$APP_DIR/artisan" "$@" >/dev/null 2>&1 \
    || abort "php artisan $* gagal" \
         "Perintah: php artisan $*" \
         "Cache tidak dibangun, deploy dihentikan agar tidak berjalan dengan cache lama."
}

info "optimize:clear"; artisan optimize:clear
ok "optimize:clear"
info "config:cache";  artisan config:cache
ok "config:cache"
info "route:cache";   artisan route:cache
ok "route:cache"
info "view:cache";    artisan view:cache
ok "view:cache"

# --- 3. Sinkronisasi public --------------------------------------------------

step "Sinkronisasi public"

[ -f "$APP_DIR/scripts/deploy-public.sh" ] \
  || abort "scripts/deploy-public.sh tidak ditemukan" "Repo tidak lengkap."

HEALTH_BASE_URL="$HEALTH_BASE_URL" APP_DIR="$APP_DIR" DOC_DIR="$DOC_DIR" \
  bash "$APP_DIR/scripts/deploy-public.sh" \
  || abort "sinkronisasi public gagal" \
       "deploy-public.sh keluar dengan kode bukan 0." \
       "Rinciannya ada di atas." \
       "" \
       "Bila document root rusak: bash scripts/deploy-public.sh --rollback latest"

# --- 4. Konfirmasi ------------------------------------------------------------

step "Konfirmasi akhir"

# deploy-public.sh sudah menjalankan health check. Di sini kita memastikan
# document root benar-benar sinkron dan tidak ada symlink yang putus.
[ -d "$DOC_DIR" ] || abort "document root hilang" "Tidak ada: $DOC_DIR"

BROKEN=""
for n in build storage favicon.ico; do
  if [ ! -L "$DOC_DIR/$n" ] || [ ! -e "$DOC_DIR/$n" ]; then
    BROKEN="$BROKEN $n"
  fi
done
[ -z "$BROKEN" ] || abort "symlink rusak" " Rusak:$BROKEN"

[ -d "$DOC_DIR/.well-known" ] || abort ".well-known hilang" "Renewal SSL akan gagal. Pulihkan dari backup cPanel."
[ -f "$DOC_DIR/php.ini" ]      || abort "php.ini hilang" "Pulihkan dari backup cPanel."

ok "document root sinkron"
ok "symlink build, storage, favicon.ico valid"
ok "berkas cPanel (.well-known, php.ini) utuh"

# --- 5. Ringkasan ------------------------------------------------------------

step "Selesai"

FINAL_COMMIT="$(git -C "$APP_DIR" rev-parse HEAD)"
printf '\n%sDEPLOY BERHASIL%s\n' "$GRN" "$RST"
printf '  commit     : %s\n' "$FINAL_COMMIT"
printf '  subject    : %s\n' "$(git -C "$APP_DIR" log -1 --pretty=%s)"
printf '  branch     : %s\n' "$CURRENT_BRANCH"
printf '  docroot    : %s\n' "$DOC_DIR"
printf '\n  Rincian lengkap ada di .deploy-public.manifest.json\n\n'
exit 0
