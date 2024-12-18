<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../auth/login.php');
    exit();
}

if (!isset($_POST['flight_id'])) {
    header('Location: index.php');
    exit();
}

try {
    // Get flight details
    $flight_id = sanitize_input($_POST['flight_id']);
    $query = "SELECT f.*, 
              a1.name as departure_airport, 
              a2.name as arrival_airport,
              a1.city as departure_city,
              a2.city as arrival_city,
              f.price
              FROM flights f 
              JOIN airports a1 ON f.departure_airport_id = a1.id
              JOIN airports a2 ON f.arrival_airport_id = a2.id
              WHERE f.id = ?";
              
    $result = execute_query($query, [$flight_id]);
    $flight = mysqli_fetch_assoc(mysqli_stmt_get_result($result));
    
    if (!$flight) {
        throw new Exception('Flight not found');
    }
    
    // Calculate total amount
    $passengers = isset($_POST['passengers']) ? (int)$_POST['passengers'] : 1;
    $total_amount = $flight['price'] * $passengers;
    
    // Include the template with the booking form
    include 'templates/book.php';
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit();
}
