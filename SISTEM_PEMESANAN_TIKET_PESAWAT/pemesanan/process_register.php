<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $telepon = trim($_POST['telepon']);

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

    if (empty($telepon)) {
        $errors[] = "Nomor telepon tidak boleh kosong";
    }

    // Cek apakah email atau username sudah terdaftar
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
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
            // Hash password sebelum disimpan ke database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Simpan data ke database
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, username, password, telepon, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$nama, $email, $username, $hashed_password, $telepon]);

            // Set session untuk user yang baru mendaftar
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $nama;
            $_SESSION['user_email'] = $email;
            $_SESSION['username'] = $username;
            $_SESSION['is_logged_in'] = true;
            
            // Set pesan sukses
            $_SESSION['success_message'] = "Pendaftaran berhasil! Selamat datang, $nama!";
            
            // Redirect ke beranda
            header("Location: index.php");
            exit();
            
        } catch(PDOException $e) {
            $errors[] = "Error saat mendaftar: " . $e->getMessage();
            $_SESSION['error_messages'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header("Location: register.php");
            exit();
        }
    } else {
        $_SESSION['error_messages'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header("Location: register.php");
        exit();
    }
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
}

form {
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="date"],
select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    background-color: #5cb85c;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #4cae4c;
}
</style>

<form>
    <label for='nama'>Nama:</label>
    <input type='text' name='nama' id='nama'>

    <label for='email'>Email:</label>
    <input type='email' name='email' id='email'>

    <label for='username'>Username:</label>
    <input type='text' name='username' id='username'>

    <label for='password'>Password:</label>
    <input type='password' name='password' id='password'>

    <label for='confirm_password'>Konfirmasi Password:</label>
    <input type='password' name='confirm_password' id='confirm_password'>

    <label for='telepon'>Nomor Telepon:</label>
    <input type='text' name='telepon' id='telepon'>

    <button type='submit'>Daftar</button>
</form>
