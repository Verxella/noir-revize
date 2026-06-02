<?php
session_start();
// Veritabanı Bağlantısı
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "noir_db";

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Bağlantı Hatası: " . $e->getMessage());
}

// --- MODELLERİ DİNAMİK GETİRME (AJAX ENDPOINT) ---
if (isset($_GET['durum']) && $_GET['durum'] == 'modelleri_getir') {
    $marka_id = $_GET['marka_id'];
    $sorgu = $db->prepare("SELECT id, model_adi FROM modeller WHERE marka_id = ? ORDER BY model_adi ASC");
    $sorgu->execute([$marka_id]);
    $modeller = $sorgu->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($modeller);
    exit;
}

// 1. KAYIT İŞLEMİ
if (isset($_GET['durum']) && $_GET['durum'] == "kayit") {
    $ad_soyad = $_POST['ad_soyad'];
    $email = $_POST['email'];
    $telefon = $_POST['telefon'];
    $sifre = password_hash($_POST['sifre'], PASSWORD_BCRYPT);
    $varsayilan_resim = "default-avatar.png"; 

    // --- TELEFON NUMARASI KONTROLÜ ---
    $kontrol_sorgu = $db->prepare("SELECT COUNT(*) as sayi FROM users WHERE telefon = ?");
    $kontrol_sorgu->execute([$telefon]);
    $kontrol_sonuc = $kontrol_sorgu->fetch(PDO::FETCH_ASSOC);

    if ($kontrol_sonuc['sayi'] > 0) {
        // Eğer numara varsa hata mesajıyla geri gönder
        header("Location: kayit.php?hata=mukerrer_telefon");
        exit;
    }
    // ---------------------------------

    // Kontrolden geçerse kayıt işlemine devam et
    $sorgu = $db->prepare("INSERT INTO users SET ad_soyad=?, email=?, telefon=?, sifre=?, profil_resmi=?");
    $ekle = $sorgu->execute([$ad_soyad, $email, $telefon, $sifre, $varsayilan_resim]);

    if ($ekle) {
        header("Location: giris.php?kayit=basarili");
        exit;
    } else {
        header("Location: kayit.php?hata=veritabani");
        exit;
    }
}

// 2. GİRİŞ İŞLEMİ
if (isset($_GET['durum']) && $_GET['durum'] == "giris") {
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];

    $sorgu = $db->prepare("SELECT * FROM users WHERE email = ?");
    $sorgu->execute([$email]);
    $user = $sorgu->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($sifre, $user['sifre'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['ad_soyad'];

        if (isset($_POST['beni_hatirla'])) {
            $token = bin2hex(random_bytes(20));
            $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?")->execute([$token, $user['id']]);
            setcookie("remember_user", $token, time() + (86400 * 30), "/");
        }

        header("Location: index2.php");
        exit;
    } else {
        header("Location: giris.php?hata=hatali_giris");
        exit;
    }
}

// 3. PROFİL GÜNCELLEME İŞLEMİ
if (isset($_GET['durum']) && $_GET['durum'] == "profil_guncelle") {
    $ad_soyad = $_POST['ad_soyad'];
    $user_id = $_SESSION['user_id'];
    
    $resim_guncelleme_sql = "";
    $resim_parametreleri = [];

    if (isset($_FILES['profil_foto']) && $_FILES['profil_foto']['error'] == 0) {
        $gecerli_uzantilar = ['jpg', 'jpeg', 'png', 'webp'];
        $dosya_adi = $_FILES['profil_foto']['name'];
        $uzanti = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));

        if (in_array($uzanti, $gecerli_uzantilar)) {
            $yeni_resim_adi = uniqid() . "." . $uzanti;
            $yukleme_dizini = 'img/profil/';
            
            if (!file_exists($yukleme_dizini)) {
                mkdir($yukleme_dizini, 0777, true);
            }

            $hedef_yol = $yukleme_dizini . $yeni_resim_adi;

            if (move_uploaded_file($_FILES['profil_foto']['tmp_name'], $hedef_yol)) {
                $resim_guncelleme_sql = ", profil_resmi=?";
                $resim_parametreleri[] = $yeni_resim_adi;
            }
        }
    }

    if (!empty($_POST['yeni_sifre'])) {
        $yeni_sifre = password_hash($_POST['yeni_sifre'], PASSWORD_BCRYPT);
        $sql = "UPDATE users SET ad_soyad=?, sifre=? $resim_guncelleme_sql WHERE id=?";
        $parametreler = array_merge([$ad_soyad, $yeni_sifre], $resim_parametreleri, [$user_id]);
    } else {
        $sql = "UPDATE users SET ad_soyad=? $resim_guncelleme_sql WHERE id=?";
        $parametreler = array_merge([$ad_soyad], $resim_parametreleri, [$user_id]);
    }

    $sorgu = $db->prepare($sql);
    $guncelle = $sorgu->execute($parametreler);

    if ($guncelle) {
        $_SESSION['user_name'] = $ad_soyad;
        header("Location: index2.php?islem=basarili");
        exit;
    } else {
        header("Location: profil_duzenle.php?hata=guncellenemedi");
        exit;
    }
}

// 4. HESAP SİLME İŞLEMİ
if (isset($_GET['durum']) && $_GET['durum'] == "hesap_sil") {
    $user_id = $_SESSION['user_id'];

    $sorgu = $db->prepare("DELETE FROM users WHERE id = ?");
    $sil = $sorgu->execute([$user_id]);

    if ($sil) {
        session_destroy();
        setcookie("remember_user", "", time() - 3600, "/");
        header("Location: index.php?islem=hesap_silindi");
        exit;
    } else {
        header("Location: profil_duzenle.php?hata=silinemedi");
        exit;
    }
}

// --- İLAN EKLEME İŞLEMİ ---
if (isset($_GET['durum']) && $_GET['durum'] == "ilan_ekle") {
    $ekleyen_id = $_SESSION['user_id'];

    $limit_sorgu = $db->prepare("SELECT COUNT(*) as toplam FROM ilanlar WHERE ekleyen_id = ?");
    $limit_sorgu->execute([$ekleyen_id]);
    $limit_sonuc = $limit_sorgu->fetch(PDO::FETCH_ASSOC);

    if ($limit_sonuc['toplam'] >= 3) {
        header("Location: ilanlarim.php?hata=limit_doldu");
        exit;
    }

    $baslik   = $_POST['baslik'];
    $fiyat    = $_POST['fiyat'];
    $sehir_id = $_POST['sehir_id'];
    $telefon  = $_POST['telefon'];
    $aciklama = $_POST['aciklama'];
    
    // YENİ ENTEGRASYON SÜTUNLARI (Gelen form tipine göre dinamik veriler)
    $ilan_tipi      = isset($_POST['ilan_tipi']) ? $_POST['ilan_tipi'] : 'Araba';
    $pazarlama_tipi = isset($_POST['pazarlama_tipi']) ? $_POST['pazarlama_tipi'] : 'Satılık';
    
    // Eğer emlak ise form adlarından, araba ise standart vites/km üzerinden atanır
    $metrekare  = ($ilan_tipi == 'Emlak') ? $_POST['kilometre'] : null;
    $oda_sayisi = ($ilan_tipi == 'Emlak') ? $_POST['vites_tipi'] : null;

    // FİX KOD: Emlak ilanlarında marka ve model ID'si 0 yerine veritabanına NULL (boş) olarak post edilir.
    $marka_id   = ($ilan_tipi == 'Araba' && !empty($_POST['marka_id'])) ? $_POST['marka_id'] : null;
    $model_id   = ($ilan_tipi == 'Araba' && !empty($_POST['model_id'])) ? $_POST['model_id'] : null;
    
    $yakit_tipi = ($ilan_tipi == 'Araba') ? $_POST['yakit_tipi'] : null;
    $vites_tipi = ($ilan_tipi == 'Araba') ? $_POST['vites_tipi'] : null;
    $kilometre  = $_POST['kilometre'];
    $model_yili = ($ilan_tipi == 'Araba') ? $_POST['model_yili'] : 0;

    $yuklenen_dosyalar = [];
    $ana_resim = "";

    if (isset($_FILES['ilan_resimleri'])) {
        $dosya_sayisi = count($_FILES['ilan_resimleri']['name']);
        if (!file_exists("img/ilanlar/")) { mkdir("img/ilanlar/", 0777, true); }

        for ($i = 0; $i < $dosya_sayisi; $i++) {
            if ($i >= 10) break; 
            if ($_FILES['ilan_resimleri']['error'][$i] == 0) {
                $gecerli_uzantilar = ['jpg', 'jpeg', 'png', 'webp'];
                $dosya_adi = $_FILES['ilan_resimleri']['name'][$i];
                $uzanti = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));

                if (in_array($uzanti, $gecerli_uzantilar)) {
                    $yeni_ad = uniqid() . "_$i." . $uzanti;
                    $yol = "img/ilanlar/" . $yeni_ad;
                    if (move_uploaded_file($_FILES['ilan_resimleri']['tmp_name'][$i], $yol)) {
                        $yuklenen_dosyalar[] = $yeni_ad;
                        if ($i == 0) { $ana_resim = $yeni_ad; }
                    }
                }
            }
        }
    }

    if (!empty($ana_resim)) {
        $tum_resimler_json = json_encode($yuklenen_dosyalar);
        
        $sorgu = $db->prepare("INSERT INTO ilanlar SET 
            baslik=?, marka_id=?, model_id=?, fiyat=?, sehir_id=?, 
            yakit_tipi=?, vites_tipi=?, telefon=?, aciklama=?, 
            ilan_resmi=?, tum_resimler=?, ekleyen_id=?,
            kilometre=?, model_yili=?, ilan_tipi=?, pazarlama_tipi=?, metrekare=?, oda_sayisi=?");
            
        $ekle = $sorgu->execute([
            $baslik, $marka_id, $model_id, $fiyat, $sehir_id, 
            $yakit_tipi, $vites_tipi, $telefon, $aciklama, 
            $ana_resim, $tum_resimler_json, $ekleyen_id,
            $kilometre, $model_yili, $ilan_tipi, $pazarlama_tipi, $metrekare, $oda_sayisi
        ]);
        
        if ($ekle) { 
            header("Location: ilanlarim.php?islem=basarili"); 
            exit; 
        }
    }

    header("Location: ilanlarim.php?hata=dosya_yuklenemedi");
    exit;
}

// --- İLAN SİLME İŞLEMİ ---
if (isset($_GET['durum']) && $_GET['durum'] == "ilan_sil") {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    $sil = $db->prepare("DELETE FROM ilanlar WHERE id=? AND ekleyen_id=?");
    $sil->execute([$id, $user_id]);
    header("Location: ilanlarim.php?silindi=tamam");
    exit;
}

// --- FAVORİ EKLEME / SİLME İŞLEMİ ---
if (isset($_GET['durum']) && $_GET['durum'] == "favori_islem") {
    if (!isset($_SESSION['user_id'])) { echo "giris_yapmali"; exit; }
    
    $user_id = $_SESSION['user_id'];
    $ilan_id = $_GET['ilan_id'];

    $kontrol = $db->prepare("SELECT * FROM favoriler WHERE user_id = ? AND ilan_id = ?");
    $kontrol->execute([$user_id, $ilan_id]);
    
    if ($kontrol->rowCount() > 0) {
        $sil = $db->prepare("DELETE FROM favoriler WHERE user_id = ? AND ilan_id = ?");
        $sil->execute([$user_id, $ilan_id]);
        echo "silindi";
    } else {
        $ekle = $db->prepare("INSERT INTO favoriler (user_id, ilan_id) VALUES (?, ?)");
        $ekle->execute([$user_id, $ilan_id]);
        echo "eklendi";
    }
    exit;
}

// --- MESAJ GÖNDERME İŞLEMİ ---
if (isset($_GET['durum']) && $_GET['durum'] == "mesaj_gonder") {
    if (!isset($_SESSION['user_id'])) { exit; }

    $ilan_id      = $_POST['ilan_id'];
    $alici_id     = $_POST['alici_id'];
    $gonderen_id  = $_SESSION['user_id'];
    $mesaj_icerik = htmlspecialchars($_POST['mesaj_icerik']); // Güvenlik için temizle

    if (!empty($mesaj_icerik)) {
        $sorgu = $db->prepare("INSERT INTO mesajlar (ilan_id, gonderen_id, alici_id, mesaj_icerik) VALUES (?, ?, ?, ?)");
        $ekle = $sorgu->execute([$ilan_id, $gonderen_id, $alici_id, $mesaj_icerik]);

        if ($ekle) {
            header("Location: ilan_detay.php?id=$ilan_id&mesaj=basarili");
        } else {
            header("Location: ilan_detay.php?id=$ilan_id&hata=mesaj_gitmedi");
        }
    } else {
        header("Location: ilan_detay.php?id=$ilan_id&hata=bos_mesaj");
    }
    exit;
}

// --- İLAN GÜNCELLEME İŞLEMİ ---
if (isset($_GET['durum']) && $_GET['durum'] == "ilan_guncelle") {
    $id         = $_POST['id'];
    $baslik     = $_POST['baslik'];
    $fiyat      = $_POST['fiyat'];
    $kilometre  = $_POST['kilometre'];
    $model_yili = !empty($_POST['model_yili']) ? $_POST['model_yili'] : 0;
    $aciklama   = $_POST['aciklama'];
    $user_id    = $_SESSION['user_id'];

    // İlana ait mevcut tipi bulup metrekare/oda sayısı sütun güncellemelerini korumak için çekiyoruz
    $tip_sorgu = $db->prepare("SELECT ilan_tipi FROM ilanlar WHERE id = ?");
    $tip_sorgu->execute([$id]);
    $mevcut_tip = $tip_sorgu->fetchColumn();

    if($mevcut_tip == 'Emlak') {
        $db->prepare("UPDATE ilanlar SET metrekare = ?, oda_sayisi = ? WHERE id = ?")->execute([$kilometre, $_POST['vites_tipi'] ?? null, $id]);
    }

    // Sadece ilanın sahibi olan kullanıcı güncelleyebilir
    $sorgu = $db->prepare("UPDATE ilanlar SET 
        baslik=?, 
        fiyat=?, 
        kilometre=?, 
        model_yili=?, 
        aciklama=? 
        WHERE id=? AND ekleyen_id=?");
        
    $guncelle = $sorgu->execute([
        $baslik, 
        $fiyat, 
        $kilometre, 
        $model_yili, 
        $aciklama, 
        $id, 
        $user_id
    ]);

    if ($guncelle) {
        header("Location: ilanlarim.php?islem=guncellendi");
        exit;
    } else {
        header("Location: ilanlarim.php?hata=guncellenemedi");
        exit;
    }
}

// --- MESAJLARI ÇEKME VE SOHBET EKRANINI OLUŞTURMA (AJAX) ---
if (isset($_GET['durum']) && $_GET['durum'] == "mesaj_cek") {
    $kisi_id = $_GET['kisi_id'];
    $user_id = $_SESSION['user_id'];

    // Karşıdaki kişinin adını al
    $kisi_sorgu = $db->prepare("SELECT ad_soyad FROM users WHERE id = ?");
    $kisi_sorgu->execute([$kisi_id]);
    $kisi = $kisi_sorgu->fetch(PDO::FETCH_ASSOC);

    // İki kişi arasındaki tüm mesaj geçmişini getir
    $m_sorgu = $db->prepare("
        SELECT * FROM mesajlar 
        WHERE (gonderen_id = ? AND alici_id = ?) OR (gonderen_id = ? AND alici_id = ?) 
        ORDER BY tarih ASC
    ");
    $m_sorgu->execute([$user_id, $kisi_id, $kisi_id, $user_id]);
    $mesajlar = $m_sorgu->fetchAll(PDO::FETCH_ASSOC);

    // Gelen mesajları "okundu" olarak işaretle
    $db->prepare("UPDATE mesajlar SET okundu_bilgisi = 1 WHERE gonderen_id = ? AND alici_id = ?")->execute([$kisi_id, $user_id]);

    // Üst Bilgi Çubuğu
    echo '<div class="chat-header">
            <span class="fw-bold fs-5 text-white">'.$kisi['ad_soyad'].'</span>
          </div>';

    // Mesajlaşma Alanı
    echo '<div class="chat-messages" id="chat-messages-area">';
    foreach($mesajlar as $m) {
        $sinif = ($m['gonderen_id'] == $user_id) ? 'bubble-out' : 'bubble-in';
        echo '<div class="bubble '.$sinif.'">
                '.$m['mesaj_icerik'].'
                <span class="msg-time">'.date('H:i', strtotime($m['tarih'])).'</span>
              </div>';
    }
    echo '</div>';

    echo '<div class="chat-footer">
            <form onsubmit="sendMessage(event)">
                <input type="hidden" name="alici_id" value="'.$kisi_id.'">
                <div class="input-group-noir">
                    <input type="text" name="mesaj_icerik" class="form-control chat-input" placeholder="Cevabınızı buraya yazın..." required autocomplete="off">
                    <button type="submit" class="btn-send"><i class="fas fa-paper-plane"></i></button>
                </div>
            </form>
          </div>';
    exit;
}

// --- CEVAP MESAJINI KAYDETME (AJAX) ---
if (isset($_GET['durum']) && $_GET['durum'] == "mesaj_gonder_ajax") {
    $alici_id = $_POST['alici_id'];
    $icerik = htmlspecialchars($_POST['mesaj_icerik']);
    $gonderen_id = $_SESSION['user_id'];

    $sorgu = $db->prepare("INSERT INTO mesajlar (ilan_id, gonderen_id, alici_id, mesaj_icerik) VALUES (0, ?, ?, ?)");
    $ekle = $sorgu->execute([$gonderen_id, $alici_id, $icerik]);

    echo json_encode(['status' => $ekle ? 'success' : 'error']);
    exit;
}
?>