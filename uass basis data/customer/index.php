<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../index.php');
    exit();
}

// Get user's bookings
$user_id = $_SESSION['user_id'];

//Check if bookings table exists.  If not, create it.
$check_bookings_table = "SHOW TABLES LIKE 'bookings'";
$result_check = execute_query($check_bookings_table, []);
if(mysqli_num_rows($result_check) == 0){
    $create_bookings_table = "CREATE TABLE bookings (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, flight_id INT, booking_date DATETIME)";
    execute_query($create_bookings_table, []);
}

$query = "SELECT b.*, f.flight_number, f.departure_time, f.arrival_time FROM bookings b JOIN flights f ON b.flight_id = f.id WHERE b.user_id = ? ORDER BY b.booking_date DESC";

$result = execute_query($query, [$user_id]);

if (!$result) {
    die("Error fetching bookings: " . mysqli_error($conn)); //Improved error handling
}
?>
