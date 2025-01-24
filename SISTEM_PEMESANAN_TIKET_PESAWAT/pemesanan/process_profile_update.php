<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $telepon = trim($_POST['telepon']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $current_password = $_POST['current_password'];

    // Validasi input
    $errors = [];

    if (empty($nama)) {
        $errors[] = "Nama tidak boleh kosong";
    }

    if (empty($email)) {
        $errors[] = "Email tidak boleh kosong";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    if (empty($username)) {
        $errors[] = "Username tidak boleh kosong";
    }

    if (empty($telepon)) {
        $errors[] = "Nomor telepon tidak boleh kosong";
    }

    if (empty($current_password)) {
        $errors[] = "Password saat ini diperlukan untuk menyimpan perubahan";
    }

    try {
        // Verifikasi password saat ini
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current_password, $user['password'])) {
            $errors[] = "Password saat ini tidak valid";
        } else {
            // Cek apakah email atau username sudah digunakan oleh user lain
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (email = ? OR username = ?) AND id != ?");
            $stmt->execute([$email, $username, $user_id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $errors[] = "Email atau username sudah digunakan";
            }

            // Jika tidak ada error, proses update
            if (empty($errors)) {
                if (!empty($new_password)) {
                    if (strlen($new_password) < 6) {
                        $errors[] = "Password baru harus minimal 6 karakter";
                    } elseif ($new_password !== $confirm_password) {
                        $errors[] = "Password baru dan konfirmasi password tidak cocok";
                    } else {
                        // Update dengan password baru
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, username = ?, password = ?, telepon = ? WHERE id = ?");
                        $stmt->execute([$nama, $email, $username, $hashed_password, $telepon, $user_id]);
                    }
                } else {
                    // Update tanpa mengubah password
                    $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, username = ?, telepon = ? WHERE id = ?");
                    $stmt->execute([$nama, $email, $username, $telepon, $user_id]);
                }

                if (empty($errors)) {
                    // Update session data
                    $_SESSION['user_name'] = $nama;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['username'] = $username;

                    $_SESSION['success_message'] = "Profil berhasil diperbarui";
                    header("Location: profile.php");
                    exit();
                }
            }
        }
    } catch(PDOException $e) {
        $errors[] = "Error: " . $e->getMessage();
    }

    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: profile.php");
        exit();
    }
}

// Jika bukan POST request, redirect ke halaman profile
header("Location: profile.php");
exit();
?>
