<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: ../auth/login.php');
    exit();
}

// Get dashboard statistics
$stats = [
    'total_bookings' => 0,
    'total_revenue' => 0,
    'active_flights' => 0,
    'total_users' => 0
];

// Get total bookings and revenue
$query = "SELECT COUNT(*) as total_bookings, SUM(total_amount) as total_revenue 
          FROM bookings 
          WHERE status = 'paid'";
$result = mysqli_query($conn, $query);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_bookings'] = $row['total_bookings'];
    $stats['total_revenue'] = $row['total_revenue'] ?? 0;
}

// Get active flights
$query = "SELECT COUNT(*) as active_flights 
          FROM flights 
          WHERE departure_time > NOW()";
$result = mysqli_query($conn, $query);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['active_flights'] = $row['active_flights'];
}

// Get total users
$query = "SELECT COUNT(*) as total_users FROM users WHERE role = 'customer'";
$result = mysqli_query($conn, $query);
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_users'] = $row['total_users'];
}

// Get recent bookings
$query = "SELECT b.*, u.name as customer_name, f.flight_number 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN flights f ON b.flight_id = f.id 
          ORDER BY b.booking_date DESC 
          LIMIT 5";
$recent_bookings = mysqli_query($conn, $query);

// Include dashboard template
include 'templates/dashboard.php';
?>
