<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Create booking
    $user_id = $_SESSION['user_id'];
    $flight_id = sanitize_input($_POST['flight_id']);
    $total_amount = sanitize_input($_POST['total_amount']);
    $payment_method = sanitize_input($_POST['payment_method']);
    
    // Insert booking
    $query = "INSERT INTO bookings (user_id, flight_id, total_amount, payment_method, status, booking_date) 
              VALUES (?, ?, ?, ?, 'pending', NOW())";
    $result = execute_query($query, [$user_id, $flight_id, $total_amount, $payment_method]);
    $booking_id = mysqli_insert_id($conn);
    
    // Insert passenger details
    $passengers = $_POST['passenger'];
    foreach ($passengers as $passenger) {
        $query = "INSERT INTO passengers (booking_id, name, id_number, seat_number) 
                 VALUES (?, ?, ?, ?)";
        execute_query($query, [
            $booking_id,
            sanitize_input($passenger['name']),
            sanitize_input($passenger['id_number']),
            sanitize_input($passenger['seat'])
        ]);
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    $_SESSION['success'] = 'Booking successful! Your booking ID is: ' . $booking_id;
    header('Location: index.php');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    $_SESSION['error'] = 'Booking failed: ' . $e->getMessage();
    header('Location: book.php');
    exit();
}
