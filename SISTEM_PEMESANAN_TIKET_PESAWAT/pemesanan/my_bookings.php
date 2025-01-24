<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Silakan login terlebih dahulu";
    header("Location: login.php");
    exit();
}

try {
    // Ambil semua booking user dengan detail penerbangan
    $query = "SELECT b.*, f.*, m.nama as airline_name, m.kode as airline_code,
                     a1.nama_airport as from_airport, a1.kode_airport as from_code,
                     a2.nama_airport as to_airport, a2.kode_airport as to_code,
                     GROUP_CONCAT(CONCAT(p.title, '. ', p.full_name) SEPARATOR ', ') as passenger_names
              FROM booking b
              JOIN flight f ON b.id_flight = f.id_flight
              JOIN maskapai m ON f.id_maskapai = m.id_maskapai
              JOIN airport a1 ON f.from_airport = a1.id_airport
              JOIN airport a2 ON f.to_airport = a2.id_airport
              LEFT JOIN passengers p ON b.id_booking = p.booking_id
              WHERE b.id_user = :user_id
              GROUP BY b.id_booking, f.id_flight, m.id_maskapai, a1.id_airport, a2.id_airport
              ORDER BY b.booking_date DESC";
              
    error_log("Executing query for user_id: " . $_SESSION['user_id']);
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Found " . count($bookings) . " bookings");

} catch(PDOException $e) {
    error_log("Error in my_bookings.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $_SESSION['error_message'] = "Terjadi kesalahan saat mengambil data pemesanan";
    $bookings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemesanan - AirlineBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF6B35;
            --secondary-color: #1B4B72;
            --accent-color: #FFB566;
            --text-color: #333;
            --light-text: #fff;
            --sunset-gradient: linear-gradient(135deg, 
                #1B4B72 0%, 
                #2C5F8F 30%, 
                #FF6B35 70%, 
                #FFB566 100%
            );
            --sunset-overlay: linear-gradient(135deg, 
                rgba(27, 75, 114, 0.95), 
                rgba(44, 95, 143, 0.85),
                rgba(255, 107, 53, 0.85),
                rgba(255, 181, 102, 0.95)
            );
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--sunset-gradient);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Styles */
        .main-header {
            background: var(--sunset-gradient);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            color: var(--light-text);
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-btn {
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        /* Booking Card Styles */
        .booking-header {
            padding-top: 120px;
            background: var(--sunset-gradient);
            color: white;
            padding-bottom: 3rem;
            text-align: center;
        }

        .booking-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .booking-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            background: var(--secondary-color);
            color: white;
            padding: 1rem 2rem;
        }

        .card-body {
            padding: 2rem;
        }

        .flight-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .airport-info {
            text-align: center;
            flex: 1;
        }

        .airport-code {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .flight-path {
            position: relative;
            display: flex;
            align-items: center;
            flex: 2;
            padding: 0 2rem;
        }

        .flight-path::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            border-top: 2px dashed var(--primary-color);
        }

        .flight-path i {
            background: white;
            padding: 0.5rem;
            color: var(--primary-color);
            position: relative;
            z-index: 1;
        }

        .passenger-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.875rem;
        }

        .status-confirmed {
            background: #10b981;
            color: white;
        }

        .status-pending {
            background: #f59e0b;
            color: white;
        }

        .btn-view {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-view:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
        }

        /* Footer Styles */
        .main-footer {
            background: var(--secondary-color);
            color: var(--light-text);
            padding: 3rem 0;
            margin-top: auto;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 0 1rem;
            }

            .flight-info {
                flex-direction: column;
                text-align: center;
            }

            .flight-path {
                margin: 2rem 0;
            }

            .card-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">AirlineBooking</a>
            <div class="nav-links">
                <a href="index.php" class="nav-btn" style="color: white;">
                    <i class="fas fa-home"></i> Beranda
                </a>
            </div>
        </div>
    </header>

    <!-- Booking Header -->
    <section class="booking-header">
        <h1>Riwayat Pemesanan</h1>
        <p>Lihat dan kelola semua pemesanan tiket Anda</p>
    </section>

    <div class="booking-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="text-center py-5">
                <i class="fas fa-ticket-alt fa-3x mb-3" style="color: var(--primary-color);"></i>
                <h3>Belum ada pemesanan</h3>
                <p class="text-muted">Anda belum melakukan pemesanan tiket apapun</p>
                <a href="index.php" class="btn-view">Pesan Tiket Sekarang</a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Booking ID: #<?= str_pad($booking['id_booking'], 6, '0', STR_PAD_LEFT) ?></h5>
                            <small><?= date('d F Y H:i', strtotime($booking['booking_date'])) ?></small>
                        </div>
                        <span class="status-badge <?= $booking['status'] == 'CONFIRMED' ? 'status-confirmed' : 'status-pending' ?>">
                            <?= $booking['status'] ?>
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="flight-info">
                            <div class="airport-info">
                                <div class="airport-code"><?= $booking['from_code'] ?></div>
                                <div class="airport-name"><?= $booking['from_airport'] ?></div>
                                <div class="departure-time">
                                    <?= date('H:i', strtotime($booking['departure_time'])) ?>
                                    <div class="text-muted"><?= date('d M Y', strtotime($booking['departure_time'])) ?></div>
                                </div>
                            </div>

                            <div class="flight-path">
                                <i class="fas fa-plane"></i>
                            </div>

                            <div class="airport-info">
                                <div class="airport-code"><?= $booking['to_code'] ?></div>
                                <div class="airport-name"><?= $booking['to_airport'] ?></div>
                                <div class="arrival-time">
                                    <?= date('H:i', strtotime($booking['arrival_time'])) ?>
                                    <div class="text-muted"><?= date('d M Y', strtotime($booking['arrival_time'])) ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="passenger-info">
                            <h6><i class="fas fa-users me-2"></i>Penumpang:</h6>
                            <p class="mb-0"><?= $booking['passenger_names'] ?></p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Total Pembayaran</h6>
                                <h5 class="text-primary">Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></h5>
                            </div>
                            <a href="booking_confirmation.php?booking_id=<?= $booking['id_booking'] ?>" class="btn-view">
                                <i class="fas fa-eye me-2"></i>Lihat E-Ticket
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <p>&copy; 2024 AirlineBooking. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
