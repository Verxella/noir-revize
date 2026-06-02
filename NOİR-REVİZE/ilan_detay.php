<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}

$host = "localhost"; $user = "root"; $pass = ""; $db_name = "noir_db";
try {
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $pass);
    
    if(!isset($_GET['id'])) { header("Location: index2.php"); exit; }
    $id = $_GET['id'];

    // SORGUNUN EN GÜNCEL HALİ
    $sorgu = $db->prepare("
        SELECT i.*, m.marka_adi, mo.model_adi, s.sehir_adi, u.ad_soyad as satici_adi
        FROM ilanlar i
        LEFT JOIN markalar m ON i.marka_id = m.id
        LEFT JOIN modeller mo ON i.model_id = mo.id
        LEFT JOIN sehirler s ON i.sehir_id = s.id
        LEFT JOIN users u ON i.ekleyen_id = u.id
        WHERE i.id = ?
    ");
    $sorgu->execute([$id]);
    $ilan = $sorgu->fetch(PDO::FETCH_ASSOC);

    if(!$ilan) { die("İlan bulunamadı."); }
    $resimler = json_decode($ilan['tum_resimler'], true);

    // İlan tipini güvenli değişkene atayalım
    $ilan_tipi = isset($ilan['ilan_tipi']) ? $ilan['ilan_tipi'] : 'Araba';

    $u_sorgu = $db->prepare("SELECT profil_resmi FROM users WHERE id = ?");
    $u_sorgu->execute([$_SESSION['user_id']]);
    $kullanici = $u_sorgu->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) { die("Hata: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $ilan['baslik']; ?> | NOIR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            background-color: #0a0a0a; 
            color: #fff; 
            font-family: 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top right, #1a1a1a, #0a0a0a);
            min-height: 100vh;
        }
        .navbar { background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(15px); border-bottom: 1px solid rgba(255,255,255,0.05); }
        .navbar-brand { color: #0d6efd !important; letter-spacing: 4px; font-weight: 900; }
        
        /* Galeri Güzelleştirme */
        .gallery-container { border-radius: 24px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 1px solid rgba(255,255,255,0.05); }
        .carousel-item img { height: 550px; object-fit: cover; }
        
        /* Büyük Fiyat ve Başlık */
        .car-title { font-size: 2.8rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 5px; }
        .price-text { font-size: 3rem; font-weight: 900; color: #0d6efd; text-shadow: 0 0 20px rgba(13,110,253,0.3); }
        
        /* Şeffaf Bilgi Kartları */
        .glass-card { 
            background: rgba(255, 255, 255, 0.03); 
            backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
            border-radius: 24px; 
            padding: 30px; 
        }

        .spec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        
        /* ORTALAMA İÇİN GÜNCELLENEN KISIM */
        .spec-box { 
            background: rgba(255,255,255,0.02); 
            padding: 20px; 
            border-radius: 15px; 
            border: 1px solid rgba(255,255,255,0.05);
            transition: 0.3s;
            /* İçerikleri Merkeze Almak İçin Flex */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .spec-box:hover { background: rgba(255,255,255,0.05); border-color: #0d6efd; }
        .spec-label { color: rgba(255,255,255,0.4); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 5px; }
        .spec-value { font-size: 1.25rem; font-weight: 700; color: #fff; }

        /* Satıcı Paneli (İstediğin Siyah-Gri Şeffaf Yapı) */
        .seller-card { 
            background: rgba(20, 20, 20, 0.6); 
            backdrop-filter: blur(25px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 24px; 
            padding: 35px;
            position: sticky;
            top: 100px;
        }
        .btn-call { 
            background: #fff; 
            color: #000; 
            font-weight: 800; 
            font-size: 1.1rem;
            border: none; 
            border-radius: 15px; 
            padding: 18px; 
            width: 100%; 
            transition: 0.4s;
            box-shadow: 0 10px 20px rgba(255,255,255,0.1);
        }
        .btn-call:hover { background: #0d6efd; color: #fff; transform: translateY(-3px); box-shadow: 0 15px 30px rgba(13,110,253,0.4); }
        
        /* Mesaj Butonu Stili */
        .btn-message {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border: 1px solid rgba(13, 110, 253, 0.3);
            font-weight: 700;
            padding: 15px;
            border-radius: 15px;
            width: 100%;
            margin-top: 15px;
            transition: 0.3s;
        }
        .btn-message:hover {
            background: #0d6efd;
            color: #fff;
        }

        .description-text { font-size: 1.1rem; line-height: 1.9; color: rgba(255,255,255,0.7); }
        .user-profile-circle { width: 40px; height: 40px; background-color: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        
        .modal-content { background: #1a1a1a; border: 1px solid #333; border-radius: 25px; color: #fff; }
        .quick-msg { border-radius: 10px; transition: 0.2s; cursor: pointer; }
        .quick-msg:hover { background: #0d6efd !important; color: #fff !important; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index2.php">NOIR</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item me-4"><a class="nav-link" href="ilanlarim.php">İlanlarım</a></li>
                <li class="nav-item"><a class="nav-link" href="favoriler.php">Favoriler</a></li>
            </ul>
            <div class="dropdown">
                <div class="d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown" style="cursor:pointer;">
                    <div class="user-profile-circle me-2">
                        <?php echo !empty($kullanici['profil_resmi']) ? '<img src="img/profil/'.$kullanici['profil_resmi'].'" style="width:100%;height:100%;object-fit:cover;">' : strtoupper(substr($_SESSION['user_name'],0,1)); ?>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end bg-black border-secondary">
                    <li><a class="dropdown-item text-white" href="profil_duzenle.php">Profilim</a></li>
                    <li><hr class="dropdown-divider" style="border-color: rgba(255,255,255,0.1);"></li>
                    <li><a class="dropdown-item text-danger" href="cikis.php">Çıkış Yap</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-3">
    <?php if(isset($_GET['mesaj']) && $_GET['mesaj'] == 'basarili'): ?>
        <div class="alert alert-success border-0 bg-success text-white mb-4" style="border-radius: 15px;">
            <i class="fas fa-check-circle me-2"></i> Mesajınız başarıyla gönderildi!
        </div>
    <?php endif; ?>

    <div class="row g-5">
        <div class="col-lg-7">
            <div id="mainCarousel" class="carousel slide gallery-container mb-5" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php if($resimler): foreach($resimler as $index => $resim): ?>
                        <div class="carousel-item <?php echo ($index == 0) ? 'active' : ''; ?>">
                            <img src="img/ilanlar/<?php echo $resim; ?>" class="d-block w-100" alt="İlan Görseli">
                        </div>
                    <?php endforeach; endif; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>

            <h3 class="fw-bold mb-4" style="letter-spacing: -1px;">
                <?php echo ($ilan_tipi == 'Emlak') ? 'Mülk Hakkında Bilgiler' : 'Araç Hakkında Bilgiler'; ?>
            </h3>
            <div class="glass-card mb-5">
                <div class="description-text">
                    <?php echo nl2br(htmlspecialchars($ilan['aciklama'])); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="seller-card">
                <div class="mb-4">
                    <h1 class="car-title"><?php echo $ilan['baslik']; ?></h1>
                    <p class="fs-5 text-white-50">
                        <?php 
                        if ($ilan_tipi == 'Emlak') {
                            echo "Premium Gayrimenkul / " . htmlspecialchars($ilan['pazarlama_tipi'] ?? 'Satılık');
                        } else {
                            echo htmlspecialchars(($ilan['marka_adi'] ?? '') . " " . ($ilan['model_adi'] ?? '')); 
                        }
                        ?>
                    </p>
                    <div class="price-text mt-3"><?php echo number_format($ilan['fiyat'], 0, ',', '.'); ?> TL</div>
                </div>

                <div class="spec-grid mb-5">
                    <div class="spec-box">
                        <span class="spec-label">Şehir</span>
                        <span class="spec-value"><?php echo htmlspecialchars($ilan['sehir_adi']); ?></span>
                    </div>
                    <div class="spec-box">
                        <span class="spec-label">İlan Tarihi</span>
                        <span class="spec-value"><?php echo date('d.m.y', strtotime($ilan['created_at'] ?? $ilan['tarih'] ?? 'now')); ?></span>
                    </div>

                    <?php if ($ilan_tipi == 'Emlak'): ?>
                        <div class="spec-box">
                            <span class="spec-label">Oda Sayısı</span>
                            <span class="spec-value text-primary"><?php echo htmlspecialchars($ilan['oda_sayisi'] ?? $ilan['vites_tipi'] ?? 'Belirtilmedi'); ?></span>
                        </div>
                        <div class="spec-box">
                            <span class="spec-label">Metrekare</span>
                            <span class="spec-value"><?php echo htmlspecialchars($ilan['metrekare'] ?? $ilan['kilometre'] ?? '0'); ?> m²</span>
                        </div>
                        <div class="spec-box">
                            <span class="spec-label">İlan Türü</span>
                            <span class="spec-value"><?php echo htmlspecialchars($ilan['pazarlama_tipi'] ?? 'Satılık'); ?></span>
                        </div>
                        <div class="spec-box">
                            <span class="spec-label">Kategori</span>
                            <span class="spec-value">Emlak</span>
                        </div>
                    <?php else: ?>
                        <div class="spec-box">
                            <span class="spec-label">Model Yılı</span>
                            <span class="spec-value"><?php echo htmlspecialchars($ilan['model_yili'] ?? 'Belirtilmedi'); ?></span>
                        </div>
                        <div class="spec-box">
                            <span class="spec-label">Kilometre</span>
                            <span class="spec-value"><?php echo number_format($ilan['kilometre'] ?? 0, 0, ',', '.'); ?> KM</span>
                        </div>
                        <div class="spec-box">
                            <span class="spec-label">Yakıt</span>
                            <span class="spec-value"><?php echo htmlspecialchars($ilan['yakit_tipi']); ?></span>
                        </div>
                        <div class="spec-box">
                            <span class="spec-label">Vites</span>
                            <span class="spec-value"><?php echo htmlspecialchars($ilan['vites_tipi']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="pt-4 border-top border-secondary">
                    <div class="d-flex align-items-center mb-4">
                        <div class="user-profile-circle me-3" style="width:50px; height:50px; background:#222; font-size:1.2rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <span class="spec-label" style="margin-bottom:0;">Satıcı</span>
                            <div class="fw-bold fs-5"><?php echo htmlspecialchars($ilan['satici_adi']); ?></div>
                        </div>
                    </div>
                    
                    <a href="tel:<?php echo $ilan['telefon']; ?>" class="btn btn-call">
                        <i class="fas fa-phone-alt me-2"></i><?php echo htmlspecialchars($ilan['telefon']); ?>
                    </a>

                    <?php if($_SESSION['user_id'] != $ilan['ekleyen_id']): ?>
                    <button class="btn btn-message" data-bs-toggle="modal" data-bs-target="#mesajGonderModal">
                        <i class="fas fa-envelope me-2"></i>Satıcıya Mesaj Gönder
                    </button>
                    <?php endif; ?>

                    <p class="text-center text-white-50 small mt-3">İlan ile ilgili detaylı bilgi için satıcıyı arayın veya mesaj atın.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mesajGonderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold">Mesaj Gönder</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="islem.php?durum=mesaj_gonder" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="ilan_id" value="<?php echo $ilan['id']; ?>">
                    <input type="hidden" name="alici_id" value="<?php echo $ilan['ekleyen_id']; ?>">
                    
                    <div class="mb-3 text-center">
                        <p class="text-white-50 small">Alıcı: <span class="text-white fw-bold"><?php echo htmlspecialchars($ilan['satici_adi']); ?></span></p>
                    </div>

                    <div class="mb-3">
                        <label class="small text-white-50 mb-2">Mesajınız</label>
                        <textarea name="mesaj_icerik" id="mesaj_alanı" class="form-control bg-dark border-secondary text-white" rows="5" placeholder="<?php echo ($ilan_tipi == 'Emlak') ? 'Mülk hakkında sorularınızı buraya yazın...' : 'Araç hakkında sorularınızı buraya yazın...'; ?>" required></textarea>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <?php if($ilan_tipi == 'Emlak'): ?>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-white-50 quick-msg" onclick="hizliMesaj(this)">Mülk hala satılık / kiralık mı?</button>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-white-50 quick-msg" onclick="hizliMesaj(this)">Son fiyat ne olur?</button>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-white-50 quick-msg" onclick="hizliMesaj(this)">Görmeye ne zaman gelebilirim?</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-white-50 quick-msg" onclick="hizliMesaj(this)">Araç hala satılık mı?</button>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-white-50 quick-msg" onclick="hizliMesaj(this)">Son fiyat ne olur?</button>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-white-50 quick-msg" onclick="hizliMesaj(this)">Takas düşünüyor musunuz?</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary px-4">Gönder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="py-5 text-center mt-5" style="background: #000;">
    <p class="text-white-50 small mb-0">NOIR Luxury Automotive & Gayrimenkul &copy; 2026</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function hizliMesaj(btn) {
    document.getElementById('mesaj_alanı').value = btn.innerText;
}
</script>
</body>
</html>