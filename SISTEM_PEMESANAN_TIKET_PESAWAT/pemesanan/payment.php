<?php
session_start();

require_once '../config/database.php';

error_log("=== Payment.php Debug Start ===");
error_log("POST Data: " . print_r($_POST, true));
error_log("Session Data: " . print_r($_SESSION, true));

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if we have booking data in session
if (!isset($_SESSION['booking_data'])) {
    $_SESSION['error_message'] = "Data pemesanan tidak ditemukan. Silakan ulangi proses pemesanan.";
    header("Location: index.php");
    exit();
}

// Get booking data from session
$booking_data = $_SESSION['booking_data'];

// Calculate total price and tax
$total_price = $booking_data['total_price'];
$tax = $total_price * 0.1;
$total_with_tax = $total_price + $tax;

// Jika form pembayaran disubmit
if (isset($_POST['process_payment'])) {
    try {
        error_log("Processing payment...");
        
        // Validate payment method
        if (empty($_POST['payment_method'])) {
            throw new Exception("Silakan pilih metode pembayaran.");
        }

        $pdo->beginTransaction();
        error_log("Transaction started");
        
        // Insert ke tabel booking
        $stmt = $pdo->prepare("INSERT INTO booking (id_user, id_flight, booking_date, passenger_count, total_price, status, payment_method) 
                              VALUES (?, ?, NOW(), ?, ?, 'CONFIRMED', ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $booking_data['flight_id'],
            $booking_data['passengers'],
            $total_with_tax,
            $_POST['payment_method']
        ]);
        
        $booking_id = $pdo->lastInsertId();
        error_log("Booking created with ID: " . $booking_id);
        
        // Insert passenger data
        $stmt = $pdo->prepare("INSERT INTO passengers (booking_id, title, full_name, nationality, id_passport) 
                              VALUES (?, ?, ?, ?, ?)");
                              
        for ($i = 0; $i < count($booking_data['passenger_names']); $i++) {
            $stmt->execute([
                $booking_id,
                $booking_data['passenger_titles'][$i],
                $booking_data['passenger_names'][$i],
                $booking_data['passenger_nationalities'][$i],
                $booking_data['passenger_passports'][$i]
            ]);
        }
        
        // Insert ke tabel payment
        $stmt = $pdo->prepare("INSERT INTO payment (booking_id, amount, payment_method, payment_date, status) 
                              VALUES (?, ?, ?, NOW(), 'SUCCESS')");
        $payment_params = [
            $booking_id,
            $total_with_tax,
            $_POST['payment_method']
        ];
        error_log("Executing payment insert with params: " . print_r($payment_params, true));
        $stmt->execute($payment_params);
        
        $pdo->commit();
        error_log("Transaction committed successfully");
        
        // Clear booking data from session
        unset($_SESSION['booking_data']);
        
        // Set success message and redirect
        $_SESSION['success_message'] = "Pembayaran berhasil! Booking ID: " . $booking_id;
        header("Location: booking_confirmation.php?booking_id=" . $booking_id);
        exit();
        
    } catch(Exception $e) {
        error_log("Payment Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            error_log("Transaction rolled back");
        }
        
        $_SESSION['error_message'] = "Gagal memproses pembayaran: " . $e->getMessage();
        header("Location: payment.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - AirlineBooking</title>
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

        .navbar {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .navbar-brand img {
            height: 30px;
        }

        .welcome-text {
            color: white;
            font-size: 1rem;
        }

        .btn-keluar {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-keluar:hover {
            background: white;
            color: var(--primary-color);
        }

        .payment-header {
            padding-top: 120px;
            background: var(--sunset-gradient);
            color: white;
            padding-bottom: 3rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .payment-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(27, 75, 114, 0.95), 
                rgba(44, 95, 143, 0.85),
                rgba(255, 107, 53, 0.85),
                rgba(255, 181, 102, 0.95)
            );
            z-index: 1;
        }

        .payment-header .container {
            position: relative;
            z-index: 2;
        }

        .payment-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }

        .payment-info {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-top: 2rem;
            color: white;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .payment-info i {
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .payment-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: var(--primary-color);
            background-color: #fff8f6;
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background-color: #fff8f6;
        }

        .payment-method input[type="radio"] {
            display: none;
        }

        .payment-method label {
            display: flex;
            align-items: center;
            margin: 0;
            cursor: pointer;
        }

        .payment-method i {
            font-size: 2rem;
            margin-right: 1rem;
            color: var(--primary-color);
        }

        .payment-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
        }

        .payment-summary .total {
            font-size: 1.2rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .btn-pay {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-pay:hover {
            background: #ff824f;
            transform: translateY(-2px);
        }

        .footer {
            background: var(--secondary-color);
            color: white;
            padding: 3rem 0;
            margin-top: auto;
        }

        .footer h4 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .footer ul {
            list-style: none;
            padding: 0;
        }

        .footer ul li {
            margin-bottom: 0.5rem;
        }

        .footer ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer ul li a:hover {
            color: var(--primary-color);
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            color: white;
            font-size: 1.5rem;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: var(--primary-color);
        }

        .text-orange {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center w-100">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-plane"></i>
                    AirlineBooking
                </a>
                <div class="d-flex align-items-center gap-3">
                    <span class="welcome-text">Selamat datang, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Tamu'; ?></span>
                    <a href="logout.php" class="btn-keluar">Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="payment-header">
        <div class="container">
            <h1>Pembayaran</h1>
            <p class="lead">Pilih metode pembayaran yang Anda inginkan</p>
            <div class="payment-info">
                <div class="payment-info-item">
                    <i class="fas fa-money-bill"></i>
                    <div class="payment-info-text">
                        Total Pembayaran: Rp <?php echo number_format($total_with_tax, 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="payment-container">
                    <h3 class="mb-4">Pilih Metode Pembayaran</h3>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php 
                            echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form id="paymentForm" method="POST" action="payment.php">
                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="transfer" value="transfer" required>
                            <label for="transfer">
                                <i class="fas fa-university"></i>
                                <div>
                                    <h5 class="mb-1">Transfer Bank</h5>
                                    <p class="mb-0 text-muted">Transfer melalui ATM atau mobile banking</p>
                                </div>
                            </label>
                        </div>

                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="credit" value="credit">
                            <label for="credit">
                                <i class="fas fa-credit-card"></i>
                                <div>
                                    <h5 class="mb-1">Kartu Kredit</h5>
                                    <p class="mb-0 text-muted">Bayar dengan Visa, Mastercard, atau JCB</p>
                                </div>
                            </label>
                        </div>

                        <div class="payment-method">
                            <input type="radio" name="payment_method" id="ewallet" value="ewallet">
                            <label for="ewallet">
                                <i class="fas fa-wallet"></i>
                                <div>
                                    <h5 class="mb-1">E-Wallet</h5>
                                    <p class="mb-0 text-muted">OVO, GoPay, DANA, atau LinkAja</p>
                                </div>
                            </label>
                        </div>

                        <button type="submit" name="process_payment" class="btn btn-pay w-100 mt-4">
                            Bayar Sekarang
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="payment-container">
                    <h3 class="mb-4">Ringkasan Pembayaran</h3>
                    <div class="payment-summary">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Harga</span>
                            <span>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Pajak (10%)</span>
                            <span>Rp <?php echo number_format($tax, 0, ',', '.'); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between total">
                            <span>Total Pembayaran</span>
                            <span>Rp <?php echo number_format($total_with_tax, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h4 class="text-orange mb-4">Tentang Kami</h4>
                    <p>AirlineBooking adalah platform pemesanan tiket pesawat terpercaya dengan berbagai pilihan maskapai dan rute penerbangan.</p>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h4 class="text-orange mb-4">Layanan</h4>
                    <ul>
                        <li><a href="#">Pemesanan Tiket</a></li>
                        <li><a href="#">Pembayaran Mudah</a></li>
                        <li><a href="#">Reschedule</a></li>
                        <li><a href="#">Refund</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h4 class="text-orange mb-4">Bantuan</h4>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                        <li><a href="#">Syarat & Ketentuan</a></li>
                        <li><a href="#">Hubungi Kami</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-orange mb-4">Ikuti Kami</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add selected class to payment method when selected
        const paymentMethods = document.querySelectorAll('.payment-method');
        paymentMethods.forEach(method => {
            const radio = method.querySelector('input[type="radio"]');
            method.addEventListener('click', () => {
                paymentMethods.forEach(m => m.classList.remove('selected'));
                method.classList.add('selected');
                radio.checked = true;
            });
        });

        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            if (!document.querySelector('input[name="payment_method"]:checked')) {
                e.preventDefault();
                alert('Silakan pilih metode pembayaran');
            }
        });
    </script>
</body>
</html>
