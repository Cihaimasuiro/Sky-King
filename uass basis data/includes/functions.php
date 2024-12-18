<?php
// Include database configuration
require_once 'config.php';

// Data sanitization function
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Execute database query
function execute_query($query, $params = []) {
    global $conn;
    $stmt = mysqli_prepare($conn, $query);
    
    if ($params) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    return $stmt;
}

// Format date
function format_date($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}
?>
