<?php
session_start();
session_destroy(); // Oturumu kapat

// Beni hatırla çerezini temizle (Süresini geçmişe alarak siler)
if (isset($_cookie['remember_user'])) {
    setcookie("remember_user", "", time() - 3600, "/");
}

header("Location: index.php");
exit;
?>