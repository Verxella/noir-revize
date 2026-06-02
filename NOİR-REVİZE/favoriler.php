<?php 
session_start();
// Giriş kontrolü
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
    // Senin islem.php'deki bağlantı ismin olan $db değişkenini kullanıyoruz
    $db = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    // Kullanıcı profil resmini çek (Navbar için)
    $u_id = $_SESSION['user_id'];
    $u_sorgu = $db->prepare("SELECT profil_resmi FROM users WHERE id = ?");
    $u_sorgu->execute([$u_id]);
    $kullanici = $u_sorgu->fetch(PDO::FETCH_ASSOC);

    // --- SADECE FAVORİYE EKLENEN İLANLARI ÇEK ---
    // JOIN kullanarak favoriler tablosundan ilan bilgilerine ulaşıyoruz
    $fav_ilanlar_sorgu = $db->prepare("
        SELECT i.*, m.marka_adi as marka, mo.model_adi as model, s.sehir_adi as sehir 
        FROM favoriler f
        INNER JOIN ilanlar i ON f.ilan_id = i.id
        LEFT JOIN markalar m ON i.marka_id = m.id
        LEFT JOIN modeller mo ON i.model_id = mo.id
        LEFT JOIN sehirler s ON i.sehir_id = s.id
        WHERE f.user_id = ? 
        ORDER BY f.id DESC
    ");
    $fav_ilanlar_sorgu->execute([$u_id]);
    $ilanlar = $fav_ilanlar_sorgu->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Hata: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorilerim | NOIR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            background-color: #121212; color: #fff; font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1920&q=80');
            background-size: cover; background-attachment: fixed; min-height: 100vh;
        }
        .navbar { background: rgba(18, 18, 18, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1); }
        .navbar-brand { color: #0d6efd !important; letter-spacing: 3px; font-weight: 800; }
        .user-profile-circle { width: 40px; height: 40px; background-color: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        
        /* PROFİL DROPDOWN SİYAH ARKAPLAN */
        .dropdown-menu {
            background-color: #000000 !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 8px;
        }
        .dropdown-item {
            color: #ffffff !important;
            padding: 10px 15px;
            border-radius: 8px;
            transition: 0.3s;
        }
        .dropdown-item:hover { background-color: rgba(255, 255, 255, 0.1) !important; }

        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 30px; }
        .car-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; overflow: hidden; transition: 0.3s; position: relative; }
        .car-card:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.07); }
        .car-img { height: 180px; object-fit: cover; }
        .btn-detail { background-color: #0d6efd; color: #fff !important; border: none; font-weight: 600; border-radius: 8px; }
        .page-content-wrapper { padding: 40px 15px; }
        
        .remove-fav { 
            position: absolute; top: 15px; right: 15px; 
            background: rgba(0,0,0,0.5); border: none; border-radius: 50%; 
            width: 35px; height: 35px; color: #ff4757; transition: 0.3s; 
            display: flex; align-items: center; justify-content: center;
        }
        .remove-fav:hover { background: #ff4757; color: white; transform: scale(1.1); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index2.php">NOIR</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item me-4"><a class="nav-link" href="ilanlarim.php">İlanlarım</a></li>
                <li class="nav-item"><a class="nav-link active" href="favoriler.php">Favoriler</a></li>
            </ul>
            <div class="dropdown">
                <div class="d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown" style="cursor:pointer;">
                    <div class="user-profile-circle me-2">
                        <?php echo !empty($kullanici['profil_resmi']) ? '<img src="img/profil/'.$kullanici['profil_resmi'].'" style="width:100%;height:100%;object-fit:cover;">' : strtoupper(substr($_SESSION['user_name'],0,1)); ?>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="profil_duzenle.php"><i class="fas fa-user-edit me-2"></i> Profilim</a></li>
                    <li><hr class="dropdown-divider" style="border-color: rgba(255,255,255,0.1);"></li>
                    <li><a class="dropdown-item text-danger" href="cikis.php"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container page-content-wrapper">
    <div class="mb-5">
        <h2 class="fw-bold"><i class="fas fa-heart me-3 text-danger"></i>Favori İlanlarım</h2>
        <p class="text-white-50">Beğendiğiniz ve takibe aldığınız tüm araçlar burada listelenir.</p>
    </div>

    <div class="row g-4">
        <?php if($ilanlar): foreach($ilanlar as $ilan): ?>
            <div class="col-md-4" id="fav-row-<?php echo $ilan['id']; ?>">
                <div class="car-card h-100 shadow-lg">
                    <!-- Favorilerden Kaldır Butonu -->
                    <button class="remove-fav" onclick="removeFavorite(<?php echo $ilan['id']; ?>)" title="Favorilerden Kaldır">
                        <i class="fas fa-heart"></i>
                    </button>

                    <img src="img/ilanlar/<?php echo $ilan['ilan_resmi']; ?>" class="car-img w-100" alt="Araç">
                    <div class="p-3">
                        <h5 class="fw-bold text-truncate text-white"><?php echo $ilan['baslik']; ?></h5>
                        <p class="text-white-50 small mb-2">
                            <?php echo ($ilan['marka'] ?? 'Bilinmiyor')." ".($ilan['model'] ?? '')." | ".($ilan['sehir'] ?? ''); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-primary fw-bold fs-5"><?php echo number_format($ilan['fiyat'],0,',','.'); ?> TL</span>
                            <a href="ilan_detay.php?id=<?php echo $ilan['id']; ?>" class="btn btn-detail btn-sm px-3">Detaylar</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="col-12 text-center py-5 glass-card">
                <i class="far fa-heart fa-3x text-white-50 mb-3"></i>
                <p class="lead">Henüz favori ilanınız bulunmuyor.</p>
                <a href="index2.php" class="btn btn-primary mt-2">İlanları Keşfet</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="py-4 text-center mt-5" style="background: rgba(0,0,0,0.3);">
    <p class="text-white-50 small mb-0">&copy; 2026 NOIR. Tüm hakları saklıdır.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function removeFavorite(ilanId) {
        if(confirm('Bu ilanı favorilerinizden kaldırmak istediğinize emin misiniz?')) {
            // islem.php'ye favori durumunu değiştirmesi için istek atıyoruz
            fetch('islem.php?durum=favori_islem&ilan_id=' + ilanId)
            .then(res => {
                // Sayfayı yenilemeden kartı ekrandan siliyoruz
                const card = document.getElementById('fav-row-' + ilanId);
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    card.remove();
                    // Eğer hiç kart kalmadıysa sayfayı yenile ki "Henüz favori yok" mesajı çıksın
                    if(document.querySelectorAll('.car-card').length === 0) {
                        location.reload();
                    }
                }, 300);
            });
        }
    }
</script>
</body>
</html>