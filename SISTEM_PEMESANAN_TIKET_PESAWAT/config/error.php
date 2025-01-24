<?php
session_start();

// Function to get a user-friendly error message based on error code or exception
function getUserFriendlyErrorMessage($error) {
    if ($error instanceof PDOException) {
        $errorCode = $error->getCode();
        switch ($errorCode) {
            case 2002: // Can't connect to MySQL server
                return "Error connecting to the database server. Please check if the server is running and accessible.";
            case 1045: // Access denied
                return "Database access denied. Please verify your database credentials.";
            case 1049: // Unknown database
                return "Unknown database. Please ensure the database name is correct.";
            // Add more PDOException cases as needed
            default:
                // Log the full error for debugging, but show a generic message to the user
                error_log("Database Error: " . $error->getMessage());
                return "A database error occurred. Please contact support."; 
        }
    } elseif ($error instanceof Exception) { // For other exceptions
        // Handle other specific exceptions as needed
        switch(true){
            case strpos($error->getMessage(), "Penerbangan tidak ditemukan") !== false:
                return "Penerbangan yang Anda cari tidak ditemukan.";
            // add another case
            default:
               error_log("Application Error: " . $error->getMessage());
               return "An error occurred. Please contact support."; // Generic message with detailed logging
        }


    } else {
        // If it's not an exception object, handle it gracefully
        return "An unexpected error occurred.";  // Or some other general error
    }
}


// Check if an error message is set in the session
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the error message after displaying it

    // Get the user-friendly error message
    $userFriendlyMessage = getUserFriendlyErrorMessage($errorMessage); 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <!-- Add Bootstrap CSS or any other styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($userFriendlyMessage);  // Make sure to escape output!?>
<?php
            // Include additional, less sensitive, error details, if appropriate.  Only for debugging!
            // if (defined('DEBUG') && DEBUG) {
            //    echo "<pre>";
            //    var_dump($errorMessage); // Or use print_r
            //    echo "</pre>";
            // }
?>
        </div>
         <a href="javascript:history.back()" class="btn btn-primary">Go Back</a> <a href="index.php" class="btn btn-primary">Home</a>
    </div>


</body>
</html>
<?php
    exit(); // Stop further execution
}


// If there are no errors in session, redirect to a default page or do nothing
// header("Location: index.php"); // Or another page 
// exit();

?>
