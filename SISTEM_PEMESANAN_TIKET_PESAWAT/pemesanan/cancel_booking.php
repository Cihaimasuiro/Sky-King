<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking_id is provided
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['booking_id']) || !is_numeric($_POST['booking_id'])) {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_POST['booking_id'];

try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // Check if booking exists and belongs to user
    $stmt = $pdo->prepare("
        SELECT * FROM booking 
        WHERE id_booking = ? 
        AND id_customer = ? 
        AND status = 'PENDING'
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        throw new Exception("Pesanan tidak ditemukan atau tidak dapat dibatalkan.");
    }
    
    // Update booking status
    $stmt = $pdo->prepare("
        UPDATE booking 
        SET status = 'CANCELLED', 
            cancelled_at = CURRENT_TIMESTAMP 
        WHERE id_booking = ?
    ");
    $stmt->execute([$booking_id]);
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success_message'] = "Pesanan berhasil dibatalkan.";
    header("Location: my_bookings.php");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header("Location: my_bookings.php");
    exit();
}
?>
