<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    try {
        // Mengubah query untuk menggunakan tabel users yang baru
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session untuk user yang login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_logged_in'] = true;
            
            if ($remember) {
                // Set cookie yang aman untuk "remember me"
                setcookie('user_email', $email, time() + (86400 * 30), "/", "", true, true);
            }

            $_SESSION['success_message'] = "Selamat datang, " . $user['nama'] . "!";
            header("Location: dashboard.php"); // Mengubah redirect ke dashboard
            exit();
        } else {
            $_SESSION['error_message'] = "Email/Username atau password salah";
            header("Location: login.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
