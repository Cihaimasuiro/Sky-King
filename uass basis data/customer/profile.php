<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header('Location: ../index.php');
    exit();
}

// Profile management functionality
?>
