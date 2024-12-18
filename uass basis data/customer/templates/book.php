<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Flight - Clahstra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Clahstra</a>
            <!-- Add navigation items -->
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <!-- Main Booking Form -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Booking Details</h3>
                        
                        <!-- Flight Information -->
                        <div class="flight-info mb-4">
                            <!-- Flight details will be populated here -->
                        </div>

                        <form action="process_booking.php" method="POST">
                            <!-- Booking form fields -->
                        </form>
                    </div>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <!-- Booking summary will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>
