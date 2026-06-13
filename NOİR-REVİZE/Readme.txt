# NOIR - Luxury Automotive & Premium Gayrimenkul Platformu

## ?? Proje Amacý
**NOIR**, lüks segment araç (vasýta) ilanlarý ile premium gayrimenkul (emlak/ev) ilanlarýný tek bir dinamik çatý altýnda birleþtiren, modern ve lüks konseptli bir web platformudur. 

Bu projenin temel amacý; bilgisayar programcýlýðý prensiplerine uygun olarak güvenli, hýzlý, kullanýcý dostu ve asenkron (sayfa yenilenmeden) çalýþabilen iliþkisel bir ilan otomasyonu geliþtirmektir. Sistem, kullanýcýlarýn kendi aralarýnda ilanlar üzerinden güvenli ve anlýk bir þekilde iletiþim (mesajlaþma) kurabilmelerine, geliþmiþ dinamik filtreleme mekanizmalarýyla aradýklarý lüks mülk veya araca saniyeler içinde ulaþabilmelerine olanak tanýr.

---

## ??? Kullanýlan Teknolojiler & Mimari
* **Backend:** PHP 8.x (OOP Yaklaþýmý & Session Yönetimi)
* **Database:** MySQL & PDO Sürücüsü (Prepared Statements ile SQL Injection Korumasý)
* **Frontend:** HTML5, CSS3 (Custom Glassmorphism Temasý), Bootstrap 5
* **Asenkron Ýþlemler:** Vanilla JavaScript & Fetch API / AJAX (Sayfa yenilenmeden dinamik favori ve mesajlaþma kontrolü)
* **Güvenlik Katmaný:** Þifreler için BCRYPT Kriptolama, XSS Korumasý için `htmlspecialchars()` filtrelemesi.

---

## ?? Kurulum ve Çalýþtýrma Talimatlarý

Projenin yerel bilgisayarýnýzda (localhost) sorunsuz bir þekilde çalýþtýrýlabilmesi için aþaðýdaki adýmlarý sýrasýyla uygulayýnýz:

### 1. Gereksinimlerin Karþýlanmasý
Sistemin çalýþabilmesi için bilgisayarýnýzda bir yerel sunucu yazýlýmýnýn (Örn: **XAMPP**, Wampserver veya Laragon) kurulu olmasý ve PHP 8.0 veya üzeri bir sürümü desteklemesi gerekmektedir.

### 2. Proje Dosyalarýnýn Konumlandýrýlmasý
* Bu projenin tüm klasör ve dosyalarýný kopyalayýn.
* XAMPP kullanýyorsanýz, dosyalarý sunucunun kök dizini olan `C:\xampp\htdocs\` klasörünün içerisine **`noir`** adýnda yeni bir klasör açarak yapýþtýrýn. (Yol þu þekilde olmalýdýr: `C:\xampp\htdocs\noir\index2.php`)

### 3. Veritabanýnýn Ýçe Aktarýlmasý (Import)
* XAMPP Kontrol Panelini açýn ve **Apache** ile **MySQL** servislerini `Start` butonlarýna basarak baþlatýn.
* Tarayýcýnýzýn adres çubuðuna `http://localhost/phpmyadmin/` yazarak veritabaný yönetim paneline giriþ yapýn.
* Üst menüden **"Yeni" (New)** seçeneðine týklayarak **`noir_db`** adýnda bir veritabaný oluþturun (Karþýlaþtýrma/Collation dilini `utf8mb4_general_ci` veya `utf8_general_ci` seçmeniz önerilir).
* Oluþturduðunuz `noir_db` veritabanýna týklayýn, üst barda bulunan **"Ýçe Aktar" (Import)** sekmesine gidin.
* Proje dosyalarýnýzýn içinde yer alan SQL dosyasýný (Örn: `noir_db.sql`) seçin ve sayfanýn altýndaki **"Git" (Go)** butonuna basarak tablolarýn yüklenmesini saðlayýn.

### 4. Projenin Tarayýcýda Çalýþtýrýlmasý
* Veritabaný baþarýyla yüklendikten sonra tarayýcýnýzdan yeni bir sekme açýn.
* Adres çubuðuna `http://localhost/noir/giris.php` yazarak projeyi tetikleyin.
* Sistemde henüz bir hesabýnýz yoksa kayýt olma sayfasýndan yeni bir üyelik oluþturabilir ya da veritabanýnda yer alan test kullanýcý bilgileriyle oturum açarak lüks araç ve emlak ilan detaylarýný, favori ekleme özelliklerini ve anlýk mesajlaþma panelini test edebilirsiniz.

---

## ?? Geliþtirici Bilgileri
* **Geliþtirici:** Hüseyin Efe Yýlmaz
* **Bölüm:** Bilgisayar Programcýlýðý
* **Öðrenci Numarasý:** 250408078
* **Kurum:** OSTÝM Teknik Üniversitesi
