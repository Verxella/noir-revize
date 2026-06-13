<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit;
}

// Veritabanı Bağlantısı
$host = "localhost";
$user = "root";
$pass = "";
$db   = "noir_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    // Kullanıcı bilgilerini çek
    $sorgu = $conn->prepare("SELECT email, created_at, profil_resmi FROM users WHERE id = ?");
    $sorgu->execute([$_SESSION['user_id']]);
    $u = $sorgu->fetch(PDO::FETCH_ASSOC);

    // --- GÜNCELLEME: İLANLARA GELEN TOPLAM BEĞENİ SAYISINI ÇEK ---
    try {
        // Kullanıcının eklediği ilanlar ile o ilanlara gelen favorileri JOIN yaparak sayıyoruz
        // Bu sorgu her sayfa yenilendiğinde en güncel rakamı getirir.
        $fav_sorgu = $conn->prepare("
            SELECT COUNT(f.id) as toplam_fav 
            FROM favoriler f 
            INNER JOIN ilanlar i ON f.ilan_id = i.id 
            WHERE i.ekleyen_id = ?
        ");
        $fav_sorgu->execute([$_SESSION['user_id']]);
        $f = $fav_sorgu->fetch(PDO::FETCH_ASSOC);
        $toplam_favori = $f['toplam_fav'] ?? 0;
    } catch (Exception $e) {
        $toplam_favori = 0;
    }

} catch (PDOException $e) {
    die("Hata: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilimi Düzenle | NOIR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.75), rgba(0,0,0,0.75)), url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            color: white;
            padding: 40px 20px;
        }
        .profile-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            padding: 40px;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.6);
        }
        
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-text-fill-color: white !important;
            -webkit-box-shadow: 0 0 0px 1000px #1e1e1e inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white !important;
            border-radius: 12px;
            padding: 12px;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15) !important;
            border-color: #0d6efd;
            box-shadow: none;
        }
        .form-control:disabled {
            background: rgba(0, 0, 0, 0.3) !important;
            border-color: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.4) !important;
        }

        .profile-img-section {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }
        .profile-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0d6efd;
            background-color: #1e1e1e;
        }
        .file-input-label {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #0d6efd;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.3s;
        }
        .file-input-label:hover { transform: scale(1.1); }

        .stats-badge {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 10px;
            text-align: center;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .password-container { position: relative; }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.7);
        }
        .toggle-password:hover { color: #0d6efd; }

        .btn-primary { background-color: #0d6efd; border: none; padding: 12px; border-radius: 12px; font-weight: 600; }
        .btn-danger-outline {
            background: transparent;
            border: 1px solid #dc3545;
            color: #dc3545;
            padding: 10px;
            border-radius: 12px;
            font-size: 0.9rem;
            width: 100%;
            margin-top: 20px;
        }
        .btn-danger-outline:hover { background: #dc3545; color: white; }
        .navbar-brand { color: #0d6efd !important; letter-spacing: 5px; font-weight: 800; text-decoration: none; display: block; text-align: center; margin-bottom: 20px; font-size: 1.8rem; }
    </style>
</head>
<body>

<div class="profile-card">
    <a href="index2.php" class="navbar-brand">NOIR</a>
    
    <div class="row mb-4">
        <div class="col-6">
            <div class="stats-badge">
                <div class="text-white-50 small">Kayıt Tarihi</div>
                <div class="fw-bold"><?php echo date("d.m.Y", strtotime($u['created_at'])); ?></div>
            </div>
        </div>
        <div class="col-6">
            <div class="stats-badge">
                <div class="text-white-50 small">Toplam Beğeni</div>
                <div class="fw-bold"><i class="fas fa-heart text-danger me-1"></i><?php echo $toplam_favori; ?></div>
            </div>
        </div>
    </div>

    <form action="islem.php?durum=profil_guncelle" method="POST" enctype="multipart/form-data">
        
        <div class="profile-img-section">
            <?php 
                $profil_src = (!empty($u['profil_resmi'])) ? 'img/profil/'.$u['profil_resmi'] : 'https://ui-avatars.com/api/?name='.urlencode($_SESSION['user_name']).'&background=0d6efd&color=fff&size=128';
            ?>
            <img src="<?php echo $profil_src; ?>" class="profile-preview" id="preview">
            
            <label for="foto-input" class="file-input-label">
                <i class="fas fa-camera"></i>
            </label>
            <input type="file" name="profil_foto" id="foto-input" style="display:none;" accept="image/*" onchange="previewImage(this)">
        </div>

        <h4 class="text-center mb-4">Profil Bilgilerini Güncelle</h4>

        <?php if(isset($_GET['islem']) && $_GET['islem'] == "premium_yukselt"): ?>
            <div class="alert alert-info border-0 shadow-sm mb-4" style="background: rgba(13, 110, 253, 0.2); color: #fff; border-radius: 12px; font-size: 14px;">
                <i class="fas fa-info-circle me-2 text-primary"></i> 
                İlan limitiniz dolmuştur. Premium ayrıcalıkları için destek ekibimizle iletişime geçin.
            </div>
        <?php endif; ?>
        
        <div class="mb-3">
            <label class="form-label small">Ad Soyad</label>
            <input type="text" name="ad_soyad" class="form-control" value="<?php echo $_SESSION['user_name']; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label small">E-Posta (Değiştirilemez)</label>
            <input type="email" class="form-control" value="<?php echo $u['email']; ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label small">Yeni Şifre (Boş bırakırsanız değişmez)</label>
            <div class="password-container">
                <input type="password" name="yeni_sifre" id="yeni_sifre" class="form-control" placeholder="••••••••">
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">Değişiklikleri Kaydet</button>
    </form>

    <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">

    <button type="button" class="btn btn-danger-outline" onclick="confirmDelete()">
        <i class="fas fa-trash-alt me-2"></i> Hesabı Kalıcı Olarak Sil
    </button>

    <div class="text-center mt-3">
        <a href="index2.php" class="text-white-50 small text-decoration-none"><i class="fas fa-arrow-left me-2"></i>Vazgeç ve Dön</a>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#yeni_sifre');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function confirmDelete() {
        if (confirm("Hesabınızı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.")) {
            window.location.href = "islem.php?durum=hesap_sil";
        }
    }
</script>
</body>
</html>