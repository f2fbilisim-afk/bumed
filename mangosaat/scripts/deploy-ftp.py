#!/usr/bin/env python3
"""Mangosaat.com.tr — wp-content parçalarını FTP ile yükler (şifre ortam değişkeni: MANGOSAAT_FTP_PASSWORD)."""

from __future__ import annotations

import ftplib
import os
import sys
from pathlib import Path

HOST = os.environ.get("MANGOSAAT_FTP_HOST", "78.142.208.71")
USER = os.environ.get("MANGOSAAT_FTP_USER", "mangosaatcom")
PASSWORD = os.environ.get("MANGOSAAT_FTP_PASSWORD", "")
REMOTE_ROOT = os.environ.get("MANGOSAAT_FTP_PATH", "/public_html/wp-content")

ROOT = Path(__file__).resolve().parents[1]


def ensure_dir(ftp: ftplib.FTP, remote_dir: str) -> None:
    parts = [p for p in remote_dir.split("/") if p]
    path = ""
    for part in parts:
        path += f"/{part}"
        try:
            ftp.mkd(path)
        except ftplib.error_perm:
            pass


def upload_tree(ftp: ftplib.FTP, local: Path, remote: str) -> None:
    ensure_dir(ftp, remote)
    for item in local.iterdir():
        rpath = f"{remote}/{item.name}"
        if item.is_dir():
            upload_tree(ftp, item, rpath)
        else:
            with item.open("rb") as fh:
                ftp.storbinary(f"STOR {rpath}", fh)
            print(f"OK {rpath}")


def main() -> int:
    if not PASSWORD:
        print("MANGOSAAT_FTP_PASSWORD tanımlı değil.", file=sys.stderr)
        return 1

    ftp = ftplib.FTP()
    ftp.connect(HOST, 21, timeout=60)
    ftp.login(USER, PASSWORD)
    ftp.set_pasv(True)

    upload_tree(ftp, ROOT / "wp-content/plugins/mango-saat-siparis", f"{REMOTE_ROOT}/plugins/mango-saat-siparis")
    upload_tree(ftp, ROOT / "wp-content/themes/woodmart-child", f"{REMOTE_ROOT}/themes/woodmart-child")

    ftp.quit()
    print("Deploy tamamlandı.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())