<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch bookings
try {
    $query = "SELECT b.*, f.*, m.nama_maskapai, m.kode_maskapai,
                     a1.nama_airport as from_airport, a1.kode_airport as from_code,
                     a2.nama_airport as to_airport, a2.kode_airport as to_code,
                     GROUP_CONCAT(p.full_name SEPARATOR ', ') as passenger_names
              FROM booking b
              JOIN flight f ON b.id_flight = f.id_flight
              JOIN maskapai m ON f.id_maskapai = m.id_maskapai
              JOIN airport a1 ON f.from_airport = a1.id_airport
              JOIN airport a2 ON f.to_airport = a2.id_airport
              LEFT JOIN passengers p ON b.id_booking = p.id_booking
              WHERE b.id_users = ?
              GROUP BY b.id_booking
              ORDER BY b.booking_date DESC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    $bookings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Airline Booking</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b01;
            --secondary-color: #ff8534;
            --white: #ffffff;
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 1rem 2rem;
        }

        .navbar-brand, .nav-link {
            color: var(--white) !important;
        }

        .booking-card {
            background: var(--white);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .flight-info h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
        }

        .booking-date {
            color: #666;
            font-size: 0.9rem;
        }

        .booking-status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .booking-status.confirmed {
            background-color: #e3fcef;
            color: #00875a;
        }

        .booking-status.pending {
            background-color: #fff7e6;
            color: #b76e00;
        }

        .booking-status.cancelled {
            background-color: #ffe8e8;
            color: #c92a2a;
        }

        .booking-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-item i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .detail-item div {
            display: flex;
            flex-direction: column;
        }

        .detail-item small {
            color: #666;
            font-size: 0.8rem;
        }

        .detail-item strong {
            color: #333;
            font-size: 1rem;
        }

        .booking-actions {
            display: flex;
            gap: 10px;
        }

        .btn-view, .btn-cancel {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-view {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-cancel {
            background-color: #fff0f0;
            color: #c92a2a;
        }

        .no-bookings {
            text-align: center;
            padding: 50px 20px;
        }

        .no-bookings i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .no-bookings h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .no-bookings p {
            color: #666;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Airline Booking</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="booking.php">Pesan Tiket</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="pesanan_saya.php">Pesanan Saya</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <h2 class="mb-4">Pesanan Saya</h2>

        <?php if (empty($bookings)): ?>
            <div class="no-bookings">
                <i class="fas fa-ticket-alt"></i>
                <h3>Belum Ada Pemesanan</h3>
                <p>Anda belum melakukan pemesanan tiket. Mulai pesan tiket sekarang untuk perjalanan Anda!</p>
                <a href="booking.php" class="btn btn-view">Pesan Tiket</a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="flight-info">
                            <h3><?php echo htmlspecialchars($booking['from_airport'] . ' → ' . $booking['to_airport']); ?></h3>
                            <span class="booking-date">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('d M Y', strtotime($booking['booking_date'])); ?>
                            </span>
                        </div>
                        <span class="booking-status <?php echo strtolower($booking['status']); ?>">
                            <?php echo htmlspecialchars($booking['status']); ?>
                        </span>
                    </div>
                    <div class="booking-details">
                        <div class="detail-item">
                            <i class="fas fa-plane-departure"></i>
                            <div>
                                <small>Maskapai</small>
                                <strong><?php echo htmlspecialchars($booking['nama_maskapai']); ?></strong>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <small>Penumpang</small>
                                <strong><?php echo htmlspecialchars($booking['passenger_names']); ?></strong>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <div>
                                <small>Total Pembayaran</small>
                                <strong>Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="booking-actions">
                        <a href="booking_confirmation.php?booking_id=<?php echo $booking['id_booking']; ?>" class="btn-view">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                        <?php if ($booking['status'] == 'PENDING'): ?>
                            <a href="cancel_booking.php?booking_id=<?php echo $booking['id_booking']; ?>" class="btn-cancel" 
                               onclick="return confirm('Apakah Anda yakin ingin membatalkan pemesanan ini?');">
                                <i class="fas fa-times"></i> Batalkan
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
