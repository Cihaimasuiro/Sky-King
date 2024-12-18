<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Location: ../index.php');
    exit();
}

try {
    $from = isset($_GET['from']) ? sanitize_input($_GET['from']) : '';
    $to = isset($_GET['to']) ? sanitize_input($_GET['to']) : '';
    $date = isset($_GET['date']) ? sanitize_input($_GET['date']) : '';
    $passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;
    
    // Search flights
    $query = "SELECT f.*, 
              a1.name as departure_airport, a1.city as departure_city,
              a2.name as arrival_airport, a2.city as arrival_city
              FROM flights f
              JOIN airports a1 ON f.departure_airport_id = a1.id
              JOIN airports a2 ON f.arrival_airport_id = a2.id
              WHERE (a1.city LIKE ? OR a1.name LIKE ?)
              AND (a2.city LIKE ? OR a2.name LIKE ?)
              AND DATE(f.departure_time) = ?
              AND f.available_seats >= ?
              ORDER BY f.departure_time";
              
    $params = [
        "%$from%", "%$from%",
        "%$to%", "%$to%",
        $date,
        $passengers
    ];
    
    $result = execute_query($query, $params);
    $flights = mysqli_fetch_all(mysqli_stmt_get_result($result), MYSQLI_ASSOC);
    
    // Include search results template
    include 'templates/results.php';
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../index.php');
    exit();
}
