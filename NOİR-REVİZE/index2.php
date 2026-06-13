<?php 
session_start();
// Giriş kontrolü: Oturum açılmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}

// VERİTABANI BAĞLANTISI
$host = "localhost";
$user = "root";
$pass = "";
$db   = "noir_db";

try {
    $db_conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    // Kullanıcı profil resmini çek
    $sorgu = $db_conn->prepare("SELECT profil_resmi FROM users WHERE id = ?");
    $sorgu->execute([$_SESSION['user_id']]);
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

    // --- BİLDİRİM SİSTEMİ (OKUNMAMIŞ MESAJ SAYISI) ---
    $unread_count = 0;
    if (isset($_SESSION['user_id'])) {
        $msg_check = $db_conn->prepare("SELECT COUNT(*) FROM mesajlar WHERE alici_id = ? AND okundu_bilgisi = 0");
        $msg_check->execute([$_SESSION['user_id']]);
        $unread_count = $msg_check->fetchColumn();
    }

    // --- MARKA TABLOSUNDAN MARKALARI ÇEK ---
    $marka_sorgu = $db_conn->query("SELECT * FROM markalar ORDER BY marka_adi ASC");
    $marka_listesi = $marka_sorgu->fetchAll(PDO::FETCH_ASSOC);

    // --- ŞEHİRLER TABLOSUNDAN ŞEHİRLERİ ÇEK ---
    $sehir_sorgu = $db_conn->query("SELECT * FROM sehirler ORDER BY sehir_adi ASC");
    $sehir_listesi = $sehir_sorgu->fetchAll(PDO::FETCH_ASSOC);

    // --- İLANLARI VE GELİŞMİŞ FİLTRELEMEYİ YÖNETEN KISIM ---
    $user_id = $_SESSION['user_id'];
    // Favori durumunu kontrol etmek için LEFT JOIN favoriler eklendi
    $sql = "SELECT i.*, m.marka_adi as marka, mo.model_adi as model, s.sehir_adi as sehir, f.id as favori_id 
            FROM ilanlar i
            LEFT JOIN markalar m ON i.marka_id = m.id
            LEFT JOIN modeller mo ON i.model_id = mo.id
            LEFT JOIN sehirler s ON i.sehir_id = s.id
            LEFT JOIN favoriler f ON (i.id = f.ilan_id AND f.user_id = $user_id)
            WHERE 1=1";
    $params = [];

    if (isset($_GET['ara']) && !empty($_GET['ara'])) {
        $sql .= " AND (i.baslik LIKE ? OR m.marka_adi LIKE ? OR mo.model_adi LIKE ? OR i.aciklama LIKE ?)";
        $term = "%" . $_GET['ara'] . "%";
        array_push($params, $term, $term, $term, $term);
    }
    if (isset($_GET['marka_id']) && !empty($_GET['marka_id'])) {
        $sql .= " AND i.marka_id = ?";
        $params[] = $_GET['marka_id'];
    }
    if (isset($_GET['model_id']) && !empty($_GET['model_id'])) {
        $sql .= " AND i.model_id = ?";
        $params[] = $_GET['model_id'];
    }
    if (isset($_GET['sehir_id']) && !empty($_GET['sehir_id'])) {
        $sql .= " AND i.sehir_id = ?";
        $params[] = $_GET['sehir_id'];
    }
    
    // --- GAYRİMENKUL ENTEGRASYONU İÇİN EKLENEN YENİ SORGULAR ---
    if (isset($_GET['ilan_tipi']) && !empty($_GET['ilan_tipi'])) {
        $sql .= " AND i.ilan_tipi = ?";
        $params[] = $_GET['ilan_tipi'];
    }
    if (isset($_GET['pazarlama_tipi']) && !empty($_GET['pazarlama_tipi'])) {
        $sql .= " AND i.pazarlama_tipi = ?";
        $params[] = $_GET['pazarlama_tipi'];
    }

    $sql .= " ORDER BY i.id DESC";
    $ilan_sorgu = $db_conn->prepare($sql);
    $ilan_sorgu->execute($params);
    $ilanlar = $ilan_sorgu->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $ilanlar = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOIR | Seçkin İlanlar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* KALICI KOYU TEMA AYARLARI */
        body { 
            background-color: #121212; 
            color: #ffffff !important; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            position: relative;
            overflow-x: hidden;
            margin: 0;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.15;
            z-index: -1;
            filter: grayscale(100%);
        }
        
        .navbar {
            background: rgba(18, 18, 18, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .navbar-brand { color: #0d6efd !important; letter-spacing: 3px; }
        
        .user-profile-circle {
            width: 40px; height: 40px;
            background-color: #262626; /* Instagram koyu gri tonu */
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.3s ease;
            overflow: hidden; border: 1px solid rgba(255,255,255,0.1);
        }

        /* --- SİYAH DROPDOWN MENÜ STİLLERİ --- */
        .dropdown-menu {
            background-color: #000000 !important;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
        }
        .dropdown-item {
            color: #ffffff !important;
            padding: 10px 15px;
            border-radius: 8px;
            transition: 0.3s;
        }
        .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
        }
        .dropdown-item.text-danger:hover {
            background-color: rgba(220,53,69,0.1) !important;
        }
        .dropdown-divider {
            border-color: rgba(255,255,255,0.1);
        }

        .filter-card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            padding: 25px; position: relative; z-index: 10;
        }

        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
            border-radius: 10px;
        }

        .form-select option {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
        }

        input:-webkit-autofill {
            -webkit-text-fill-color: white !important;
            -webkit-box-shadow: 0 0 0px 1000px #2d2d2d inset !important;
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        .car-card {
            border: none; border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            height: 100%;
            transition: transform 0.3s; position: relative;
        }
        .car-card:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.08); }
        .car-img { height: 200px; object-fit: cover; border-radius: 12px 12px 0 0; }
        
        .fav-btn {
            position: absolute; top: 15px; right: 15px;
            width: 35px; height: 35px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            border: none; border-radius: 50%;
            color: white; display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 5;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .fav-btn.active i { color: #ff4757 !important; }

        .text-primary { color: #0d6efd !important; }
        .btn-detail { background-color: #0d6efd; color: #fff !important; border: none; font-weight: 600; border-radius: 8px; }
        
        .footer {
            background-color: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(15px);
            padding: 80px 0 30px 0;
            margin-top: 100px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* --- PREMİUM GAYRİMENKUL KART ROZETİ --- */
        .badge-category {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            z-index: 2;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index2.php">NOIR</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item me-4"><a class="nav-link" href="ilanlarim.php">İlanlarım</a></li>
                <li class="nav-item me-4"><a class="nav-link" href="favoriler.php">Favoriler</a></li>
                <li class="nav-item me-4">
                    <a class="nav-link position-relative" href="mesajlarim.php">
                        Mesajlarım
                        <?php if($unread_count > 0): ?>
                            <span class="position-absolute top-2 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                <?php echo $unread_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
            <div class="dropdown">
                <div class="d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                    <span class="me-2 d-none d-lg-inline small">Hoş geldin, <?php echo $_SESSION['user_name']; ?></span>
                    <div class="user-profile-circle">
                        <?php 
                        if (!empty($kullanici['profil_resmi'])) {
                            echo '<img src="img/profil/'.$kullanici['profil_resmi'].'" alt="Profil" style="width:100%; height:100%; object-fit:cover;">';
                        } else {
                            // Instagram tarzı varsayılan gri ikon
                            echo '<i class="fas fa-user" style="color: #8e8e8e; font-size: 1.2rem;"></i>';
                        }
                        ?>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="profil_duzenle.php"><i class="fas fa-user-edit me-2"></i> Profilimi Düzenle</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="cikis.php"><i class="fas fa-sign-out-alt me-2"></i> Güvenli Çıkış</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<header class="hero-section text-center">
    <div class="py-5">
        <h1 class="display-4 fw-bold text-center">Noir Dünyasına Hoş Geldin</h1>
        <p class="lead text-center">Size özel en seçkin vasıta ve premium gayrimenkul ilanlarını keşfetmeye hemen başlayın.</p>
    </div>
</header>

<div class="container">
    <div class="filter-card">
        <form action="index2.php" method="GET" class="row g-3">
            <div class="col-md-12">
                <input type="text" name="ara" class="form-control" placeholder="Kelime, ilan başlığı veya detay arayın..." value="<?php echo htmlspecialchars($_GET['ara'] ?? ''); ?>">
            </div>
            
            <div class="col-md-3">
                <select name="ilan_tipi" id="ilan_tipi_select" class="form-select" onchange="kategoriDegistir(this.value)">
                    <option value="">Tüm Kategoriler</option>
                    <option value="Araba" <?php if(($_GET['ilan_tipi'] ?? '') == 'Araba') echo 'selected'; ?>>Vasıta (Araç)</option>
                    <option value="Emlak" <?php if(($_GET['ilan_tipi'] ?? '') == 'Emlak') echo 'selected'; ?>>Premium Gayrimenkul</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="pazarlama_tipi" class="form-select">
                    <option value="">İlan Durumu (Tümü)</option>
                    <option value="Satılık" <?php if(($_GET['pazarlama_tipi'] ?? '') == 'Satılık') echo 'selected'; ?>>Satılık</option>
                    <option value="Kiralık" <?php if(($_GET['pazarlama_tipi'] ?? '') == 'Kiralık') echo 'selected'; ?>>Kiralık</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="sehir_id" class="form-select">
                    <option value="">Tüm Şehirler</option>
                    <?php foreach($sehir_listesi as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php if(($_GET['sehir_id'] ?? '') == $s['id']) echo 'selected'; ?>><?php echo $s['sehir_adi']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
            </div>

            <div id="arac_filtreleri" class="row g-3 m-0 p-0" style="<?php echo (($_GET['ilan_tipi'] ?? '') == 'Emlak') ? 'display:none;' : ''; ?>">
                <div class="col-md-6 ps-0">
                    <select name="marka_id" id="marka_select" class="form-select" onchange="modelleriGetirFiltre(this.value)">
                        <option value="">Tüm Markalar</option>
                        <?php foreach($marka_listesi as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php if(($_GET['marka_id'] ?? '') == $m['id']) echo 'selected'; ?>><?php echo $m['marka_adi']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 pe-0">
                    <select name="model_id" id="model_select" class="form-select">
                        <option value="">Model Seçin</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="row mt-5 g-4">
        <h3 class="mb-2 fw-bold">Size Özel İlan Listesi</h3>

        <?php if (!empty($ilanlar)): ?>
            <?php foreach ($ilanlar as $ilan): ?>
                <div class="col-md-4">
                    <div class="card car-card shadow-sm">
                        <span class="badge-category text-primary">
                            <i class="<?php echo (($ilan['ilan_tipi'] ?? 'Araba') == 'Emlak') ? 'fas fa-home' : 'fas fa-car'; ?> me-1"></i>
                            <?php echo htmlspecialchars($ilan['ilan_tipi'] ?? 'Araba'); ?> / <?php echo htmlspecialchars($ilan['pazarlama_tipi'] ?? 'Satılık'); ?>
                        </span>

                        <button class="fav-btn <?php echo ($ilan['favori_id']) ? 'active' : ''; ?>" onclick="toggleFavorite(this, <?php echo $ilan['id']; ?>)">
                            <i class="<?php echo ($ilan['favori_id']) ? 'fas' : 'far'; ?> fa-heart"></i>
                        </button>
                        <img src="<?php echo !empty($ilan['ilan_resmi']) ? 'img/ilanlar/'.$ilan['ilan_resmi'] : 'https://via.placeholder.com/600x400/1e1e1e/ffffff?text=NOIR'; ?>" class="card-img-top car-img" alt="İlan Resmi" onerror="this.src='https://via.placeholder.com/600x400/1e1e1e/ffffff?text=NOIR'">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-truncate text-white"><?php echo $ilan['baslik']; ?></h5>
                            <p class="mb-1 small text-white-50">
                                <?php 
                                if(($ilan['ilan_tipi'] ?? 'Araba') == 'Emlak') {
                                    echo ($ilan['sehir'] ?? 'Şehir Yok') . " | Lüks Gayrimenkul";
                                } else {
                                    echo ($ilan['marka'] ?? 'Belirtilmedi') . " " . ($ilan['model'] ?? '') . " | " . ($ilan['sehir'] ?? 'Şehir Yok'); 
                                }
                                ?>
                            </p>
                            <p class="text-primary fw-bold mb-2"><?php echo number_format(($ilan['fiyat'] ?? 0), 0, ',', '.'); ?> TL</p>
                            <a href="ilan_detay.php?id=<?php echo $ilan['id']; ?>" class="btn btn-detail btn-sm w-100 mt-3">Detayları Gör</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="no-results">
                    <i class="fas fa-search-minus fa-3x text-primary mb-3"></i>
                    <h4 class="fw-bold">Sonuç Bulunamadı</h4>
                    <p class="text-white-50">Girdiğiniz filtre özelliklerine uygun bir ilan bulunmamaktadır.</p>
                    <a href="index2.php" class="btn btn-outline-primary mt-2">Tüm İlanları Gör</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row text-center">
            <div class="col-12">
                <p class="small mb-0 text-white-50">&copy; 2026 NOIR. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function kategoriDegistir(tip) {
        const aracFiltreleri = document.getElementById('arac_filtreleri');
        if(tip === 'Emlak') {
            aracFiltreleri.style.display = 'none';
        } else {
            aracFiltreleri.style.display = 'flex';
        }
    }

    function modelleriGetirFiltre(markaId) {
        const modelSelect = document.getElementById('model_select');
        modelSelect.innerHTML = '<option value="">Yükleniyor...</option>';
        if (!markaId) { modelSelect.innerHTML = '<option value="">Model Seçin</option>'; return; }

        fetch('islem.php?durum=modelleri_getir&marka_id=' + markaId)
            .then(res => res.json())
            .then(data => {
                modelSelect.innerHTML = '<option value="">Tüm Modeller</option>';
                data.forEach(m => { 
                    modelSelect.innerHTML += `<option value="${m.id}">${m.model_adi}</option>`; 
                });
            });
    }

    window.onload = () => {
        const markaId = document.getElementById('marka_select').value;
        if(markaId) {
            modelleriGetirFiltre(markaId);
        }
        // Sayfa yenilendiğinde seçili emlak filtresi varsa arayüzü güncelle
        const ilanTipi = document.getElementById('ilan_tipi_select').value;
        kategoriDegistir(ilanTipi);
    }

    function toggleFavorite(btn, ilanId) {
        btn.classList.toggle('active');
        const icon = btn.querySelector('i');
        
        if (btn.classList.contains('active')) {
            icon.classList.replace('far', 'fas');
        } else {
            icon.classList.replace('fas', 'far');
        }

        fetch('islem.php?durum=favori_islem&ilan_id=' + ilanId)
        .then(response => response.text())
        .then(data => {
            console.log("Sunucu Yanıtı:", data);
        })
        .catch(error => {
            console.error('Hata:', error);
        });
    }
</script>
</body>
</html>