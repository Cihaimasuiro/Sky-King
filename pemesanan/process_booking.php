<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        error_log("Processing booking...");
        error_log("POST data: " . print_r($_POST, true));
        
        // Validate required fields
        $required_fields = ['flight_id', 'total_price', 'passenger_name', 'passenger_title', 'passenger_nationality', 'passenger_passport'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field])) {
                throw new Exception("Data pemesanan tidak lengkap: $field tidak ditemukan");
            }
        }

        // Store booking data in session
        $_SESSION['temp_booking_data'] = [
            'flight_id' => $_POST['flight_id'],
            'total_price' => $_POST['total_price'],
            'passengers' => count($_POST['passenger_name']),
            'passenger_name' => $_POST['passenger_name'],
            'passenger_title' => $_POST['passenger_title'],
            'passenger_nationality' => $_POST['passenger_nationality'],
            'passenger_passport' => $_POST['passenger_passport']
        ];
        
        error_log("Temp booking data saved to session: " . print_r($_SESSION['temp_booking_data'], true));
        
        // Redirect to payment page
        header("Location: payment.php");
        exit();
        
    } catch (Exception $e) {
        error_log("Error in process_booking.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: booking.php");
        exit();
    }
} else {
    header("Location: booking.php");
    exit();
}
?>
