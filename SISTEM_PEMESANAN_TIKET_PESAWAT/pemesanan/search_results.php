<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Silakan login terlebih dahulu untuk memesan tiket.";
    header("Location: login.php");
    exit();
}

$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$date = $_GET['date'] ?? '';
$passengers = $_GET['passengers'] ?? 1;
$class = $_GET['class'] ?? 'economy';

try {
    // First, get the airport codes for the cities
    $airport_query = "SELECT * FROM airport WHERE kota IN (:from, :to)";
    $airport_stmt = $pdo->prepare($airport_query);
    $airport_stmt->execute([':from' => $from, ':to' => $to]);
    $airports = $airport_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($airports) < 2) {
        $_SESSION['error'] = "Debug: Airport not found. Searching for cities: From=$from, To=$to";
        $flights = [];
    } else {
        // Get the airport codes for the cities
        $from_airport = '';
        $to_airport = '';
        foreach ($airports as $airport) {
            if ($airport['kota'] == $from) {
                $from_airport = $airport['kode_airport'];
            }
            if ($airport['kota'] == $to) {
                $to_airport = $airport['kode_airport'];
            }
        }

        $query = "SELECT f.*, m.nama as nama_maskapai, m.kode as kode_maskapai, m.logo as logo,
                         a1.kode_airport as from_code, a1.nama_airport as from_name,
                         a2.kode_airport as to_code, a2.nama_airport as to_name
                  FROM flight f
                  JOIN maskapai m ON f.id_maskapai = m.id_maskapai
                  JOIN airport a1 ON f.from_airport = a1.id_airport
                  JOIN airport a2 ON f.to_airport = a2.id_airport
                  WHERE a1.kode_airport = :from 
                  AND a2.kode_airport = :to
                  AND DATE(f.departure_time) = :date
                  AND f.kelas = :class
                  AND f.kapasitas >= :passengers";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':from' => $from_airport,
            ':to' => $to_airport,
            ':date' => $date,
            ':class' => $class,
            ':passengers' => $passengers
        ]);
        
        $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($flights)) {
            // Check flights without date and class constraints
            $basic_query = "SELECT COUNT(*) as count FROM flight f
                           JOIN airport a1 ON f.from_airport = a1.id_airport
                           JOIN airport a2 ON f.to_airport = a2.id_airport
                           WHERE a1.kode_airport = :from AND a2.kode_airport = :to";
            $basic_stmt = $pdo->prepare($basic_query);
            $basic_stmt->execute([':from' => $from_airport, ':to' => $to_airport]);
            $route_exists = $basic_stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($route_exists > 0) {
                $_SESSION['error'] = "Debug: Route exists but no matches found with date=$date, class=$class, passengers=$passengers";
            } else {
                $_SESSION['error'] = "Debug: No flights found for route $from to $to";
            }
        }
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
    $flights = [];
}

// Add database connection check
if (!isset($pdo)) {
    $_SESSION['error'] = "Database connection not established";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - AirlineBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF6B35;
            --secondary-color: #004E89;
            --accent-color: #FFB566;
            --sunset-gradient: linear-gradient(135deg, #004E89, #2C5F8F, #FF6B35, #FFB566);
            --sunset-overlay: linear-gradient(135deg, 
                rgba(0, 78, 137, 0.95), 
                rgba(44, 95, 143, 0.85),
                rgba(255, 107, 53, 0.85),
                rgba(255, 181, 102, 0.95));
            --text-color: #333;
            --light-text: #fff;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
        }

        .navbar {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
            color: var(--light-text);
            text-decoration: none;
            -webkit-background-clip: text;
            background: linear-gradient(135deg, #fff, #FFB566);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
        }

        .navbar-brand i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }

        .search-header {
            background: var(--sunset-overlay);
            padding: 3rem 0;
            color: var(--light-text);
            margin-bottom: 3rem;
            backdrop-filter: blur(10px);
        }

        .search-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .search-details {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .search-detail-item {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem 2rem;
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }

        .search-detail-item i {
            color: var(--accent-color);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .search-detail-item .label {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 0.3rem;
        }

        .search-detail-item .value {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .flight-results {
            padding-top: 120px;
            background: var(--sunset-gradient);
            color: white;
            padding-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .flight-results::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--sunset-overlay);
            z-index: 1;
        }

        .flight-results .container {
            position: relative;
            z-index: 2;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .flight-results {
            padding: 2rem 0;
        }

        .flight-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
            padding: 2rem;
        }

        .flight-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .airline-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-right: 1.5rem;
        }

        .flight-route {
            position: relative;
            padding: 1.5rem 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .flight-route .fa-plane {
            color: var(--primary-color);
            font-size: 2rem;
            margin: 0 2rem;
        }

        .airport-code {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .airport-name {
            font-size: 1rem;
            color: var(--text-color);
            opacity: 0.8;
        }

        .flight-details {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .search-header {
                padding: 2rem 0;
            }

            .search-header h2 {
                font-size: 2rem;
            }

            .flight-card {
                padding: 1.5rem;
                margin: 1rem;
            }

            .flight-route {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .flight-route .fa-plane {
                transform: rotate(90deg);
                margin: 1rem 0;
            }

            .price {
                font-size: 1.8rem;
                text-align: center;
            }
        }

        /* Footer Styles */
        footer {
            background: linear-gradient(to bottom, #1a1a1a, #2d2d2d);
            color: var(--light-text);
            padding: 4rem 0 2rem;
            margin-top: auto;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 3rem;
        }

        .footer-section h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -8px;
            width: 30px;
            height: 2px;
            background: var(--primary-color);
        }

        .footer-section p {
            color: #ccc;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .social-links {
            display: flex;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .social-links a {
            background: rgba(255, 255, 255, 0.1);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 1rem;
        }

        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .footer-links a:hover {
            color: var(--primary-color);
            padding-left: 5px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 3rem;
            margin-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #888;
            font-size: 0.9rem;
        }

        @media (max-width: 992px) {
            .footer-content {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }
        }

        @media (max-width: 576px) {
            .footer-content {
                grid-template-columns: 1fr;
            }
            
            .footer-section {
                text-align: center;
            }

            .footer-section h3::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .social-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-plane-departure"></i>AirlineBooking
            </a>
            <div class="ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn btn-outline-light">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Search Results Header -->
    <div class="search-header text-center">
        <div class="container">
            <h2>Hasil Pencarian Penerbangan</h2>
            <p>Menampilkan penerbangan dari <?php echo htmlspecialchars($from); ?> ke <?php echo htmlspecialchars($to); ?></p>
            
            <div class="search-details">
                <div class="search-detail-item">
                    <i class="fas fa-calendar-alt"></i>
                    <div class="label">Tanggal Keberangkatan</div>
                    <div class="value"><?php echo date('d F Y', strtotime($date)); ?></div>
                </div>
                <div class="search-detail-item">
                    <i class="fas fa-users"></i>
                    <div class="label">Jumlah Penumpang</div>
                    <div class="value"><?php echo $passengers; ?> Orang</div>
                </div>
                <div class="search-detail-item">
                    <i class="fas fa-chair"></i>
                    <div class="label">Kelas</div>
                    <div class="value"><?php echo ucfirst($class); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flight Results -->
    <div class="flight-results">
        <div class="container">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($flights)): ?>
                <div class="alert alert-info">
                    Tidak ada penerbangan yang tersedia untuk pencarian Anda.
                    <br>
                    <a href="index.php" class="btn btn-primary mt-3">Cari Penerbangan Lain</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($flights as $flight): ?>
                        <div class="col-12">
                            <div class="card flight-card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <img src="..\<?php echo htmlspecialchars($flight['logo']); ?>"
                                                     alt="<?php echo htmlspecialchars($flight['nama_maskapai']); ?>"
                                                     class="airline-logo">
                                                <div>
                                                    <h5 class="mb-0"><?php echo htmlspecialchars($flight['nama_maskapai']); ?></h5>
                                                    <small class="text-muted"><?php echo htmlspecialchars($flight['kode_maskapai']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="flight-route text-center">
                                                <div class="row">
                                                    <div class="col-5">
                                                        <div class="airport-code"><?php echo htmlspecialchars($flight['from_code']); ?></div>
                                                        <div class="airport-name"><?php echo htmlspecialchars($flight['from_name']); ?></div>
                                                        <div class="departure-time">
                                                            <?php echo date('H:i', strtotime($flight['departure_time'])); ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-2">
                                                        <i class="fas fa-plane"></i>
                                                    </div>
                                                    <div class="col-5">
                                                        <div class="airport-code"><?php echo htmlspecialchars($flight['to_code']); ?></div>
                                                        <div class="airport-name"><?php echo htmlspecialchars($flight['to_name']); ?></div>
                                                        <div class="arrival-time">
                                                            <?php echo date('H:i', strtotime($flight['arrival_time'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="price mb-3">
                                                <small class="text-muted">Mulai dari</small><br>
                                                Rp <?php echo number_format($flight['harga'], 0, ',', '.'); ?>
                                            </div>
                                            <form action="passenger_details.php" method="POST">
                                                <input type="hidden" name="flight_id" value="<?php echo $flight['id_flight']; ?>">
                                                <input type="hidden" name="passengers" value="<?php echo $passengers; ?>">
                                                <input type="hidden" name="total_price" value="<?php echo $flight['harga'] * $passengers; ?>">
                                                <input type="hidden" name="departure_time" value="<?php echo $flight['departure_time']; ?>">
                                                <input type="hidden" name="arrival_time" value="<?php echo $flight['arrival_time']; ?>">
                                                <input type="hidden" name="airline_name" value="<?php echo $flight['nama_maskapai']; ?>">
                                                <input type="hidden" name="airline_code" value="<?php echo $flight['kode_maskapai']; ?>">
                                                <input type="hidden" name="from_airport" value="<?php echo $flight['from_code']; ?>">
                                                <input type="hidden" name="to_airport" value="<?php echo $flight['to_code']; ?>">
                                                <input type="hidden" name="class" value="<?php echo $flight['kelas']; ?>">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="fas fa-ticket-alt me-2"></i>Pilih
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Tentang Kami</h3>
                <p>AirlineBooking adalah platform pemesanan tiket pesawat terpercaya dengan berbagai pilihan maskapai dan rute penerbangan terbaik untuk perjalanan Anda.</p>
                <div class="social-links">
                    <a href="#" class="social-circle"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-circle"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-circle"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-circle"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Layanan</h3>
                <ul class="footer-links">
                    <li><a href="#">Pemesanan Tiket</a></li>
                    <li><a href="#">Cek Status Penerbangan</a></li>
                    <li><a href="#">Program Loyalitas</a></li>
                    <li><a href="#">Paket Wisata</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Informasi</h3>
                <ul class="footer-links">
                    <li><a href="#">Syarat & Ketentuan</a></li>
                    <li><a href="#">Kebijakan Privasi</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Hubungi Kami</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Kontak</h3>
                <p><i class="fas fa-phone"></i> +62 123 4567 890</p>
                <p><i class="fas fa-envelope"></i> info@airlinebooking.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Jl. Pesawat No. 123, Jakarta</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 AirlineBooking. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
