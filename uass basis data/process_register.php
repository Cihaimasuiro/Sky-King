<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $no_pasport = trim($_POST['no_pasport']);

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

    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Password tidak cocok";
    }

    // Cek apakah email atau username sudah terdaftar
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Customer WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $errors[] = "Email atau username sudah terdaftar";
        }
    } catch(PDOException $e) {
        $errors[] = "Error: " . $e->getMessage();
    }

    // Jika tidak ada error, proses pendaftaran
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO Customer (nama, email, username, password, no_pasport) VALUES (?, ?, ?, ?, ?)");
            $hashed_password = hash('sha256', $password);
            $stmt->execute([$nama, $email, $username, $hashed_password, $no_pasport]);

            $_SESSION['success_message'] = "Pendaftaran berhasil! Silakan login.";
            header("Location: login.php");
            exit();
        } catch(PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['error_messages'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header("Location: register.php");
        exit();
    }
}
?>
