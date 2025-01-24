<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validasi form
        if (empty($_POST['booking_id'])) {
            $_SESSION['error_message'] = "Data tidak valid. Silakan coba lagi.";
            header("Location: payment.php");
            exit();
        }

        $booking_id = $_POST['booking_id'];

        // Mulai transaksi
        $pdo->beginTransaction();

        // Update status booking
        $stmt = $pdo->prepare("UPDATE booking SET status = 'CONFIRMED', waktu_pembayaran = NOW() WHERE id_booking = ? AND id_users = ?");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);

        if ($stmt->rowCount() === 0) {
            throw new PDOException("Booking tidak ditemukan atau Anda tidak memiliki akses.");
        }

        // Insert ke tabel pembayaran
        $stmt = $pdo->prepare("INSERT INTO payment (id_booking, payment_time, status) VALUES (?, NOW(), 'CONFIRMED')");
        $stmt->execute([$booking_id]);

        // Commit transaksi
        $pdo->commit();

        // Simpan booking_id di session
        $_SESSION['booking_id'] = $booking_id;

        // Redirect ke halaman e-tiket
        $_SESSION['success_message'] = "Pembayaran berhasil! Tiket Anda telah siap.";
        header("Location: booking_confirmation.php");
        exit();
    } catch (PDOException $e) {
        // Rollback transaksi jika terjadi error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "Error dalam proses pembayaran: " . $e->getMessage();
        header("Location: payment.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
