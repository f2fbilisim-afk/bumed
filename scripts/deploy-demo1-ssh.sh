#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="${ROOT}/demo1"

HOST="${BUMED_SSH_HOST:-134.255.199.83}"
USER="${BUMED_SSH_USER:-root}"
PASS="${BUMED_SSH_PASSWORD:-}"
REMOTE_DIR="${BUMED_SSH_PATH:-/home/bumed/domains/bumed.webonalti.net/public_html/demo1}"

if [[ -z "$PASS" ]]; then
  echo "BUMED_SSH_PASSWORD gerekli (Cursor Secrets — chate yazmayın)." >&2
  exit 1
fi

if ! command -v sshpass >/dev/null 2>&1; then
  echo "sshpass bulunamadı." >&2
  exit 1
fi

export SSHPASS="$PASS"
SSH_OPTS=(-o StrictHostKeyChecking=no -o PreferredAuthentications=password -o PubkeyAuthentication=no)
REMOTE="${USER}@${HOST}:${REMOTE_DIR}/"

echo "Yükleniyor: ${SRC} -> ${REMOTE}"

deploy_file() {
  local f="$1"
  if [[ -f "${SRC}/${f}" ]]; then
    sshpass -e scp "${SSH_OPTS[@]}" "${SRC}/${f}" "$REMOTE"
  fi
}

for f in index.html styles.css events.css events.js events-feed.php mobile.css mobile.js instagram-feed.php; do
  deploy_file "$f"
done

shopt -s nullglob
for f in "${SRC}"/*.{jpg,jpeg,png,webp,gif,svg}; do
  deploy_file "$(basename "$f")"
done
shopt -u nullglob

sshpass -e ssh "${SSH_OPTS[@]}" "${USER}@${HOST}" \
  "php ${REMOTE_DIR}/instagram-feed.php > ${REMOTE_DIR}/instagram-feed.json && php ${REMOTE_DIR}/events-feed.php > ${REMOTE_DIR}/events-feed.json && chown -R bumed:bumed ${REMOTE_DIR}"

echo "Deploy tamamlandı: https://bumed.webonalti.net/demo1/"
