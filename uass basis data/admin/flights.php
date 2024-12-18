<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    header('Location: ../index.php');
    exit();
}

// Flight management functionality
?>
