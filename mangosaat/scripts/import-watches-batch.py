#!/usr/bin/env python3
"""Ekli saat görsellerini mangosaat WooCommerce'e yükler."""

from __future__ import annotations

import json
import os
import re
import subprocess
import sys
from pathlib import Path

SITE = "https://www.mangosaat.com.tr"
USER = os.environ.get("MANGO_WP_USER", "admin")
COOKIE = "/tmp/mango-wp-cookies.txt"
ASSETS = Path("/home/ubuntu/.cursor/projects/agent-repos-bumed/assets")

CAT_SAAT = 74
CAT_SAAT_ERKEK = 75
PRODUCTS = [
    {
        "file": "f2e85f7b-98a7-42f9-8736-3035c4fbade6.jpg",
        "name": "Patek Philippe Nautilus — Mavi Kadran",
        "sku": "MANGO-PP-NAU-M001",
        "brand": "patek-philippe",
        "short": "Çelik kasa ve bilezik, yatay çizgili mavi kadran, sapfir cam.",
    },
    {
        "file": "6e804b07-21ea-4484-bd2c-2d45d7ab2671.jpg",
        "name": "Patek Philippe Nautilus — Siyah Kadran",
        "sku": "MANGO-PP-NAU-S001",
        "brand": "patek-philippe",
        "short": "Çelik kasa, yatay çizgili siyah kadran, entegre bilezik.",
    },
    {
        "file": "1b9137ff-2d56-43e6-bd0a-71db77e91329.jpg",
        "name": "Patek Philippe Nautilus — Koyu Mavi Kadran",
        "sku": "MANGO-PP-NAU-M002",
        "brand": "patek-philippe",
        "short": "Güneş yansımalı koyu mavi kadran, çelik Nautilus kasa.",
    },
    {
        "file": "d2fe9a3c-f3a9-419c-bb82-94c38899c3ba.jpg",
        "name": "Patek Philippe Nautilus — Siyah PVD",
        "sku": "MANGO-PP-NAU-P001",
        "brand": "patek-philippe",
        "short": "Tam siyah PVD kaplama kasa ve bilezik, siyah kadran.",
    },
    {
        "file": "1abe66c5-e848-475f-b492-cf7bac2334d2.jpg",
        "name": "Cartier Santos — Beyaz Kadran Çelik",
        "sku": "MANGO-CT-SAN-W001",
        "brand": "cartier",
        "short": "Romen rakamlı beyaz kadran, çelik bilezik, otomatik.",
    },
    {
        "file": "1a55758d-7221-407a-bb55-1f96eb6ed0a1.jpg",
        "name": "Cartier Santos — Siyah Kadran Çelik",
        "sku": "MANGO-CT-SAN-B001",
        "brand": "cartier",
        "short": "Siyah kadran, çelik Santos kasa ve bilezik.",
    },
    {
        "file": "bb9bbd32-c46e-4b97-b23e-95dc17f2e457.jpg",
        "name": "Cartier Santos — Gümüş Kadran Siyah Deri",
        "sku": "MANGO-CT-SAN-G001",
        "brand": "cartier",
        "short": "Güneş yansımalı gümüş kadran, siyah timsah deri kordon.",
    },
    {
        "file": "2a1ea7ff-f3bb-4438-9ad1-b4891188fbae.jpg",
        "name": "Cartier Santos — Gümüş Kadran Kahverengi Deri",
        "sku": "MANGO-CT-SAN-G002",
        "brand": "cartier",
        "short": "Romen rakamlı gümüş kadran, kahverengi deri kordon.",
    },
    {
        "file": "801bb6ac-2258-4a30-8447-9e4d80805496.jpg",
        "name": "Cartier Santos — Mavi Kadran Çelik",
        "sku": "MANGO-CT-SAN-M001",
        "brand": "cartier",
        "short": "Mavi güneş yansımalı kadran, çelik bilezik, tarih göstergesi.",
    },
    {
        "file": "750e3d99-3bf4-4953-96a7-30d2af1f948e.jpg",
        "name": "Rolex Air-King — Siyah Kadran",
        "sku": "MANGO-RX-AK-B001",
        "brand": "rolex",
        "short": "Siyah Air-King kadranı, çelik Oyster bilezik, yeşil saniye.",
    },
]


def run(cmd: list[str], timeout: int = 180) -> subprocess.CompletedProcess:
    return subprocess.run(cmd, capture_output=True, text=True, timeout=timeout)


def login(pwd: str) -> str:
    base = ["curl", "-sS", "-c", COOKIE, "-b", COOKIE, "-A", "Mozilla/5.0"]
    run(base + [f"{SITE}/wp-login.php", "-o", "/dev/null"])
    run(
        base
        + [
            "-L",
            "-X",
            "POST",
            f"{SITE}/wp-login.php",
            "--data-urlencode",
            f"log={USER}",
            "--data-urlencode",
            f"pwd={pwd}",
            "--data-urlencode",
            "wp-submit=Oturum aç",
            "--data-urlencode",
            f"redirect_to={SITE}/wp-admin/",
            "--data-urlencode",
            "testcookie=1",
            "-o",
            "/dev/null",
        ]
    )
    dash = run(base + [f"{SITE}/wp-admin/"])
    m = re.search(r'"nonce":"([a-f0-9]+)"', dash.stdout)
    if not m:
        raise RuntimeError("REST nonce alınamadı")
    return m.group(1)


def upload_media(nonce: str, path: Path, title: str) -> int:
    fname = path.name
    cmd = [
        "curl",
        "-sS",
        "-b",
        COOKIE,
        "-A",
        "Mozilla/5.0",
        "-H",
        f"X-WP-Nonce: {nonce}",
        "-H",
        f"Content-Disposition: attachment; filename={fname}",
        "-H",
        "Content-Type: image/jpeg",
        "--data-binary",
        f"@{path}",
        f"{SITE}/wp-json/wp/v2/media",
    ]
    r = run(cmd)
    data = json.loads(r.stdout)
    if "id" not in data:
        raise RuntimeError(f"Medya yüklenemedi: {data}")
    mid = data["id"]
  # set title
    run(
        [
            "curl",
            "-sS",
            "-b",
            COOKIE,
            "-H",
            f"X-WP-Nonce: {nonce}",
            "-H",
            "Content-Type: application/json",
            "-X",
            "POST",
            "-d",
            json.dumps({"title": title, "alt_text": title}, ensure_ascii=False),
            f"{SITE}/wp-json/wp/v2/media/{mid}",
        ]
    )
    return int(mid)


def create_product(nonce: str, item: dict, media_id: int, brand_id: int) -> int:
    payload = {
        "name": item["name"],
        "type": "simple",
        "status": "publish",
        "sku": item["sku"],
        "regular_price": "9999",
        "short_description": item["short"],
        "description": f"<p>{item['short']}</p><p>Fiyat bilgi amaçlıdır; sipariş WhatsApp üzerinden onaylanır.</p>",
        "categories": [{"id": CAT_SAAT}, {"id": CAT_SAAT_ERKEK}],
        "images": [{"id": media_id}],
    }
    r = run(
        [
            "curl",
            "-sS",
            "-b",
            COOKIE,
            "-H",
            f"X-WP-Nonce: {nonce}",
            "-H",
            "Content-Type: application/json",
            "-X",
            "POST",
            "-d",
            json.dumps(payload, ensure_ascii=False),
            f"{SITE}/wp-json/wc/v3/products",
        ]
    )
    data = json.loads(r.stdout)
    if "id" not in data:
        raise RuntimeError(data)
    pid = int(data["id"])
    run(
        [
            "curl",
            "-sS",
            "-b",
            COOKIE,
            "-H",
            f"X-WP-Nonce: {nonce}",
            "-H",
            "Content-Type: application/json",
            "-X",
            "POST",
            "-d",
            json.dumps({"product_brand": [brand_id]}, ensure_ascii=False),
            f"{SITE}/wp-json/wp/v2/product/{pid}",
        ]
    )
    return pid


def resolve_brand_id(nonce: str, slug: str) -> int:
    r = run(
        [
            "curl",
            "-sS",
            "-b",
            COOKIE,
            "-H",
            f"X-WP-Nonce: {nonce}",
            f"{SITE}/wp-json/wp/v2/product_brand?slug={slug}",
        ]
    )
    data = json.loads(r.stdout)
    return int(data[0]["id"])


def main() -> int:
    pwd = os.environ.get("MANGO_WP_PASSWORD", "")
    if not pwd:
        print("MANGO_WP_PASSWORD gerekli", file=sys.stderr)
        return 1
    nonce = login(pwd)
    brand_cache: dict[str, int] = {}
    created = []
    for item in PRODUCTS:
        path = ASSETS / item["file"]
        if not path.is_file():
            print("SKIP missing", path)
            continue
        slug = item["brand"]
        if slug not in brand_cache:
            brand_cache[slug] = resolve_brand_id(nonce, slug)
        print("Upload", item["name"], "...")
        media_id = upload_media(nonce, path, item["name"])
        pid = create_product(nonce, item, media_id, brand_cache[slug])
        print("OK", pid, item["sku"], f"{SITE}/?post_type=product&p={pid}")
        created.append(pid)
    print("Toplam", len(created), "ürün")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())