# Mango Saat | Cursor uygulama şartnamesi

## Proje ve kapsam

Mangosaat.com.tr için Türkçe, TL fiyatlı, mobil uyumlu bir ürün kataloğu geliştir. Altyapı WordPress, WooCommerce, WoodMart ve WoodMart Core. Görsel temel [WoodMart Watches demosu](https://woodmart.xtemos.com/demo-watches/demo/watch/). Kullanılabilirlik referansı [Saatimarkaa](https://saatimarkaa.com/). Ekteki ekranlar ürün galerisini ve ürüne bağlı kutu teklifini anlatır. Ekteki üçüncü görsel Mango Saat logosudur. Kurulu demoyu ve mevcut sayfa oluşturucuyu kullan. Tasarımı Mango Saat’e özgü oluştur.

Katalogdaki ürünler tekil veya sepet halinde WhatsApp üzerinden sipariş talebine dönüşür. Online ödeme, kart bilgisi, WooCommerce ödeme formu ve otomatik sipariş kaydı devre dışıdır. Sepet, WhatsApp’a gönderilecek ürünleri hazırlamak için çalışır. WhatsApp hazır mesajı açar. Mesajı müşteri gönderir ve siparişi işletme görüşmede teyit eder.

## Görsel tasarım ve sayfalar

- Siyah ve mat altın ağırlıklı premium kimlik. Açık zeminli ürün alanları, güçlü görsel hiyerarşi, okunaklı fiyatlar ve telefon ekranında rahat dokunulan butonlar kullan. Logo oranlarını koru. Koyu header’da logonun okunabildiği bir uygulama kullan. Altın tonları ve stiller site genelinde tutarlı olsun.
- Header’da logo, kategori menüsü, ürün arama ve adet göstergeli sepet yer alsın. Mobil menüde Erkek/Kadın alt kategorileri açılabilir olsun. Footer’da kategori ve marka bağlantıları ile WhatsApp iletişimi bulunsun.
- Ana sayfada saat odaklı hero, Erkek Saatleri ve Kadın Saatleri girişleri, fotoğraflı öne çıkan saatler, marka alanı ve diğer ana kategorilere bağlantılar yer alsın. Öne çıkanlar WooCommerce panelinden seçilebilsin.
- Kategori ve marka arşivlerinde fotoğraf, marka, ürün/model adı, fiyat ve görünür WhatsApp Sipariş butonu göster. Fotoğraf ve başlık ürün sayfasına gitsin. Ürün kartındaki varsayılan Sepete Ekle butonunu ve bu butonu tekrar getirebilecek hızlı görünüm davranışını düzenle.
- Ürün detayında marka bağlantısı, model adı, fiyat, açıklama ve çoklu ürün galerisi göster. Mobilde kaydırma ve görsel büyütme çalışsın. Varyasyon varsa ürün detayında seçilebilsin. Karttan WhatsApp’a geçişte henüz seçilmemiş bir varyasyon kesinleşmiş gibi yazılmasın.
- Verilen logo ve yetkilendirilmiş ürün görsellerini kullan. Gerçek ürün verileri teslim edilmemişse örnekleri hazırlık ortamında tut. Hazır demo ürünleri, yabancı metinler ve örnek kampanyalar müşteriye açık sayfalarda kalmasın.

## Katalog

| Ana kategori | Alt kategoriler |
|---|---|
| Saat | Erkek, Kadın |
| Parfüm | Erkek, Kadın |
| Gözlük | Erkek, Kadın |
| Çakmak | |
| Uygun Seri | |

Saat altındaki Erkek ve Parfüm altındaki Erkek gibi terimler ayrı kategori kayıtlarıdır. Çakışmayı önlemek için hiyerarşiye özgü adresler kullan. Mağaza, kategori ve marka sayfalarında marka ve fiyat filtresi ile ürün araması çalışsın. Marka filtresi açık kategorinin içinde sonuç versin. Marka bağlantısı markanın kendi arşivini açsın. Arama en az ürün adı ve model adıyla çalışsın. Boş sonuçlarda anlaşılır mesaj ve filtre temizleme seçeneği sun.

WooCommerce’in yerleşik marka kayıtlarını kullan. Ürün birden fazla kategoriye atanabilir. Marka ve kategori yüklemesi yeniden çalıştırıldığında kopya üretmesin, mevcut kayıtları silmesin. Aşağıdaki 32 markayı yükle. Kategori bazında hangi markanın görüneceği ürün atamalarına göre belirlensin.

Audemars Piguet, Armani, Breitling, Burberry, BVLGARI, Cartier, Franck Muller, Gucci, Guess, Hublot, Invicta, IWC Schaffhausen, Jacob & Co., Longines, Michael Kors, Montblanc, Omega, Oris, Panerai, Patek Philippe, Rado, Richard Mille, Rolex, Roger Dubuis, Seiko, SEVENFRIDAY, TAG Heuer, Tissot, Tudor, Ulysse Nardin, Vacheron Constantin, Versace.

## WhatsApp ve sepet

- Yönetim panelinde değiştirilebilir sipariş numarası tanımla. Başlangıç değeri +90 541 463 16 77. WhatsApp bağlantısında 905414631677 kullan. Türkçe karakterler, satır sonları ve ürün URL’leri doğru kodlansın.
- **Ürün kartı — WhatsApp Sipariş.** Ürün/model adı, görüntülenen fiyat veya fiyat aralığı ve sayfa linkiyle doğrudan görüşme aç. Varyasyon daha seçilmemişse mesaj ürün hakkında sipariş talebi olarak yazılsın.
- **Ürün detayı — WhatsApp Sipariş ve Sepete Ekle.** İki işlem de seçilen varyasyonu, adedi ve isteğe bağlı kutuyu dikkate alsın. Varyasyon gerekiyorsa seçim yapılmadan kesin fiyatla talep oluşturma. Ürün sepete eklenince kullanıcı katalogda kalabilsin veya sepeti açabilsin.
- **Sepet — Sepeti WhatsApp’tan Gönder.** Adet değiştirme, ürün kaldırma ve uygun satırda kutu ekleme/çıkarma çalışsın. Mesajda her ürünün adı, varsa varyasyonu, adedi, birim fiyatı, ürün linki, seçili kutu ve kutu fiyatı ile sepetin ürün toplamı yer alsın. URL’ye müşterinin kişisel bilgilerini ekleme. Boş sepette gönderme butonu gösterme. WhatsApp açıldığında sepeti otomatik boşaltma.
- WooCommerce ödeme yöntemlerini kapat. Sepet, mini sepet ve mobil arayüzdeki ödeme butonlarını kaldır veya uygun WhatsApp akışına bağla. Ödeme sayfasına doğrudan gelen ziyaretçiyi sepete yönlendir. Kullanılan WooCommerce yapılandırmasında varsayılan checkout üzerinden ödeme veya sipariş oluşturma açık kalmasın. Fiyat ve sepet özeti aynı hesaplamadan beslensin. Teslimat ve ödeme ayrıntıları görüşmede netleşir.

## Ürüne bağlı kutu

- Her kutu WooCommerce’de ayrı bir üründür. Ürün adı, SKU, fotoğraf, fiyat ve uygulanıyorsa stok panelden yönetilir. Kutu katalogda, marka arşivlerinde, aramada ve bağımsız satın alma arayüzlerinde gösterilmez. Kutu yalnızca ilişkili ana ürüne eklenebilir.
- Ana ürün düzenleme ekranında aranabilir **Uyumlu kutu** ve isteğe bağlı **Bu ürüne özel kutu fiyatı** alanları sun. Özel fiyat boşsa kutu ürününün geçerli fiyatı alınır. Sıfır geçerli bir özel fiyattır. Aynı kutu farklı ürünlere farklı fiyatlarla bağlanabilsin.
- Ürün detayında fiyatın altında **Kutuyla al** seçeneğini varsayılan olarak kapalı göster. Kutu görseli, adı ve ek fiyatı görünür olsun. Stok kısıtı olan kutu seçilemiyorsa kullanıcıya sebebini göster ve ana ürüne kutusuz devam etmesine izin ver. Bağlı kutu yoksa kutu alanı gösterilmez.
- Sepette kutulu ve kutusuz aynı ürünü ayrı satırlar olarak koru. Varsayılan kural seçili kutudan her ana ürün adedi için bir kutudur. Adet değişiminde kutu adedini ve toplamı güncelle. Ana ürün silinirse bağlı kutuyu kaldır. Bir kutu birden çok satıra bağlıysa her satırın fiyatı ve seçimi bağımsız işlesin. Sepet yenilenince seçim korunsun. Sunucuda ilişkiyi, fiyatı, adetleri ve varsa stoku doğrula. Yönetici ilişkiyi kaldırırsa veya stok değişirse kullanıcıya durumu bildirip toplamı güncelle.
- Referanstaki sayaç ve indirim yüzdesi zorunlu değildir. Gerçek bir kampanya yapılandırılmadıkça geri sayım, üzeri çizili fiyat veya indirim etiketi gösterme.

## Yönetim ve teknik uygulama

Müşteri WordPress panelinden kategori, marka, ürün, görseller, galeri, SKU, fiyat, açıklama, öne çıkarma ve kutu eşleştirmesini yönetebilsin. WooCommerce’e aşina bir kullanıcı yeni bir ürünü kod yazmadan yayımlayabilsin.

Sipariş işlevlerini **Mango Saat Sipariş** adlı özel eklentide, görsel özelleştirmeleri tema ayarlarında ve gerektiğinde child theme’de tut. WordPress, WoodMart ve WooCommerce çekirdek dosyalarını düzenleme. Kurulu sürümlerde kullanılan WooCommerce sepet türünü ve WoodMart AJAX bileşenlerini incele. Ürün döngüsü, mini sepet, arama ve filtreler üzerinden günlük kullanımda değişikliklerin çalıştığını doğrula. Sepeti veya kullanıcıya özel içeriği ortak önbelleğe alma. Yönetim ayarlarını yetki kontrolü ve girdi doğrulamasıyla kaydet. Çıktıları kullanıldıkları bağlama göre güvenli biçimde işle.

## Teslim kabulü

- Masaüstü, tablet ve mobilde menü, arama, filtreler, ürün galerisi ve sepet düzgün çalışıyor.
- Marka arşivi yalnızca o markayı, kategori içi marka filtresi yalnızca kesişen ürünleri gösteriyor. Katalogda kutular ve demo ürünler görünmüyor.
- Ana sayfa ve katalogdaki karttan doğru numaraya, doğru ürün adı ve linkiyle WhatsApp mesajı açılıyor.
- Kutu bağlı ve bağlı olmayan ürünler doğru görünüyor. Aynı ürünün kutulu ve kutusuz satırları, değişen adetler, silme ve yeniden yükleme beklenen sonucu veriyor.
- Kutu fiyatı, özel kutu fiyatı ve stok durumu doğru işleniyor. Ürün sayfası, sepet ve WhatsApp mesajındaki seçimler ve toplamlar tutarlı.
- Mobil ve masaüstünde Türkçe karakterli çok ürünlü mesaj doğru açılıyor. Sepet ve mini sepet hiçbir noktada ödeme veya adres formuna götürmüyor.
- Müşteri panelden bir ürünü ekleyebiliyor, fiyatını değiştirebiliyor, galeri yükleyebiliyor, marka atayabiliyor ve kutu bağlayabiliyor.
- Teslimde değişen dosyaları, tema/eklenti ayarlarını, ürün ve kutu yönetiminin kısa tarifini ve doğrulanan senaryoları bildir.
