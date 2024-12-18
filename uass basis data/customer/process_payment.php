<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/payment.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../auth/login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// Get form data
$bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;

// Validate inputs
if (!$bookingId || !$paymentMethod || !$amount) {
    $_SESSION['error'] = "Invalid payment data provided.";
    header('Location: payment.php?booking_id=' . $bookingId);
    exit();
}

try {
    // Initialize payment handler
    $payment = new Payment();
    
    // Process payment
    $paymentData = [
        'method' => $paymentMethod,
        'amount' => $amount,
        'status' => 'paid',
        'transaction_id' => generate_transaction_id()
    ];
    
    $payment->processPayment($bookingId, $paymentData);
    
    $_SESSION['success'] = "Payment processed successfully!";
    header('Location: index.php');
    
} catch (Exception $e) {
    $_SESSION['error'] = "Payment processing failed: " . $e->getMessage();
    header('Location: payment.php?booking_id=' . $bookingId);
}

// Helper function to generate transaction ID
function generate_transaction_id() {
    return 'TRX' . date('YmdHis') . rand(1000, 9999);
}
