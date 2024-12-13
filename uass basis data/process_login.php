<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    try {
        $stmt = $pdo->prepare("SELECT * FROM Customer WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && hash('sha256', $password) === $user['password']) {
            $_SESSION['user_id'] = $user['id_customer'];
            $_SESSION['user_name'] = $user['nama'];
            
            if ($remember) {
                // Set cookie for 30 days
                setcookie('user_email', $email, time() + (86400 * 30), "/");
            }

            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Email atau password salah";
            header("Location: login.php");
            exit();
        }

    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
}
?>
