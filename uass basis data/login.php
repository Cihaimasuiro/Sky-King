<?php
session_start();

// Check for success message from registration
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear the session messages
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Check for remember me cookie
$remembered_email = isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Airline Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Masuk</h2>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <form action="process_login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="<?php echo htmlspecialchars($remembered_email); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember"
                                    <?php echo $remembered_email ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="remember">Ingat saya</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Masuk</button>
                        </form>
                        <div class="text-center mt-3">
                            Belum punya akun? <a href="register.php">Daftar di sini</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
