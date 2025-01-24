<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Ambil data user dari session
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';

// Connect to database
require_once '../config/database.php';

// Initialize stats array with default values
$stats = array(
    'total_pemesanan' => 0,
    'completed_bookings' => 0,
    'total_spent' => 0,
    'unique_flights' => 0
);

try {
    // Mengambil statistik booking
    $stats_query = "SELECT 
        COUNT(*) as total_pemesanan,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as completed_bookings,
        COALESCE(SUM(CASE WHEN total_price IS NOT NULL THEN total_price ELSE 0 END), 0) as total_spent,
        COUNT(DISTINCT id_flight) as unique_flights
    FROM booking 
    WHERE id_user = :user_id";
    
    $stats_stmt = $pdo->prepare($stats_query);
    $stats_stmt->execute(['user_id' => $_SESSION['user_id']]);
    $result = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Update stats with database results if available
    if ($result) {
        $stats['total_pemesanan'] = (int)$result['total_pemesanan'];
        $stats['completed_bookings'] = (int)$result['completed_bookings'];
        $stats['total_spent'] = (float)$result['total_spent'];
        $stats['unique_flights'] = (int)$result['unique_flights'];
    }

} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $_SESSION['error_message'] = "Terjadi kesalahan saat mengambil data";
}

// Debug: Print values to check
echo "<!-- Debug Info: User ID: " . $_SESSION['user_id'] . " -->";
echo "<!-- Debug Info: Stats: " . print_r($stats, true) . " -->";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AirlineBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF6B35;
            --secondary-color: #1B4B72;
            --accent-color: #FFB566;
            --gradient-1: linear-gradient(135deg, #FF6B35 0%, #FFB566 100%);
            --gradient-2: linear-gradient(135deg, #1B4B72 0%, #2D82B5 100%);
        }

        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: var(--gradient-2);
            color: white;
            padding: 2rem 0;
            position: relative;
            overflow: hidden;
            min-height: 300px;
            display: flex;
            align-items: center;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: url('https://www.transparentpng.com/thumb/airplane/airplane-free-cut-out-6.png') no-repeat;
            background-size: contain;
            opacity: 0.1;
            transform: rotate(45deg);
        }

        .profile-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-top: -50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--gradient-1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-1);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
            position: relative;
        }

        .action-icon::after {
            content: '';
            position: absolute;
            width: 70px;
            height: 70px;
            border: 2px solid var(--primary-color);
            border-radius: 50%;
            opacity: 0.3;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: var(--gradient-1);
            opacity: 0.1;
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .welcome-text {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-custom {
            background: var(--gradient-1);
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
            color: white;
        }

        .weather-widget {
            background: var(--gradient-2);
            border-radius: 15px;
            padding: 1.5rem;
            color: white;
            margin-top: 2rem;
        }

        .weather-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }

        /* Tambahan CSS untuk fitur baru */
        .promo-section {
            margin-bottom: 2rem;
        }

        .promo-card {
            background: var(--gradient-1);
            border-radius: 15px;
            padding: 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .promo-content {
            z-index: 1;
        }

        .promo-image {
            position: absolute;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .notification-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .notification-item {
            display: flex;
            align-items: start;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .notification-item i {
            margin-right: 1rem;
            margin-top: 0.25rem;
        }

        .notification-content p {
            margin-bottom: 0.25rem;
        }

        .destination-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .destination-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .destination-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
        }

        .destination-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .destination-item:hover img {
            transform: scale(1.1);
        }

        .destination-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
        }

        .destination-overlay h5 {
            margin-bottom: 0.25rem;
        }

        .destination-overlay p {
            margin: 0;
            font-size: 0.9rem;
        }

        .travel-tips {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .tip-item {
            display: flex;
            align-items: start;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .tip-item i {
            margin-right: 1rem;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .tip-item h5 {
            margin-bottom: 0.25rem;
        }

        .tip-item p {
            margin: 0;
            color: #6c757d;
        }

        .dashboard-footer {
            margin-top: 4rem;
            padding: 3rem 0 1rem;
            background: var(--gradient-2);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .social-links {
            margin-top: 1rem;
        }

        .social-links a {
            color: white;
            margin-right: 1rem;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .social-links a:hover {
            transform: translateY(-3px);
        }

        .quick-links {
            list-style: none;
            padding: 0;
        }

        .quick-links li {
            margin-bottom: 0.5rem;
        }

        .quick-links a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .quick-links a:hover {
            padding-left: 0.5rem;
            opacity: 0.8;
        }

        .contact-info {
            list-style: none;
            padding: 0;
        }

        .contact-info li {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .contact-info i {
            margin-right: 0.5rem;
            width: 20px;
        }

        .footer-bottom {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .airlines-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .airlines-card h4 {
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            font-weight: 600;
            padding-left: 0.5rem;
            border-left: 4px solid var(--primary-color);
            text-align: center;
        }

        .airlines-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 1rem;
            padding: 1rem;
        }

        .airline-item {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            border-radius: 12px;
            background: white;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            height: 100px;
        }

        .airline-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .airline-logo {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .airline-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: all 0.3s ease;
        }

        .airline-item:hover .airline-logo img {
            transform: scale(1.1);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.4rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            text-align: center;
        }

        .btn-outline-primary:hover {
            background: var(--gradient-1);
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <h1 class="welcome-text">Welcome Back!</h1>
            <p class="lead mb-0">Selamat datang di dashboard penerbangan Anda</p>
        </div>
    </div>

    <div class="container">
        <div class="profile-section">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="profile-image">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="col-md-7">
                    <h2 class="mb-1"><?php echo htmlspecialchars($user_name); ?></h2>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($user_email); ?></p>
                </div>
                <div class="col-md-3 text-end">
                    <a href="profile.php" class="btn btn-custom">
                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <div class="action-card" onclick="window.location.href='index.php'">
                <div class="action-icon floating-icon">
                    <i class="fas fa-plane"></i>
                </div>
                <h4>Pesan Tiket</h4>
                <p class="text-muted">Mulai perjalanan baru Anda</p>
            </div>
            <div class="action-card" onclick="window.location.href='my_bookings.php'">
                <div class="action-icon floating-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h4>Pesanan Saya</h4>
                <p class="text-muted">Lihat semua pesanan Anda</p>
            </div>
            <div class="action-card" onclick="window.location.href='../service/index.php'">
                <div class="action-icon floating-icon">
                    <i class="fas fa-headset"></i>
                </div>
                    <h4>Bantuan</h4>
                <p class="text-muted">Hubungi customer service</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">
                    <?php echo number_format($stats['total_pemesanan']); ?>
                </div>
                <div class="stat-label">Total Pemesanan</div>
                <i class="fas fa-ticket-alt fa-2x text-muted position-absolute end-0 bottom-0 mb-3 me-3"></i>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    Rp <?php echo number_format($stats['total_spent'] ?? 0, 0, ',', '.'); ?>
                </div>
                <div class="stat-label">Total Pengeluaran</div>
                <i class="fas fa-money-bill-wave fa-2x text-muted position-absolute end-0 bottom-0 mb-3 me-3"></i>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
                <div class="promo-section">
                    <div class="promo-card">
                        <div class="promo-content">
                            <h3>Promo Spesial!</h3>
                            <p>Dapatkan diskon hingga 30% untuk penerbangan domestik</p>
                            <a href="#" class="btn btn-custom">Lihat Promo</a>
                        </div>
                        <div class="promo-image">
                            <i class="fas fa-gift floating-icon fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="notification-card">
                    <h4><i class="fas fa-bell me-2"></i>Notifikasi</h4>
                    <div class="notification-item">
                        <i class="fas fa-tag text-primary"></i>
                        <div class="notification-content">
                            <p>Promo Tahun Baru! Diskon 25%</p>
                            <small>1 jam yang lalu</small>
                        </div>
                    </div>
                    <div class="notification-item">
                        <i class="fas fa-plane-departure text-success"></i>
                        <div class="notification-content">
                            <p>Penerbangan baru tersedia</p>
                            <small>3 jam yang lalu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <?php
                // Fetch airlines from database
                $stmt = $pdo->query("SELECT * FROM maskapai");
                $airlines = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="airlines-card">
                    <h4>Partner Maskapai</h4>
                    <div class="airlines-grid">
                        <?php foreach ($airlines as $airline): ?>
                        <div class="airline-item">
                            <div class="airline-logo">
                                <img src="../<?php echo htmlspecialchars($airline['logo']); ?>" alt="<?php echo htmlspecialchars($airline['nama']); ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="travel-tips">
                    <h4>Tips Perjalanan</h4>
                    <div class="tips-list">
                        <div class="tip-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h5>Checkin Lebih Awal</h5>
                                <p>Datang 2 jam sebelum keberangkatan</p>
                            </div>
                        </div>
                        <div class="tip-item">
                            <i class="fas fa-passport"></i>
                            <div>
                                <h5>Dokumen Lengkap</h5>
                                <p>Siapkan ID dan dokumen perjalanan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <h5>Tentang Kami</h5>
                        <p>Airline Booking adalah platform pemesanan tiket pesawat terpercaya yang menyediakan berbagai pilihan penerbangan dengan harga terbaik.</p>
                    </div>
                    <div class="col-md-4 mb-4">
                        <h5>Link Cepat</h5>
                        <ul class="list-unstyled">
                            <li><a href="tiket.php" class="text-white">Pesan Tiket</a></li>
                            <li><a href="my_bookings.php" class="text-white">Pesanan Saya</a></li>
                            <li><a href="profile.php" class="text-white">Profil</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 mb-4">
                        <h5>Hubungi Kami</h5>
                        <ul class="list-unstyled">
                            <li>Email: info@airlinebooking.com</li>
                            <li>Telepon: (021) 123-4567</li>
                            <li>Alamat: Jl. Penerbangan No. 123, Jakarta</li>
                        </ul>
                    </div>
                </div>
                <hr class="my-4" style="background-color: white;">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <p class="mb-0">&copy; 2023 Airline Booking. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
