<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = "127.0.0.1";
$db_port = "8081";
$db_name = "uasbasisdata";
$db_user = "root";
$db_pass = "";

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

} catch (PDOException $e) {

    $errorMessage = "Database connection failed: " . $e->getMessage();
    error_log($errorMessage);


    $errorCode = $e->getCode();

    switch ($errorCode) {
        case 2002:
           $userFriendlyMessage = "Error connecting to the database server. Please check if the database server is running and accessible.";
            break;
        case 1045:
            $userFriendlyMessage = "Database access denied. Please verify your database credentials.";
            break;
        case 1049:
            $userFriendlyMessage = "Unknown database '$db_name'. Please ensure the database name is correct.";
            break;
        default:
            $userFriendlyMessage = "A database error occurred.  Please contact support. Error Details (for support): " . $errorMessage; // Generic message, but logs details
    }

    die($userFriendlyMessage);
}

?>
