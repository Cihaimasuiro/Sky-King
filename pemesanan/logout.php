<?php
session_start();

// Hapus semua data session
session_destroy();

// Hapus cookie remember me jika ada
if (isset($_COOKIE['user_email'])) {
    setcookie('user_email', '', time() - 3600, '/');
}

// Redirect ke halaman login
header("Location: login.php");
exit();
?>
