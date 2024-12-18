<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    header('Location: ../index.php');
    exit();
}

// Report generation functionality
function generateBookingReport($start_date, $end_date) {
    global $conn;
    $query = "SELECT COUNT(*) as total_bookings, SUM(total_amount) as total_revenue 
              FROM bookings 
              WHERE booking_date BETWEEN ? AND ?";
    
    return execute_query($query, [$start_date, $end_date]);
}

function generateFlightReport() {
    global $conn;
    $query = "SELECT f.*, COUNT(b.id) as total_bookings 
              FROM flights f 
              LEFT JOIN bookings b ON f.id = b.flight_id 
              GROUP BY f.id";
    
    return mysqli_query($conn, $query);
}
?>
