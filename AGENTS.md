# BÜMED demo1 — canlı çalışma

- Kaynak: `demo1/` → canlı: `https://bumed.webonalti.net/demo1/`
- Her değişiklikten sonra `main`’e push veya `scripts/deploy-demo1.sh` ile FTP yükle.
- Deploy ortam değişkenleri (Cursor Secrets / GitHub Actions secrets):
  - `BUMED_SFTP_HOST` (örn. `bumed.webonalti.net`)
  - `BUMED_SFTP_USER`
  - `BUMED_SFTP_PASSWORD`
  - `BUMED_SFTP_PATH` (sunucudaki klasör, örn. `/demo1` veya paneldeki `public_html/demo1`)
