#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="${ROOT}/demo1"

HOST="${BUMED_SFTP_HOST:-${FTP_HOST:-${DEPLOY_HOST:-}}}"
USER="${BUMED_SFTP_USER:-${FTP_USER:-${DEPLOY_USER:-}}}"
PASS="${BUMED_SFTP_PASSWORD:-${FTP_PASSWORD:-${DEPLOY_PASSWORD:-}}}"
REMOTE_PATH="${BUMED_SFTP_PATH:-/demo1}"

if [[ -z "$HOST" || -z "$USER" || -z "$PASS" ]]; then
  echo "Eksik deploy bilgisi. Ortam değişkenleri: BUMED_SFTP_HOST, BUMED_SFTP_USER, BUMED_SFTP_PASSWORD (veya FTP_* / DEPLOY_*)." >&2
  exit 1
fi

if ! command -v lftp >/dev/null 2>&1; then
  echo "lftp bulunamadı." >&2
  exit 1
fi

echo "Yükleniyor: ${SRC} -> ftp://${HOST}${REMOTE_PATH}"

lftp -u "${USER},${PASS}" "ftp://${HOST}" <<EOF
set ftp:ssl-allow no
set net:timeout 30
set net:max-retries 3
mkdir -p ${REMOTE_PATH}
cd ${REMOTE_PATH}
lcd ${SRC}
mirror -R -v --delete --parallel=2 \
  --exclude-glob .git* \
  .
bye
EOF

echo "Deploy tamamlandı."
