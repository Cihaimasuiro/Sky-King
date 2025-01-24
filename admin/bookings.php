    <?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Status Update
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("UPDATE booking SET status = ? WHERE id_booking = ?");
        $stmt->execute([$status, $booking_id]);
        $success_message = "Booking status updated successfully!";
    } catch(PDOException $e) {
        $error_message = "Error updating booking status: " . $e->getMessage();
    }
}

// Handle Delete Operation
if (isset($_POST['delete_booking'])) {
    $booking_id = $_POST['booking_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM booking WHERE id_booking = ?");
        $stmt->execute([$booking_id]);
        $success_message = "Booking deleted successfully!";
    } catch(PDOException $e) {
        $error_message = "Error deleting booking: " . $e->getMessage();
    }
}

// Get all bookings with related information
$bookings_query = "
    SELECT 
        b.*, 
        u.nama as customer_name,
        u.email as customer_email,
        f.departure_time,
        f.arrival_time,
        m.nama as airline_name,
        a1.kota as from_city,
        a2.kota as to_city
    FROM booking b
    JOIN users u ON b.id_user = u.id
    JOIN flight f ON b.id_flight = f.id_flight
    JOIN maskapai m ON f.id_maskapai = m.id_maskapai
    JOIN airport a1 ON f.from_airport = a1.id_airport
    JOIN airport a2 ON f.to_airport = a2.id_airport
    ORDER BY b.booking_date DESC
";
$bookings = $pdo->query($bookings_query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h2>Manage Bookings</h2>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Flight Details</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo $booking['id_booking']; ?></td>
                            <td>
                                <div class="customer-info">
                                    <div><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                    <div class="email"><?php echo htmlspecialchars($booking['customer_email']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="flight-info">
                                    <div><?php echo htmlspecialchars($booking['airline_name']); ?></div>
                                    <div class="route"><?php echo htmlspecialchars($booking['from_city']); ?> → <?php echo htmlspecialchars($booking['to_city']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="schedule-info">
                                    <?php 
                                        $departure = new DateTime($booking['departure_time']);
                                        $arrival = new DateTime($booking['arrival_time']);
                                    ?>
                                    <div><?php echo $departure->format('d M Y'); ?></div>
                                    <div class="time"><?php echo $departure->format('H:i') . ' - ' . $arrival->format('H:i'); ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="price">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></div>
                            </td>
                            <td class="actions">
                                <?php if ($booking['status'] !== 'cancelled'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id_booking']; ?>">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" name="update_status" class="btn btn-cancel" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        <i class="ri-close-circle-line"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id_booking']; ?>">
                                    <button type="submit" name="delete_booking" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this booking?')">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
