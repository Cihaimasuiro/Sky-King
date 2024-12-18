<?php
session_start();
require_once 'functions.php';

// User login function
function login($email, $password) {
    global $conn;
    
    $email = sanitize_input($email);
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = execute_query($query, [$email]);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
    }
    return false;
}

// User logout function
function logout() {
    session_unset();
    session_destroy();
    return true;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
?>
