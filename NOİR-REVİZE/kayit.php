<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol | NOIR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            padding: 20px;
            margin: 0;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            padding: 45px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.6);
        }
        .form-control {
            background: rgba(255, 255, 255, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        /* --- AUTOFILL (OTOMATİK DOLDURMA) BEYAZLIK ENGELLEME --- */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-background-clip: text;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
            box-shadow: inset 0 0 20px 20px rgba(255, 255, 255, 0.01) !important;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15) !important;
            border-color: #0d6efd !important;
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.3);
            outline: none;
        }
        .form-control::placeholder { 
            color: rgba(255, 255, 255, 0.75) !important; 
            font-size: 0.9rem;
        }
        
        #error-message {
            display: none;
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #ff858d !important;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .password-container { position: relative; }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.7);
            transition: color 0.3s;
            z-index: 10;
        }
        .toggle-password:hover { color: #0d6efd; }

        .btn-primary { 
            background-color: #0d6efd; 
            border: none; 
            padding: 14px; 
            border-radius: 12px; 
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover { 
            background-color: #0b5ed7; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
        }
        .navbar-brand { 
            color: #0d6efd !important; 
            letter-spacing: 5px; 
            font-weight: 800; 
            text-decoration: none; 
            display: block; 
            text-align: center; 
            margin-bottom: 25px;
            font-size: 2rem;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="register-card">
    <a href="index.php" class="navbar-brand">NOIR</a>
    <h3 class="text-center mb-4" style="font-weight: 700;">Yeni Hesap Oluştur</h3>
    
    <?php if (isset($_GET['hata']) && $_GET['hata'] == "mukerrer_telefon"): ?>
        <div class="alert alert-danger" style="background: rgba(220,53,69,0.1); border: 1px solid #dc3545; color: #dc3545; border-radius: 12px;">
            <i class="fas fa-exclamation-circle me-2"></i> Bu telefon numarası zaten sistemde kayıtlı!
        </div>
    <?php endif; ?>

    <div id="error-message"></div>
    
    <form id="registerForm" action="islem.php?durum=kayit" method="POST">
        <div class="mb-3">
            <label class="form-label small">Ad Soyad</label>
            <input type="text" name="ad_soyad" class="form-control" placeholder="Örn: Ahmet Yılmaz" required>
        </div>

        <div class="mb-3">
            <label class="form-label small">E-Posta</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="ahmet@ornekmail.com" required>
        </div>

        <div class="mb-3">
            <label class="form-label small">Telefon Numarası</label>
            <input type="tel" id="phone" name="telefon" class="form-control" placeholder="05XXXXXXXXX" required maxlength="11">
        </div>

        <div class="mb-4">
            <label class="form-label small">Şifre</label>
            <div class="password-container">
                <input type="password" name="sifre" id="sifre" class="form-control" placeholder="••••••••" required>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-4">Kayıt İşlemini Tamamla</button>
        
        <p class="text-center small mb-0">
            Zaten bir hesabınız var mı? <a href="giris.php" class="text-primary text-decoration-none fw-bold">Giriş Yap</a>
        </p>
    </form>
</div>

<script>
    // Şifre Gizle/Göster
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#sifre');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });

    // Form Doğrulama Kontrolü
    const form = document.querySelector('#registerForm');
    const errorDiv = document.querySelector('#error-message');

    form.addEventListener('submit', function (e) {
        let messages = [];
        const email = document.querySelector('#email').value;
        const phone = document.querySelector('#phone').value;

        // E-posta kontrolü
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            messages.push("Lütfen geçerli bir e-posta adresi giriniz.");
        }

        // Telefon kontrolü
        const phoneRegex = /^[0-9]{11}$/;
        if (!phoneRegex.test(phone)) {
            messages.push("Telefon numarası 11 haneli olmalı ve 0 ile başlamalıdır.");
        }

        if (messages.length > 0) {
            e.preventDefault(); 
            errorDiv.innerText = messages.join('\n');
            errorDiv.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
</script>

</body>
</html>