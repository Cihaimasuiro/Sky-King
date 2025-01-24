<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['flight_id'])) {
    header("Location: dashboard.php");
    exit();
}

try {
    // Get flight details
    $stmt = $pdo->prepare("
        SELECT p.*, 
               b1.nama as bandara_asal, 
               b2.nama as bandara_tujuan,
               b1.kota as kota_asal,
               b2.kota as kota_tujuan
        FROM Penerbangan p
        JOIN Bandara b1 ON p.asal_penerbangan = b1.id_bandara
        JOIN Bandara b2 ON p.tujuan_penerbangan = b2.id_bandara
        WHERE p.id_penerbangan = ?
    ");
    $stmt->execute([$_POST['flight_id']]);
    $flight = $stmt->fetch();

    if (!$flight) {
        throw new Exception("Penerbangan tidak ditemukan");
    }

    // Get customer details
    $stmt = $pdo->prepare("SELECT * FROM Customer WHERE id_customer = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $customer = $stmt->fetch();

} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: dashboard.php");
    exit();
}

// Calculate base price (this would typically come from a price table in the database)
$base_price = 1000000; // Example base price
$passengers = intval($_POST['passengers']);
$class_multiplier = [
    'economy' => 1,
    'business' => 2,
    'first' => 3
][$_POST['class']] ?? 1;

$total_price = $base_price * $passengers * $class_multiplier;
$tax = $total_price * 0.1;
$total_with_tax = $total_price + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pemesanan - AirlineBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">AirlineBooking</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Selamat datang, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Keluar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <!-- Main Booking Form -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Detail Pemesanan</h3>
                        
                        <!-- Flight Information -->
                        <div class="flight-info mb-4">
                            <h5>Detail Penerbangan</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Nomor Penerbangan:</strong> <?php echo htmlspecialchars($flight['nomor_penerbangan']); ?></p>
                                            <p><strong>Tanggal:</strong> <?php echo htmlspecialchars($_POST['date']); ?></p>
                                            <p><strong>Kelas:</strong> <?php echo ucfirst(htmlspecialchars($_POST['class'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Dari:</strong> <?php echo htmlspecialchars($flight['bandara_asal']); ?></p>
                                            <p><strong>Ke:</strong> <?php echo htmlspecialchars($flight['bandara_tujuan']); ?></p>
                                            <p><strong>Waktu:</strong> <?php echo htmlspecialchars($flight['jam_berangkat']); ?> - <?php echo htmlspecialchars($flight['jam_kedatangan']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="process_booking.php" method="POST" id="bookingForm">
                            <input type="hidden" name="flight_id" value="<?php echo htmlspecialchars($_POST['flight_id']); ?>">
                            <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($total_with_tax); ?>">

                            <!-- Passenger Information -->
                            <?php for ($i = 1; $i <= $passengers; $i++): ?>
                            <div class="passenger-info mb-4">
                                <h5>Data Penumpang <?php echo $i; ?></h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Title</label>
                                                <select class="form-select" name="passenger_title[]" required>
                                                    <option value="Mr">Mr</option>
                                                    <option value="Mrs">Mrs</option>
                                                    <option value="Ms">Ms</option>
                                                </select>
                                            </div>
                                            <div class="col-md-9">
                                                <label class="form-label">Nama Lengkap</label>
                                                <input type="text" class="form-control" name="passenger_name[]" required
                                                    <?php if ($i === 1) echo 'value="'.htmlspecialchars($customer['nama']).'"'; ?>>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Kewarganegaraan</label>
                                                <input type="text" class="form-control" name="passenger_nationality[]" required value="Indonesia">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nomor KTP/Paspor</label>
                                                <input type="text" class="form-control" name="passenger_passport[]" required
                                                    <?php if ($i === 1) echo 'value="'.htmlspecialchars($customer['no_pasport']).'"'; ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endfor; ?>

                            <!-- Contact Information -->
                            <div class="contact-info mb-4">
                                <h5>Informasi Kontak</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="contact_email" 
                                                    value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nomor Telepon</label>
                                                <input type="tel" class="form-control" name="contact_phone" 
                                                    value="<?php echo htmlspecialchars($customer['no_telepon']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">Lanjut ke Pembayaran</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ringkasan Pemesanan</h5>
                        <div class="summary-details">
                            <p><strong>Penerbangan:</strong> <?php echo htmlspecialchars($flight['nomor_penerbangan']); ?></p>
                            <p><strong>Rute:</strong> <?php echo htmlspecialchars($flight['kota_asal']); ?> → <?php echo htmlspecialchars($flight['kota_tujuan']); ?></p>
                            <p><strong>Tanggal:</strong> <?php echo htmlspecialchars($_POST['date']); ?></p>
                            <p><strong>Jumlah Penumpang:</strong> <?php echo htmlspecialchars($passengers); ?></p>
                            <p><strong>Kelas:</strong> <?php echo ucfirst(htmlspecialchars($_POST['class'])); ?></p>
                            
                            <hr>
                            
                            <div class="price-details">
                                <p class="d-flex justify-content-between">
                                    <span>Harga Tiket (<?php echo $passengers; ?> x <?php echo number_format($base_price * $class_multiplier, 0, ',', '.'); ?>)</span>
                                    <span>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></span>
                                </p>
                                <p class="d-flex justify-content-between">
                                    <span>Pajak & Biaya</span>
                                    <span>Rp <?php echo number_format($tax, 0, ',', '.'); ?></span>
                                </p>
                                <hr>
                                <p class="d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <span>Rp <?php echo number_format($total_with_tax, 0, ',', '.'); ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
