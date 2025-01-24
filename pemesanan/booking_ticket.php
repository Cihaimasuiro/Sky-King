<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Silakan login terlebih dahulu untuk memesan tiket.";
    header("Location: login.php");
    exit();
}

// Jika tidak ada data POST yang dikirim, redirect ke halaman pencarian
if (!isset($_POST['flight_id']) || !isset($_POST['passengers'])) {
    header("Location: index.php");
    exit();
}

// Jika form konfirmasi disubmit
if (isset($_POST['confirm_booking'])) {
    // Proses konfirmasi pemesanan
    $flight_id = $_POST['flight_id'] ?? '';
    $passengers = $_POST['passengers'] ?? '';
    $total_price = $_POST['total_price'] ?? '';
    $user_id = $_SESSION['user_id'];

    // Validasi data penumpang
    $passenger_names = $_POST['passenger_name'] ?? [];
    $passenger_titles = $_POST['passenger_title'] ?? [];
    $passenger_nationalities = $_POST['passenger_nationality'] ?? [];
    $passenger_passports = $_POST['passenger_passport'] ?? [];

    if (empty($flight_id) || empty($passengers) || empty($total_price) || 
        count($passenger_names) != $passengers || 
        count($passenger_titles) != $passengers || 
        count($passenger_nationalities) != $passengers || 
        count($passenger_passports) != $passengers) {
        $_SESSION['error_message'] = "Data pemesanan tidak lengkap";
        header("Location: search_results.php");
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Create booking
        $stmt = $pdo->prepare("INSERT INTO booking (id_user, id_flight, booking_date, passenger_count, total_price, status) 
                              VALUES (?, ?, NOW(), ?, ?, 'pending')");
        $stmt->execute([$user_id, $flight_id, $passengers, $total_price]);
        $booking_id = $pdo->lastInsertId();

        // Simpan data penumpang
        $stmt = $pdo->prepare("INSERT INTO passengers (booking_id, title, full_name, nationality, id_passport) 
                              VALUES (?, ?, ?, ?, ?)");
        
        for ($i = 0; $i < $passengers; $i++) {
            $stmt->execute([
                $booking_id,
                $passenger_titles[$i],
                $passenger_names[$i],
                $passenger_nationalities[$i],
                $passenger_passports[$i]
            ]);
        }

        $pdo->commit();

        // Simpan data booking ke session untuk digunakan di halaman pembayaran
        $_SESSION['booking_data'] = [
            'booking_id' => $booking_id,
            'total_price' => $total_price,
            'airline_name' => $_POST['airline_name'],
            'airline_code' => $_POST['airline_code'],
            'from_airport' => $_POST['from_airport'],
            'to_airport' => $_POST['to_airport'],
            'departure_time' => $_POST['departure_time'],
            'passenger_count' => $passengers
        ];
        
        header("Location: payment.php");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Gagal melakukan pemesanan: " . $e->getMessage();
        header("Location: search_results.php");
        exit();
    }
} elseif (isset($_POST['proceed_to_payment'])) {
    // Simpan data booking ke session untuk digunakan di halaman pembayaran
    $_SESSION['booking_data'] = [
        'flight_id' => $_POST['flight_id'],
        'passengers' => $_POST['passengers'],
        'total_price' => $_POST['total_price'],
        'airline_name' => $_POST['airline_name'],
        'airline_code' => $_POST['airline_code'],
        'from_airport' => $_POST['from_airport'],
        'to_airport' => $_POST['to_airport'],
        'departure_time' => $_POST['departure_time'],
        'arrival_time' => $_POST['arrival_time'],
        'class' => $_POST['class'],
        'passenger_names' => $_POST['passenger_name'],
        'passenger_titles' => $_POST['passenger_title'],
        'passenger_nationalities' => $_POST['passenger_nationality'],
        'passenger_passports' => $_POST['passenger_passport']
    ];
    
    header("Location: payment.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pemesanan - AirlineBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .navbar-brand i {
            margin-right: 0.5rem;
            color: var(--accent-color);
        }

        .booking-header {
            padding-top: 120px;
            background: var(--sunset-gradient);
            color: white;
            padding-bottom: 3rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .booking-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--sunset-overlay);
            z-index: 1;
        }

        .booking-header .container {
            position: relative;
            z-index: 2;
        }

        .booking-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }

        .flight-info {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-top: 2rem;
            color: white;
        }

        .flight-info i {
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .main-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .detail-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .detail-section h4 {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .flight-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 2rem 0;
        }

        .airport-info {
            text-align: center;
        }

        .airport-code {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .airport-name {
            color: #666;
            font-size: 0.9rem;
        }

        .flight-time {
            font-size: 1.2rem;
            font-weight: 500;
            color: var(--primary-color);
        }

        .price-details {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .price-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 1.5rem;
        }

        .total-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 1rem 2rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .booking-header {
                padding-top: 100px;
            }

            .booking-header h1 {
                font-size: 2rem;
            }

            .flight-route {
                flex-direction: column;
                gap: 2rem;
            }

            .detail-section, .price-details {
                padding: 1.5rem;
            }
        }

        /* Footer Styles */
        .footer {
            background: #222;
            color: #fff;
            padding: 4rem 0 2rem;
            margin-top: 4rem;
        }

        .footer h3 {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--primary-color);
        }

        .footer p {
            color: #ccc;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            line-height: 1.8;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: var(--primary-color);
            padding-left: 5px;
        }

        .footer-social {
            margin-top: 1.5rem;
        }

        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }

        .footer-social a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        .footer-contact {
            color: #ccc;
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }

        .footer-contact i {
            color: var(--primary-color);
            margin-right: 0.5rem;
            width: 20px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            color: #888;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-plane-departure"></i>
                AirlineBooking
            </a>
            <div class="ms-auto">
                <span class="text-white me-3">
                    Selamat datang, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Tamu'); ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light">Keluar</a>
            </div>
        </div>
    </nav>

    <!-- Booking Header -->
    <div class="booking-header">
        <div class="container">
            <h1>Konfirmasi Pemesanan</h1>
            <div class="flight-info">
                <div>
                    <i class="fas fa-plane-departure me-2"></i>
                    <?php echo htmlspecialchars($_POST['airline_name']); ?> (<?php echo htmlspecialchars($_POST['airline_code']); ?>)
                </div>
                <div>
                    <i class="fas fa-calendar-alt me-2"></i>
                    <?php echo date('d M Y', strtotime($_POST['departure_time'])); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <!-- Flight Details -->
            <div class="col-md-8">
                <div class="detail-section">
                    <h4>Detail Penerbangan</h4>
                    <div class="flight-route">
                        <div class="airport-info">
                            <div class="airport-code"><?php echo htmlspecialchars($_POST['from_airport']); ?></div>
                            <div class="flight-time"><?php echo date('H:i', strtotime($_POST['departure_time'])); ?> WIB</div>
                            <div class="airport-name"><?php echo htmlspecialchars($_POST['from_airport']); ?></div>
                        </div>
                        <div class="flight-duration">
                            <i class="fas fa-plane"></i>
                        </div>
                        <div class="airport-info">
                            <div class="airport-code"><?php echo htmlspecialchars($_POST['to_airport']); ?></div>
                            <div class="flight-time"><?php echo date('H:i', strtotime($_POST['arrival_time'])); ?> WIB</div>
                            <div class="airport-name"><?php echo htmlspecialchars($_POST['to_airport']); ?></div>
                        </div>
                    </div>
                    <div class="additional-info mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Kelas Kabin</h5>
                                <p><?php echo ucfirst(htmlspecialchars($_POST['class'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Jumlah Penumpang</h5>
                                <p><?php echo htmlspecialchars($_POST['passengers']); ?> orang</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Passenger Details -->
                <div class="detail-section">
                    <h4>Data Penumpang</h4>
                    <?php 
                    $passenger_names = $_POST['passenger_name'];
                    $passenger_titles = $_POST['passenger_title'];
                    for ($i = 0; $i < count($passenger_names); $i++) { 
                    ?>
                        <div class="mb-3">
                            <h5>Penumpang <?php echo $i + 1; ?></h5>
                            <p class="mb-1"><?php echo htmlspecialchars($passenger_titles[$i] . '. ' . $passenger_names[$i]); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Price Details -->
            <div class="col-md-4">
                <div class="price-details">
                    <h4>Detail Harga</h4>
                    <div class="price-row">
                        <span>Harga Tiket</span>
                        <span>Rp <?php echo number_format($_POST['total_price']/$_POST['passengers'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Jumlah Penumpang</span>
                        <span><?php echo htmlspecialchars($_POST['passengers']); ?> x</span>
                    </div>
                    <div class="price-row">
                        <span class="fw-bold">Total</span>
                        <span class="total-price">Rp <?php echo number_format($_POST['total_price'], 0, ',', '.'); ?></span>
                    </div>
                    <form method="POST">
                        <!-- Data penerbangan -->
                        <input type="hidden" name="flight_id" value="<?php echo htmlspecialchars($_POST['flight_id']); ?>">
                        <input type="hidden" name="passengers" value="<?php echo htmlspecialchars($_POST['passengers']); ?>">
                        <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($_POST['total_price']); ?>">
                        <input type="hidden" name="airline_name" value="<?php echo htmlspecialchars($_POST['airline_name']); ?>">
                        <input type="hidden" name="airline_code" value="<?php echo htmlspecialchars($_POST['airline_code']); ?>">
                        <input type="hidden" name="from_airport" value="<?php echo htmlspecialchars($_POST['from_airport']); ?>">
                        <input type="hidden" name="to_airport" value="<?php echo htmlspecialchars($_POST['to_airport']); ?>">
                        <input type="hidden" name="departure_time" value="<?php echo htmlspecialchars($_POST['departure_time']); ?>">
                        <input type="hidden" name="arrival_time" value="<?php echo htmlspecialchars($_POST['arrival_time']); ?>">
                        <input type="hidden" name="class" value="<?php echo htmlspecialchars($_POST['class']); ?>">
                        
                        <!-- Data penumpang -->
                        <?php foreach ($_POST['passenger_name'] as $i => $name) : ?>
                            <input type="hidden" name="passenger_name[]" value="<?php echo htmlspecialchars($name); ?>">
                            <input type="hidden" name="passenger_title[]" value="<?php echo htmlspecialchars($_POST['passenger_title'][$i]); ?>">
                            <input type="hidden" name="passenger_nationality[]" value="<?php echo htmlspecialchars($_POST['passenger_nationality'][$i]); ?>">
                            <input type="hidden" name="passenger_passport[]" value="<?php echo htmlspecialchars($_POST['passenger_passport'][$i]); ?>">
                        <?php endforeach; ?>
                        
                        <input type="hidden" name="proceed_to_payment" value="1">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-2"></i>
                            Lanjut ke Pembayaran
                        </button>
                    </form>

                    <script>
                    document.querySelector('form').addEventListener('submit', function(e) {
                        // Disable the submit button to prevent double submission
                        this.querySelector('button[type="submit"]').disabled = true;
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- About Us -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h3>Tentang Kami</h3>
                    <p>AirlineBooking adalah platform pemesanan tiket pesawat terpercaya dengan berbagai pilihan maskapai dan rute penerbangan terbaik untuk perjalanan Anda.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Services -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h3>Layanan</h3>
                    <ul class="footer-links">
                        <li><a href="#">Pemesanan Tiket</a></li>
                        <li><a href="#">Cek Status Penerbangan</a></li>
                        <li><a href="#">Program Loyalitas</a></li>
                        <li><a href="#">Paket Wisata</a></li>
                    </ul>
                </div>

                <!-- Information -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h3>Informasi</h3>
                    <ul class="footer-links">
                        <li><a href="#">Syarat & Ketentuan</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Hubungi Kami</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="col-lg-3 col-md-6">
                    <h3>Kontak</h3>
                    <div class="footer-contact">
                        <p><i class="fas fa-phone"></i> +62 123 4567 890</p>
                        <p><i class="fas fa-envelope"></i> info@airlinebooking.com</p>
                        <p><i class="fas fa-map-marker-alt"></i> Jl. Pesawat No. 123, Jakarta</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; 2024 AirlineBooking. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
