<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap | NOIR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            /* Kayıt sayfasıyla uyumlu modern araç/garaj arka planı */
            background: linear-gradient(rgba(0,0,0,0.75), rgba(0,0,0,0.75)), url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            margin: 0;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            padding: 45px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.6);
        }

        /* Hata Mesajı Stili */
        .alert-noir {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #ff858d;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white !important;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #0d6efd;
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.3);
            outline: none;
        }

        /* Otomatik doldurma (autofill) sonrası beyaz arka planı engelleyen kod */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: white !important;
            -webkit-box-shadow: 0 0 0px 1000px rgba(30, 30, 30, 1) inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .form-control::placeholder { color: rgba(255,255,255,0.6); }
        
        /* Şifre Göster Butonu Stili */
        .password-container { position: relative; }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.7);
            z-index: 10;
        }
        .toggle-password:hover { color: #0d6efd; }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover { 
            background-color: #0b5ed7; 
            transform: translateY(-2px);
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
        .form-check-input { background-color: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); }
        .form-check-input:checked { background-color: #0d6efd; border-color: #0d6efd; }
    </style>
</head>
<body>

<div class="login-card">
    <a href="index.php" class="navbar-brand">NOIR</a>
    <h3 class="text-center mb-4" style="font-weight: 700;">Hoş Geldiniz</h3>

    <!-- HATA MESAJI BLOĞU -->
    <?php if(isset($_GET['hata']) && $_GET['hata'] == "hatali_giris"): ?>
        <div class="alert-noir">
            <i class="fas fa-exclamation-circle me-2"></i> 
            E-posta veya şifre hatalı. Lütfen bilgilerinizi kontrol edin.
        </div>
    <?php endif; ?>
    
    <form action="islem.php?durum=giris" method="POST">
        <div class="mb-3">
            <label class="form-label small">E-Posta Adresi</label>
            <input type="email" name="email" class="form-control" placeholder="mail@ornek.com" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label small">Şifre</label>
            <div class="password-container">
                <input type="password" name="sifre" id="sifre" class="form-control" placeholder="••••••••" required>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
        </div>
        
        <div class="mb-4 form-check">
            <input type="checkbox" name="beni_hatirla" class="form-check-input" id="remember">
            <label class="form-check-label small" for="remember">Beni Hatırla</label>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 mb-4">Giriş Yap</button>
        
        <p class="text-center small mb-0">
            Hesabınız yok mu? <a href="kayit.php" class="text-primary text-decoration-none fw-bold">Kayıt Ol</a>
        </p>
    </form>
</div>

<script>
    // Şifre Gizle/Göster Fonksiyonu
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#sifre');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>