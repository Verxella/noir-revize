<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}

// VERİTABANI BAĞLANTISI
$host = "localhost"; $user = "root"; $pass = ""; $db = "noir_db";
try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    // Giriş yapan kullanıcının ilan sayısını say
    $ilan_say_sorgu = $conn->prepare("SELECT COUNT(*) as toplam FROM ilanlar WHERE ekleyen_id = ?");
    $ilan_say_sorgu->execute([$_SESSION['user_id']]);
    $ilan_sonuc = $ilan_say_sorgu->fetch(PDO::FETCH_ASSOC);
    $toplam_ilan_sayisi = $ilan_sonuc['toplam'];
    $ilan_limiti = 3; // Standart kullanıcı limiti

    // Kullanıcı profilini çek (Navbar için)
    $u_sorgu = $conn->prepare("SELECT profil_resmi FROM users WHERE id = ?");
    $u_sorgu->execute([$_SESSION['user_id']]);
    $kullanici = $u_sorgu->fetch(PDO::FETCH_ASSOC);

    // JOIN kullanarak ilanları isimleriyle çek
    $ilan_sorgu = $conn->prepare("
        SELECT i.*, m.marka_adi as marka, mo.model_adi as model, s.sehir_adi as sehir 
        FROM ilanlar i
        LEFT JOIN markalar m ON i.marka_id = m.id
        LEFT JOIN modeller mo ON i.model_id = mo.id
        LEFT JOIN sehirler s ON i.sehir_id = s.id
        WHERE i.ekleyen_id = ? 
        ORDER BY i.id DESC
    ");
    $ilan_sorgu->execute([$_SESSION['user_id']]);
    $ilanlar = $ilan_sorgu->fetchAll(PDO::FETCH_ASSOC);

    // --- SEÇENEKLER İÇİN TABLOLARDAN VERİ ÇEKME ---
    $markalar = $conn->query("SELECT * FROM markalar ORDER BY marka_adi ASC")->fetchAll(PDO::FETCH_ASSOC);
    $sehirler = $conn->query("SELECT * FROM sehirler ORDER BY sehir_adi ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { die("Hata: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlanlarım | NOIR</title>
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
        
        .dropdown-menu {
            background-color: #000000 !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
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
        .dropdown-divider { border-color: rgba(255,255,255,0.1); }

        .glass-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 30px; }
        .car-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; overflow: hidden; transition: 0.3s; }
        .car-card:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.07); }
        .car-img { height: 180px; object-fit: cover; }
        #image-preview-container, #ev-image-preview-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 10px; min-height: 50px; }
        .preview-wrapper { position: relative; width: 80px; height: 80px; }
        .preview-wrapper img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #0d6efd; }
        
        .modal-content { background: #1e1e1e; border: 1px solid #333; border-radius: 20px; color: white; }
        
        /* AUTOFILL VE BEYAZ ARKAPLAN FIX */
        .form-control, .form-select { 
            background: rgba(255,255,255,0.05) !important; 
            border: 1px solid #444 !important; 
            color: white !important; 
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-background-clip: text;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px rgba(255, 255, 255, 0.01) !important;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.1) !important;
            border-color: #0d6efd !important;
            box-shadow: none;
            color: white !important;
        }
        .form-select option { background-color: #1e1e1e; color: white; }
        .form-control::placeholder { color: rgba(255, 255, 255, 0.4) !important; }
        .page-content-wrapper { padding: 40px 15px; }
        .btn-detail { background-color: #0d6efd; color: #fff !important; border: none; font-weight: 600; border-radius: 8px; text-decoration: none; display: block; text-align: center; }
        
        /* Dinamik Kategori Rozeti */
        .badge-category {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            z-index: 2;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index2.php">NOIR</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item me-4"><a class="nav-link active" href="ilanlarim.php">İlanlarım</a></li>
                <li class="nav-item"><a class="nav-link" href="favoriler.php">Favoriler</a></li>
            </ul>
            <div class="dropdown">
                <div class="d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown" style="cursor:pointer;">
                    <div class="user-profile-circle me-2">
                        <?php echo !empty($kullanici['profil_resmi']) ? '<img src="img/profil/'.$kullanici['profil_resmi'].'" style="width:100%;height:100%;object-fit:cover;">' : strtoupper(substr($_SESSION['user_name'],0,1)); ?>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="profil_duzenle.php"><i class="fas fa-user-edit me-2"></i> Profilim</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="cikis.php"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container page-content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-bold m-0"><i class="fas fa-folder-open me-3 text-primary"></i>İlanlarım</h2>
        
        <div class="d-flex align-items-center gap-2">
            <?php if($toplam_ilan_sayisi < $ilan_limiti): ?>
                <button class="btn btn-primary px-4 py-2" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#ilanEkleModal">
                    <i class="fas fa-car me-2"></i>Araç İlanı Yayınla
                </button>
                <button class="btn btn-outline-primary px-4 py-2" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#evIlanEkleModal">
                    <i class="fas fa-home me-2"></i>Ev İlanı Yayınla
                </button>
            <?php else: ?>
                <a href="profil_duzenle.php?islem=premium_yukselt" class="btn btn-warning px-4 py-2" style="border-radius: 12px; font-weight: 600;">
                    <i class="fas fa-crown me-2"></i>Limit Doldu (Premium'a Geç)
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <?php if($ilanlar): foreach($ilanlar as $ilan): ?>
            <div class="col-md-4">
                <div class="car-card h-100 shadow-lg position-relative">
                    <span class="badge-category text-primary">
                        <i class="<?php echo (($ilan['ilan_tipi'] ?? 'Araba') == 'Emlak') ? 'fas fa-home' : 'fas fa-car'; ?> me-1"></i>
                        <?php echo htmlspecialchars($ilan['ilan_tipi'] ?? 'Araba'); ?>
                    </span>

                    <img src="img/ilanlar/<?php echo $ilan['ilan_resmi']; ?>" class="car-img w-100" alt="İlan Resmi">
                    <div class="p-3">
                        <h5 class="fw-bold text-truncate"><?php echo $ilan['baslik']; ?></h5>
                        <p class="text-white-50 small mb-2">
                            <?php 
                            if(($ilan['ilan_tipi'] ?? 'Araba') == 'Emlak') {
                                echo ($ilan['sehir'] ?? 'Şehir Yok') . " | Lüks Gayrimenkul";
                            } else {
                                echo ($ilan['marka'] ?? 'Bilinmiyor')." ".($ilan['model'] ?? ''); 
                            }
                            ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-primary fw-bold fs-5"><?php echo number_format($ilan['fiyat'],0,',','.'); ?> TL</span>
                            <div>
                                <button class="btn btn-sm btn-outline-warning me-2" onclick="ilanDuzenle(<?php echo htmlspecialchars(json_encode($ilan)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="islem.php?durum=ilan_sil&id=<?php echo $ilan['id']; ?>" class="text-danger" onclick="return confirm('Silmek istediğine emin misin?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                        <a href="ilan_detay.php?id=<?php echo $ilan['id']; ?>" class="btn btn-detail btn-sm w-100">Detayları Gör</a>
                    </div>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="col-12 text-center py-5 glass-card">
                <i class="fas fa-folder-open fa-3x text-white-50 mb-3"></i>
                <p class="lead">Henüz bir ilanınız bulunmuyor.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="ilanEkleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold">Yeni Araç İlanı Oluştur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="islem.php?durum=ilan_ekle" method="POST" enctype="multipart/form-data" id="ilanForm">
                <input type="hidden" name="ilan_tipi" value="Araba">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="small text-white-50">İlan Başlığı</label>
                            <input type="text" name="baslik" class="form-control" placeholder="Örn: Hatasız Boyasız BMW" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-white-50">İletişim Numarası</label>
                            <input type="tel" name="telefon" class="form-control" placeholder="05XX XXX XX XX" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="small text-white-50">Marka</label>
                            <select name="marka_id" id="marka_select" class="form-select" required onchange="modelleriGetir(this.value)">
                                <option value="">Marka Seçin</option>
                                <?php foreach($markalar as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo $m['marka_adi']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="small text-white-50">Model</label>
                            <select name="model_id" id="model_select" class="form-select" required disabled>
                                <option value="">Önce Marka Seçin</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="small text-white-50">Yakıt Tipi</label>
                            <select name="yakit_tipi" class="form-select" required>
                                <option value="Benzin">Benzin</option>
                                <option value="Dizel">Dizel</option>
                                <option value="Hibrit">Hibrit</option>
                                <option value="Elektrik">Elektrik</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="small text-white-50">Vites Tipi</label>
                            <select name="vites_tipi" class="form-select" required>
                                <option value="Otomatik">Otomatik</option>
                                <option value="Manuel">Manuel</option>
                                <option value="Yarı Otomatik">Yarı Otomatik</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="small text-white-50">Kilometre (KM)</label>
                            <input type="number" name="kilometre" class="form-control" placeholder="Örn: 120000" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small text-white-50">Model Yılı</label>
                            <input type="number" name="model_yili" class="form-control" placeholder="Örn: 2023" min="1950" max="2027" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small text-white-50">Fiyat (TL)</label>
                            <input type="number" name="fiyat" class="form-control" placeholder="Örn: 4500000" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small text-white-50">Şehir</label>
                            <select name="sehir_id" class="form-select" required>
                                <option value="">Şehir Seçin</option>
                                <?php foreach($sehirler as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo $s['sehir_adi']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-white-50">Araç Detaylı Açıklaması</label>
                        <textarea name="aciklama" class="form-control" rows="4" placeholder="Aracın durumu, hasar kaydı, ekstraları gibi detayları buraya yazınız..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="small text-white-50 d-flex justify-content-between">Araç Fotoğrafları (Maksimum 10 adet) <span id="file-count" class="text-primary">0/10</span></label>
                        <input type="file" name="ilan_resimleri[]" id="ilan_resimleri" class="form-control" accept="image/*" multiple required>
                        <div id="image-preview-container"></div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary px-4">İlanı Yayınla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="evIlanEkleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold">Yeni Premium Ev İlanı Oluştur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="islem.php?durum=ilan_ekle" method="POST" enctype="multipart/form-data" id="evIlanForm">
                <input type="hidden" name="ilan_tipi" value="Emlak">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="small text-white-50">İlan Başlığı</label>
                            <input type="text" name="baslik" class="form-control" placeholder="Örn: Boğaz Manzaralı Havuzlu Villa" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-white-50">İletişim Numarası</label>
                            <input type="tel" name="telefon" class="form-control" placeholder="05XX XXX XX XX" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Pazarlama Tipi</label>
                            <select name="pazarlama_tipi" class="form-select" required>
                                <option value="Satılık">Satılık</option>
                                <option value="Kiralık">Kiralık</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Oda Sayısı</label>
                            <select name="vites_tipi" class="form-select" required>
                                <option value="1+1">1+1</option>
                                <option value="2+1">2+1</option>
                                <option value="3+1">3+1</option>
                                <option value="4+1">4+1</option>
                                <option value="5+2 ve üzeri">5+2 ve üzeri</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small text-white-50">Brüt Metrekare (m²)</label>
                            <input type="number" name="kilometre" class="form-control" placeholder="Örn: 150" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-white-50">Fiyat (TL)</label>
                            <input type="number" name="fiyat" class="form-control" placeholder="Örn: 12500000" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-white-50">Şehir</label>
                            <select name="sehir_id" class="form-select" required>
                                <option value="">Şehir Seçin</option>
                                <?php foreach($sehirler as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo $s['sehir_adi']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-white-50">Mülk Detaylı Açıklaması</label>
                        <textarea name="aciklama" class="form-control" rows="4" placeholder="Mülkün konumu, sosyal imkanları, cephesi gibi detayları buraya yazınız..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="small text-white-50 d-flex justify-content-between">Mülk Fotoğrafları (Maksimum 10 adet) <span id="ev-file-count" class="text-primary">0/10</span></label>
                        <input type="file" name="ilan_resimleri[]" id="ev_ilan_resimleri" class="form-control" accept="image/*" multiple required>
                        <div id="ev-image-preview-container"></div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-primary px-4">İlanı Yayınla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="duzenleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold">İlanı Düzenle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="islem.php?durum=ilan_guncelle" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="small text-white-50">İlan Başlığı</label>
                            <input type="text" name="baslik" id="edit_baslik" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-white-50">Fiyat (TL)</label>
                            <input type="number" name="fiyat" id="edit_fiyat" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Metrekare / Kilometre</label>
                            <input type="number" name="kilometre" id="edit_kilometre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Model Yılı (Sadece Araçlar İçin)</label>
                            <input type="number" name="model_yili" id="edit_model_yili" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-white-50">Açıklama</label>
                        <textarea name="aciklama" id="edit_aciklama" class="form-control" rows="4" required></textarea>
                    </div>
                    <p class="text-info small"><i class="fas fa-info-circle me-1"></i> Kategoriye bağlı detay ve fotoğraf değişikliği için yeni ilan vermeniz önerilir.</p>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="btn btn-warning px-4">Değişiklikleri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// DÜZENLEME FONKSİYONU
function ilanDuzenle(ilan) {
    document.getElementById('edit_id').value = ilan.id;
    document.getElementById('edit_baslik').value = ilan.baslik;
    document.getElementById('edit_fiyat').value = ilan.fiyat;
    document.getElementById('edit_kilometre').value = ilan.kilometre;
    document.getElementById('edit_model_yili').value = ilan.model_yili ? ilan.model_yili : '';
    document.getElementById('edit_aciklama').value = ilan.aciklama;
    
    var myModal = new bootstrap.Modal(document.getElementById('duzenleModal'));
    myModal.show();
}

function modelleriGetir(markaId) {
    const modelSelect = document.getElementById('model_select');
    modelSelect.innerHTML = '<option value="">Yükleniyor...</option>';
    modelSelect.disabled = true;

    if (!markaId) {
        modelSelect.innerHTML = '<option value="">Önce Marka Seçin</option>';
        return;
    }

    fetch('islem.php?durum=modelleri_getir&marka_id=' + markaId)
        .then(response => {
            if (!response.ok) throw new Error('Ağ hatası');
            return response.json();
        })
        .then(data => {
            modelSelect.innerHTML = '<option value="">Model Seçin</option>';
            if (data && data.length > 0) {
                data.forEach(model => {
                    modelSelect.innerHTML += `<option value="${model.id}">${model.model_adi}</option>`;
                });
                modelSelect.disabled = false;
            } else {
                modelSelect.innerHTML = '<option value="">Bu markaya ait model bulunamadı</option>';
            }
        })
        .catch(error => {
            console.error('Hata:', error);
            modelSelect.innerHTML = '<option value="">Hata oluştu!</option>';
        });
}

// Araç Resim Önizleme
document.getElementById('ilan_resimleri').addEventListener('change', function(e) {
    const container = document.getElementById('image-preview-container');
    const fileCount = document.getElementById('file-count');
    const files = e.target.files;
    container.innerHTML = '';
    if (files.length > 10) {
        alert("En fazla 10 fotoğraf seçebilirsiniz!");
        this.value = "";
        fileCount.innerText = "0/10";
        return;
    }
    fileCount.innerText = files.length + "/10";
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        reader.onload = function(event) {
            const wrapper = document.createElement('div');
            wrapper.className = 'preview-wrapper';
            const img = document.createElement('img');
            img.src = event.target.result;
            wrapper.appendChild(img);
            container.appendChild(wrapper);
        }
        reader.readAsDataURL(file);
    }
});

// Ev Resim Önizleme
document.getElementById('ev_ilan_resimleri').addEventListener('change', function(e) {
    const container = document.getElementById('ev-image-preview-container');
    const fileCount = document.getElementById('ev-file-count');
    const files = e.target.files;
    container.innerHTML = '';
    if (files.length > 10) {
        alert("En fazla 10 fotoğraf seçebilirsiniz!");
        this.value = "";
        fileCount.innerText = "0/10";
        return;
    }
    fileCount.innerText = files.length + "/10";
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        reader.onload = function(event) {
            const wrapper = document.createElement('div');
            wrapper.className = 'preview-wrapper';
            const img = document.createElement('img');
            img.src = event.target.result;
            wrapper.appendChild(img);
            container.appendChild(wrapper);
        }
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>