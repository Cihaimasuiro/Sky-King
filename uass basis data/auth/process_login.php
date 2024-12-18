<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (login($email, $password)) {
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $user_id = $_SESSION['user_id'];
            
            // Store token in database
            $query = "INSERT INTO remember_tokens (user_id, token, expires_at) 
                     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))";
            execute_query($query, [$user_id, $token]);
            
            // Set cookie
            setcookie('remember_token', $token, time() + (86400 * 30), '/');
        }
        
        // Redirect based on user role
        header('Location: ' . (is_admin() ? '../admin/' : '../customer/'));
        exit();
    } else {
        $_SESSION['error'] = 'Invalid email or password';
        header('Location: login.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
