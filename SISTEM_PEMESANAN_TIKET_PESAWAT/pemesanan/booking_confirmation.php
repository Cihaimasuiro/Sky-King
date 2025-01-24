<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if booking_id is provided
if (!isset($_GET['booking_id']) && !isset($_SESSION['booking_id'])) {
    $_SESSION['error_message'] = "ID Pemesanan tidak valid";
    header("Location: dashboard.php");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : $_SESSION['booking_id'];

try {
    // Get booking details with all necessary information
    $query = "
        SELECT 
            b.*, 
            f.departure_time, f.arrival_time, f.kelas, m.logo as logo,
            m.nama as nama_maskapai, m.kode as kode_maskapai,
            a1.nama_airport as from_airport_name, a1.kode_airport as from_airport_code,
            a2.nama_airport as to_airport_name, a2.kode_airport as to_airport_code,
            u.nama as nama_pemesan, u.email as email_pemesan,
            b.booking_date as created_at
        FROM booking b
        JOIN flight f ON b.id_flight = f.id_flight
        JOIN maskapai m ON f.id_maskapai = m.id_maskapai
        JOIN airport a1 ON f.from_airport = a1.id_airport
        JOIN airport a2 ON f.to_airport = a2.id_airport
        JOIN users u ON b.id_user = u.id
        WHERE b.id_booking = ? AND b.id_user = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception("Pemesanan tidak ditemukan atau Anda tidak memiliki akses");
    }

    // Get passenger details
    $stmt = $pdo->prepare("SELECT * FROM passengers WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    $passengers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - AirlineBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        }

        body {
            background: var(--sunset-gradient);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .brand {
            background: var(--sunset-gradient);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .brand-name {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            text-decoration: none;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .user-profile i {
            font-size: 1.2rem;
        }

        .main-content {
            margin-top: 80px;
            min-height: calc(100vh - 80px);
            padding: 2rem;
        }

        .page-header {
            color: white;
            padding: 2rem 0;
            text-align: center;
        }

        .booking-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .booking-header {
            background: #fff;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .booking-id {
            font-size: 1.2rem;
            color: #666;
        }

        .status-badge {
            background: #FFA500;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            font-size: 0.9rem;
            float: right;
        }

        .flight-route {
            padding: 2rem;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .airport {
            text-align: center;
            flex: 1;
        }

        .airport-code {
            font-size: 2rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .airport-name {
            color: #666;
            font-size: 0.9rem;
        }

        .flight-path {
            position: relative;
            flex: 2;
            height: 2px;
            background: #ddd;
            margin: 0 2rem;
        }

        .flight-path::after {
            content: '✈';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--primary-color);
            font-size: 1.5rem;
            background: white;
            padding: 0 1rem;
        }

        .flight-time {
            font-size: 1.2rem;
            color: #333;
            margin-top: 0.5rem;
        }

        .flight-date {
            font-size: 0.9rem;
            color: #666;
        }

        .home-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .home-link i {
            margin-right: 0.5rem;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin: 1rem;
        }

        .btn-action {
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            border: none;
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-print {
            background: var(--sunset-gradient);
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
            color: white;
        }

        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none !important;
            }
            .brand, .page-header, .action-buttons {
                display: none !important;
            }
            .booking-container {
                padding: 0;
            }
            .card {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="brand">
        <a href="index.php" class="brand-name">AirlineBooking</a>
        <a href="profile.php" class="user-profile">
            <i class="fas fa-user-circle"></i>
            <?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'User Profile' ?>
        </a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Riwayat Pemesanan</h1>
            <p>Lihat dan kelola semua pemesanan tiket Anda</p>
        </div>

        <div class="booking-container">
            <div class="card">
                <div class="booking-header">
                    <span class="status-badge">CONFIRMED</span>
                    <div class="booking-id">
                        Booking ID: #<?= str_pad($booking_id, 6, '0', STR_PAD_LEFT) ?>
                    </div>
                    <small class="text-muted">
                        <?= isset($booking['created_at']) ? date('d F Y H:i', strtotime($booking['created_at'])) : '' ?>
                    </small>
                </div>

                <div class="action-buttons no-print">
                    <button onclick="window.print()" class="btn-action btn-print">
                        <i class="fas fa-print"></i>
                        Cetak E-Ticket
                    </button>
                </div>

                <div class="flight-route">
                    <div class="airport">
                        <div class="airport-code"><?= $booking['from_airport_code'] ?></div>
                        <div class="airport-name"><?= $booking['from_airport_name'] ?></div>
                        <div class="flight-time"><?= date('H:i', strtotime($booking['departure_time'])) ?></div>
                        <div class="flight-date"><?= date('d M Y', strtotime($booking['departure_time'])) ?></div>
                    </div>
                    
                    <div class="flight-path"></div>
                    
                    <div class="airport">
                        <div class="airport-code"><?= $booking['to_airport_code'] ?></div>
                        <div class="airport-name"><?= $booking['to_airport_name'] ?></div>
                        <div class="flight-time"><?= date('H:i', strtotime($booking['arrival_time'])) ?></div>
                        <div class="flight-date"><?= date('d M Y', strtotime($booking['arrival_time'])) ?></div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-plane me-2"></i>Detail Penerbangan</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td>Maskapai</td>
                                    <td>
                                        <img src="..\<?= strtolower($booking['logo']) ?>" 
                                             alt="<?= $booking['nama_maskapai'] ?>"
                                             class="img-fluid"
                                             style="max-height: 30px;">
                                        <?= $booking['nama_maskapai'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Nomor Penerbangan</td>
                                    <td><?= $booking['kode_maskapai'] ?>-<?= rand(1000, 9999) ?></td>
                                </tr>
                                <tr>
                                    <td>Kelas</td>
                                    <td><?= $booking['kelas'] ?></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fas fa-user me-2"></i>Detail Penumpang</h5>
                            <?php foreach ($passengers as $passenger): ?>
                            <div class="mb-3">
                                <strong><?= $passenger['title'] ?>. <?= $passenger['full_name'] ?></strong><br>
                                <small class="text-muted">
                                    Nationality: <?= $passenger['nationality'] ?><br>
                                    ID/Passport: <?= $passenger['id_passport'] ?>
                                </small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-money-bill me-2"></i>Detail Pembayaran</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td>Total Pembayaran</td>
                                    <td>Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td>Status Pembayaran</td>
                                    <td><span class="badge bg-success">Lunas</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
