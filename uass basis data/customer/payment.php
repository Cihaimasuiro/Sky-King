<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/payment.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../auth/login.php');
    exit();
}

// Get booking ID from URL
$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if (!$bookingId) {
    header('Location: index.php');
    exit();
}

// Get booking details
$query = "SELECT b.*, f.flight_number, f.departure_time, f.arrival_time, 
          f.origin, f.destination, f.price 
          FROM bookings b 
          JOIN flights f ON b.flight_id = f.id 
          WHERE b.id = ? AND b.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $bookingId, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header('Location: index.php');
    exit();
}

// Initialize payment handler
$payment = new Payment();
$paymentHistory = $payment->getPaymentsByBooking($bookingId);

// Include header
require_once '../includes/header.php';
?>

<div class="container py-4">
    <h2>Payment for Booking #<?php echo $bookingId; ?></h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Booking Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Flight:</strong> <?php echo htmlspecialchars($booking['flight_number']); ?></p>
                    <p><strong>From:</strong> <?php echo htmlspecialchars($booking['origin']); ?></p>
                    <p><strong>To:</strong> <?php echo htmlspecialchars($booking['destination']); ?></p>
                    <p><strong>Departure:</strong> <?php echo date('d M Y H:i', strtotime($booking['departure_time'])); ?></p>
                    <p><strong>Arrival:</strong> <?php echo date('d M Y H:i', strtotime($booking['arrival_time'])); ?></p>
                    <p><strong>Total Amount:</strong> Rp <?php echo number_format($booking['total_amount']); ?></p>
                </div>
            </div>
            
            <?php if ($booking['payment_status'] !== 'paid'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Form</h5>
                </div>
                <div class="card-body">
                    <form action="process_payment.php" method="POST">
                        <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="">Select Payment Method</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="e_wallet">E-Wallet</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount to Pay</label>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   value="<?php echo $booking['total_amount']; ?>" readonly>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Process Payment</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($paymentHistory)): ?>
                        <p>No payment records found.</p>
                    <?php else: ?>
                        <?php foreach ($paymentHistory as $payment): ?>
                            <div class="mb-3">
                                <p><strong>Date:</strong> <?php echo date('d M Y H:i', strtotime($payment['payment_date'])); ?></p>
                                <p><strong>Amount:</strong> Rp <?php echo number_format($payment['amount']); ?></p>
                                <p><strong>Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-<?php echo $payment['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($payment['payment_status']); ?>
                                    </span>
                                </p>
                                <hr>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
