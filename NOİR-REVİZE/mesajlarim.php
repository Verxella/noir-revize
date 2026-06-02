<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: giris.php"); exit; }

$host = "localhost"; $user = "root"; $pass = ""; $db_name = "noir_db";
try {
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $pass);
    $user_id = $_SESSION['user_id'];

    // NAVBAR İÇİN PROFİL ÇEK
    $u_sorgu = $db->prepare("SELECT profil_resmi, ad_soyad FROM users WHERE id = ?");
    $u_sorgu->execute([$user_id]);
    $kullanici = $u_sorgu->fetch(PDO::FETCH_ASSOC);

    // KONUŞMA LİSTESİNİ ÇEK (En son mesaj atanlar üstte)
    $konusmalar = $db->prepare("
        SELECT DISTINCT 
            u.id as kisi_id, u.ad_soyad, u.profil_resmi,
            (SELECT mesaj_icerik FROM mesajlar 
             WHERE (gonderen_id = ? AND alici_id = u.id) OR (gonderen_id = u.id AND alici_id = ?) 
             ORDER BY tarih DESC LIMIT 1) as son_mesaj,
            (SELECT tarih FROM mesajlar 
             WHERE (gonderen_id = ? AND alici_id = u.id) OR (gonderen_id = u.id AND alici_id = ?) 
             ORDER BY tarih DESC LIMIT 1) as son_tarih
        FROM users u
        INNER JOIN mesajlar m ON (m.gonderen_id = u.id OR m.alici_id = u.id)
        WHERE (m.gonderen_id = ? OR m.alici_id = ?) AND u.id != ?
        ORDER BY son_tarih DESC
    ");
    $konusmalar->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    $liste = $konusmalar->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) { die("Hata: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesajlarım | NOIR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            background-color: #0a0a0a; 
            color: #fff; 
            font-family: 'Segoe UI', sans-serif; 
            height: 100vh; 
            overflow: hidden; 
            /* Noir Arka Plan Deseni */
            background-image: radial-gradient(circle at 50% -20%, #1a1a1a, #0a0a0a);
        }
        .navbar { 
            background: rgba(0, 0, 0, 0.8); 
            backdrop-filter: blur(15px); 
            border-bottom: 1px solid rgba(255,255,255,0.05); 
            height: 70px; 
        }
        
        .messaging-wrapper { 
            height: calc(100vh - 70px); 
            display: flex; 
            padding: 20px;
            gap: 20px;
        }
        
        /* Sol Liste - Şeffaf Siyah */
        .contacts-side { 
            width: 380px; 
            background: rgba(255, 255, 255, 0.03); 
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05); 
            border-radius: 20px;
            overflow-y: auto; 
        }
        .contact-card { 
            padding: 15px 20px; 
            cursor: pointer; 
            transition: 0.3s; 
            border-bottom: 1px solid rgba(255,255,255,0.03); 
            display: flex; 
            align-items: center; 
        }
        .contact-card:hover { background: rgba(255, 255, 255, 0.05); }
        .contact-card.active { 
            background: rgba(13, 110, 253, 0.1); 
            border-left: 4px solid #0d6efd; 
        }
        .contact-img { 
            width: 50px; 
            height: 50px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 2px solid rgba(13, 110, 253, 0.3); 
        }
        
        /* Sağ Sohbet Alanı - Şeffaf Siyah */
        .chat-side { 
            flex-grow: 1; 
            display: flex; 
            flex-direction: column; 
            background: rgba(255, 255, 255, 0.02); 
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            overflow: hidden;
        }
        .chat-header { 
            padding: 15px 25px; 
            background: rgba(255, 255, 255, 0.03); 
            border-bottom: 1px solid rgba(255,255,255,0.05); 
            display: flex; 
            align-items: center; 
        }
        .chat-messages { 
            flex-grow: 1; 
            padding: 30px; 
            overflow-y: auto; 
            display: flex; 
            flex-direction: column; 
            gap: 12px; 
        }
        
        /* Balonlar */
        .bubble { 
            max-width: 70%; 
            padding: 12px 18px; 
            border-radius: 18px; 
            font-size: 0.95rem; 
            line-height: 1.5;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .bubble-in { 
            align-self: flex-start; 
            background: rgba(255, 255, 255, 0.08); 
            color: #fff; 
            border-bottom-left-radius: 4px; 
            border: 1px solid rgba(255,255,255,0.05);
        }
        .bubble-out { 
            align-self: flex-end; 
            background: #0d6efd; 
            color: #fff; 
            border-bottom-right-radius: 4px; 
        }
        .msg-time { 
            font-size: 0.7rem; 
            opacity: 0.5; 
            margin-top: 5px; 
            display: block; 
            text-align: right; 
        }

        /* Input Alanı */
        .chat-footer { 
            padding: 20px; 
            background: rgba(0, 0, 0, 0.2); 
            border-top: 1px solid rgba(255,255,255,0.05); 
        }
        .input-group-noir { 
            background: rgba(255, 255, 255, 0.05); 
            border-radius: 15px; 
            padding: 8px 15px; 
            display: flex;
            align-items: center;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .chat-input { 
            background: transparent !important; 
            border: none !important; 
            color: #fff !important; 
            box-shadow: none !important; 
            flex-grow: 1;
        }
        .btn-send { 
            color: #0d6efd; 
            font-size: 1.3rem; 
            background: none;
            border: none;
            transition: 0.3s; 
        }
        .btn-send:hover { transform: scale(1.1); color: #fff; }

        .user-profile-circle { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #0d6efd; display: flex; align-items: center; justify-content: center; }
        
        /* Özel Kaydırma Çubuğu */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark px-4">
    <a class="navbar-brand fw-bold text-primary" href="index2.php" style="letter-spacing: 3px;">NOIR</a>
    <div class="ms-auto d-flex align-items-center">
        <a href="index2.php" class="btn btn-sm btn-outline-secondary me-3" style="border-radius: 10px;">Ana Sayfa</a>
        <div class="user-profile-circle shadow">
            <?php echo !empty($kullanici['profil_resmi']) ? '<img src="img/profil/'.$kullanici['profil_resmi'].'" style="width:100%;height:100%;object-fit:cover;">' : strtoupper(substr($kullanici['ad_soyad'],0,1)); ?>
        </div>
    </div>
</nav>

<div class="messaging-wrapper">
    <!-- SOL: KİŞİ LİSTESİ -->
    <div class="contacts-side shadow">
        <div class="p-3 border-bottom border-secondary text-white-50 small fw-bold" style="letter-spacing: 1px;">SOHBETLER</div>
        <?php if($liste): foreach($liste as $item): ?>
            <div class="contact-card" onclick="loadChat(<?php echo $item['kisi_id']; ?>, this)">
                <img src="img/profil/<?php echo !empty($item['profil_resmi']) ? $item['profil_resmi'] : 'default-avatar.png'; ?>" class="contact-img me-3 shadow">
                <div class="w-100 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><?php echo $item['ad_soyad']; ?></span>
                        <span class="small text-white-50" style="font-size: 0.7rem;"><?php echo date('H:i', strtotime($item['son_tarih'])); ?></span>
                    </div>
                    <div class="small text-white-50 text-truncate"><?php echo $item['son_mesaj']; ?></div>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="p-5 text-center text-white-50 small">Henüz bir mesajlaşma bulunmuyor.</div>
        <?php endif; ?>
    </div>

    <!-- SAĞ: SOHBET EKRANI -->
    <div class="chat-side shadow" id="main-chat-container">
        <div class="h-100 d-flex flex-column align-items-center justify-content-center text-white-50">
            <div class="mb-4" style="font-size: 4rem; opacity: 0.2;"><i class="fas fa-comments"></i></div>
            <h5 class="fw-bold text-white" style="letter-spacing: 1px;">Noir Güvenli İletişim</h5>
            <p class="small">Lütfen mesajlaşmak için soldan bir kullanıcı seçin.</p>
        </div>
    </div>
</div>

<script>
function loadChat(kisiId, element) {
    document.querySelectorAll('.contact-card').forEach(c => c.classList.remove('active'));
    element.classList.add('active');

    const container = document.getElementById('main-chat-container');
    container.innerHTML = '<div class="h-100 d-flex align-items-center justify-content-center"><div class="spinner-border text-primary" role="status"></div></div>';

    fetch(`islem.php?durum=mesaj_cek&kisi_id=${kisiId}`)
        .then(res => res.text())
        .then(data => {
            container.innerHTML = data;
            scrollBottom();
        });
}

function scrollBottom() {
    const chatBox = document.querySelector('.chat-messages');
    if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;
}

function sendMessage(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    fetch('islem.php?durum=mesaj_gonder_ajax', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            const kisiId = form.querySelector('input[name="alici_id"]').value;
            // Mevcut aktif kartı bul ve tekrar yükle
            const activeCard = document.querySelector('.contact-card.active');
            loadChat(kisiId, activeCard);
        }
    });
}
</script>

</body>
</html>