<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format';
        header('Location: register.php');
        exit();
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: register.php');
        exit();
    }
    
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long';
        header('Location: register.php');
        exit();
    }
    
    // Check if email already exists
    $query = "SELECT id FROM users WHERE email = ?";
    $result = execute_query($query, [$email]);
    if (mysqli_num_rows(mysqli_stmt_get_result($result)) > 0) {
        $_SESSION['error'] = 'Email already registered';
        header('Location: register.php');
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $query = "INSERT INTO users (name, email, phone, password, role, created_at) 
              VALUES (?, ?, ?, ?, 'customer', NOW())";
    
    try {
        execute_query($query, [$name, $email, $phone, $hashed_password]);
        
        // Auto login after registration
        $user_id = mysqli_insert_id($conn);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_role'] = 'customer';
        
        header('Location: ../customer/');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        header('Location: register.php');
        exit();
    }
} else {
    header('Location: register.php');
    exit();
}
