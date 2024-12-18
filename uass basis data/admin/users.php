<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    header('Location: ../index.php');
    exit();
}

// User management functionality
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
