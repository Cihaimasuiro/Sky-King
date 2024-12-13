<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get form data
        $id_customer = $_SESSION['user_id'];
        $id_penerbangan = $_POST['flight_id'];
        $tanggal_pesan = date('Y-m-d');
        $jumlah_pembayaran = $_POST['total_amount'];
        $jenis_pembayaran = $_POST['payment_method'];
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Create booking
        $stmt = $pdo->prepare("
            INSERT INTO Pemesanan (tanggal_pesan, status, id_customer, id_penerbangan)
            VALUES (?, 'Pending', ?, ?)
        ");
        $stmt->execute([$tanggal_pesan, $id_customer, $id_penerbangan]);
        $id_pemesanan = $pdo->lastInsertId();
        
        // Create payment record
        $stmt = $pdo->prepare("
            INSERT INTO Pembayaran (id_pemesanan, jumlah, tanggal_pembayaran, jenis_pembayaran)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$id_pemesanan, $jumlah_pembayaran, $tanggal_pesan, $jenis_pembayaran]);
        
        // Commit transaction
        $pdo->commit();
        
        // Store booking ID in session for payment page
        $_SESSION['booking_id'] = $id_pemesanan;
        
        // Redirect to payment page
        header("Location: payment.php");
        exit();
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error dalam pemesanan: " . $e->getMessage();
        header("Location: booking.php");
        exit();
    }
}
?>
