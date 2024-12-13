<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Get search parameters
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$date = $_GET['date'] ?? '';
$passengers = $_GET['passengers'] ?? 1;
$class = $_GET['class'] ?? 'economy';

// Format date for display
setlocale(LC_TIME, 'id_ID');
$formatted_date = date('l, d F Y', strtotime($date));

// Query available flights from database
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               b1.nama as bandara_asal, 
               b2.nama as bandara_tujuan,
               b1.kota as kota_asal,
               b2.kota as kota_tujuan,
               b1.kode as kode_asal,
               b2.kode as kode_tujuan,
               m.nama as nama_maskapai,
               m.logo as logo_maskapai
        FROM Penerbangan p
        JOIN Bandara b1 ON p.asal_penerbangan = b1.id_bandara
        JOIN Bandara b2 ON p.tujuan_penerbangan = b2.id_bandara
        JOIN Maskapai m ON p.id_maskapai = m.id_maskapai
        WHERE b1.kode = ? AND b2.kode = ?
    ");
    $stmt->execute([$from, $to]);
    $flights = $stmt->fetchAll();

} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - Airline Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .flight-card {
            transition: transform 0.2s;
        }
        .flight-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .airline-logo {
            max-height: 40px;
            width: auto;
        }
        .flight-time {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .airport-code {
            color: #666;
            font-size: 0.9rem;
        }
        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
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

    <div class="container my-4">
        <!-- Search Summary -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-4">Detail Pencarian</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Dari</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($from); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Ke</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($to); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Tanggal</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($formatted_date); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person text-primary me-2"></i>
                            <div>
                                <small class="text-muted">Penumpang</small>
                                <div class="fw-bold"><?php echo htmlspecialchars($passengers); ?> Orang</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flight Results -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif (empty($flights)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Tidak ada penerbangan yang tersedia untuk pencarian Anda.
                <div class="mt-3">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Cari Penerbangan Lain
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($flights as $flight): ?>
                <div class="card flight-card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <img src="<?php echo htmlspecialchars($flight['logo_maskapai']); ?>" 
                                     alt="<?php echo htmlspecialchars($flight['nama_maskapai']); ?>" 
                                     class="airline-logo mb-2">
                                <div class="text-muted"><?php echo htmlspecialchars($flight['nama_maskapai']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($flight['nomor_penerbangan']); ?></small>
                            </div>
                            <div class="col-md-6">
                                <div class="row text-center">
                                    <div class="col">
                                        <div class="flight-time"><?php echo htmlspecialchars($flight['jam_berangkat']); ?></div>
                                        <div class="airport-code"><?php echo htmlspecialchars($flight['kode_asal']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($flight['kota_asal']); ?></small>
                                    </div>
                                    <div class="col-5">
                                        <div class="flight-duration">
                                            <i class="bi bi-airplane"></i>
                                        </div>
                                        <div class="border-top"></div>
                                        <small class="text-muted">Langsung</small>
                                    </div>
                                    <div class="col">
                                        <div class="flight-time"><?php echo htmlspecialchars($flight['jam_kedatangan']); ?></div>
                                        <div class="airport-code"><?php echo htmlspecialchars($flight['kode_tujuan']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($flight['kota_tujuan']); ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="price mb-3">
                                    Rp <?php echo number_format($flight['harga'], 0, ',', '.'); ?>
                                </div>
                                <form action="booking.php" method="POST">
                                    <input type="hidden" name="flight_id" value="<?php echo htmlspecialchars($flight['id_penerbangan']); ?>">
                                    <input type="hidden" name="passengers" value="<?php echo htmlspecialchars($passengers); ?>">
                                    <input type="hidden" name="class" value="<?php echo htmlspecialchars($class); ?>">
                                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-ticket-perforated me-2"></i>Pilih
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
