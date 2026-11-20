# Mango Saat (WordPress / WooCommerce)

Şartname: `Mango_Saat_Cursor_Sartnamesi.md`

## İçerik

| Parça | Konum |
|--------|--------|
| **Mango Saat Sipariş** eklentisi | `wp-content/plugins/mango-saat-siparis/` |
| **WoodMart Child** teması | `wp-content/themes/woodmart-child/` |

## Eklenti özellikleri

- WooCommerce ödeme / checkout kapalı; checkout isteği sepete yönlenir
- Ürün döngüsünde **WhatsApp Sipariş**; ürün detayında WhatsApp + **Sepete Ekle**
- Yönetilebilir WhatsApp numarası (varsayılan `905414631677`)
- Kutu ürünleri: gizli katalog, ana ürüne bağlama, özel kutu fiyatı, sepet ve mesajda kutu satırı
- Şartname kategorileri ve 32 marka: **WooCommerce → Mango Saat Sipariş → Kategori ve markaları senkronize et**

## Canlıya yükleme

**cPanel (önerilen):** `dist/mango-saat-wp-bundle.zip` dosyasını indirin → cPanel Dosya Yöneticisi → `public_html/wp-content/` → yükle ve açın (klasörler `plugins/mango-saat-siparis` ve `themes/woodmart-child` altına yerleşmeli).

**FTP:** Bazı ağlarda veri bağlantısı kapalı olabilir; yerelde:

```bash
cd mangosaat
export MANGOSAAT_FTP_PASSWORD='…'   # cPanel FTP şifresi; repoya veya chat’e yazmayın
python3 scripts/deploy-ftp.py
```

WordPress panelinde:

1. **Eklentiler** → *Mango Saat Sipariş* → Etkinleştir
2. **Görünüm → Temalar** → *WoodMart Child — Mango Saat* → Etkinleştir
3. **WooCommerce → Mango Saat Sipariş** → numarayı doğrula (kategoriler/markalar eklenti **etkinleştirildiğinde** otomatik yüklenir; gerekirse aynı ekrandan **senkron** butonuna basın)
4. Demo ürünleri ve yabancı metinli kampanyaları yayından kaldır (şartname)
5. WoodMart/Elementor ile ana sayfa ve menüyü Mango kimliğine göre düzenle (Watches demo referans)

## Ürün yönetimi (kısa)

- Normal ürün: kategori + `product_brand` markası, fiyat, galeri
- Kutu ürünü: ürün düzenle → **Kutu ürünü** işaretle (katalogda görünmez)
- Ana ürün: yan meta kutusundan **Uyumlu kutu** seç; isteğe bağlı özel kutu fiyatı
- Kutu ürünleri panelde **Ürünler** listesinde görünür; `_mango_is_box` işaretli ve vitrinde gizlidir (Mango Saat Sipariş eklentisi etkinken)

## Doğrulama kontrol listesi

Şartnamedeki teslim maddeleri: masaüstü/mobil menü, filtreler, WhatsApp mesaj içeriği, kutulu/kutusuz sepet satırları, ödeme butonu yokluğu.