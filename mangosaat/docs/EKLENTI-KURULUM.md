# Mango Saat Sipariş — listede görünmüyorsa

WordPress, `wp-content/plugins/` altındaki klasörlerde **`Plugin Name:`** başlıklı bir `.php` dosyası arar. Klasör var ama eklenti listede yoksa genelde şu nedenlerden biridir:

1. **Ana dosya eksik** — `mango-saat-siparis.php` sunucuda yok veya boş (sadece `assets/` yüklenmiş olabilir).
2. **Yanlış zip açma** — `mango-saat-wp-bundle.zip` doğrudan `plugins/` içine açılınca `wp-content/plugins/...` gibi **çift dizin** oluşur; eklenti bulunamaz.
3. **İç içe klasör** — `plugins/mango-saat-siparis/mango-saat-siparis/` şeklinde fazladan bir seviye.

## Doğru yapı (cPanel Dosya Yöneticisi)

```
public_html/wp-content/plugins/mango-saat-siparis/
├── mango-saat-siparis.php    ← bu dosya şart
├── includes/
│   ├── class-mango-saat.php
│   └── ...
└── assets/
```

## Kurulum (önerilen)

1. `dist/mango-saat-siparis-plugin.zip` indirin (yalnızca eklenti; kök klasör `mango-saat-siparis/`).
2. cPanel → **Dosya Yöneticisi** → `public_html/wp-content/plugins/`.
3. Eski **boş veya eksik** `mango-saat-siparis` klasörünü silin veya yeniden adlandırın (`mango-saat-siparis-eski`).
4. Zip’i bu `plugins` klasörüne yükleyin ve **Extract**.
5. Sonuç: `plugins/mango-saat-siparis/mango-saat-siparis.php` dosyası görünmeli.
6. WordPress → **Eklentiler** → **Mango Saat Sipariş** → **Etkinleştir**.

Alternatif: wp-admin → **Eklentiler → Yeni ekle → Eklenti yükle** ile aynı zip’i yükleyin (sunucu izin veriyorsa).

## Kontrol

Tarayıcıda (oturum gerekmez):

`https://www.mangosaat.com.tr/wp-content/plugins/mango-saat-siparis/mango-saat-siparis.php`

Sayfa **beyaz/boş** olmalı; PHP kaynak kodu veya indirme olmamalı. **404** ise dosya yolu hâlâ yanlış veya dosya yok.