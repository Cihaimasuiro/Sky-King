<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    header('Location: ../index.php');
    exit();
}

// Booking management functionality
$query = "SELECT b.*, u.name as customer_name, f.flight_number 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN flights f ON b.flight_id = f.id 
          ORDER BY b.booking_date DESC";

$result = mysqli_query($conn, $query);
?>
