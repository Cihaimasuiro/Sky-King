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

// Simpan data penerbangan ke session untuk digunakan nanti
$_SESSION['flight_data'] = $_POST;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penumpang - AirlineBooking</title>
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
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .flight-info i {
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .flight-info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .flight-info-item i {
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .flight-info-text {
            font-size: 1.2rem;
            font-weight: 500;
        }

        .passenger-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .passenger-form h4 {
            color: var(--secondary-color);
            font-weight: 600;
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        .passenger-form h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 1rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .footer {
            background: #222;
            color: #fff;
            margin-top: auto;
        }
        .text-orange {
            color: #FF6B35;
        }
        .social-links a {
            color: #fff;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        .social-links a:hover {
            color: #FF6B35;
        }
        .list-unstyled li a:hover {
            color: #FF6B35 !important;
        }

        @media (max-width: 768px) {
            .booking-header {
                padding-top: 100px;
            }

            .booking-header h1 {
                font-size: 2rem;
            }

            .flight-info {
                flex-direction: column;
                gap: 1rem;
                padding: 1.5rem;
            }
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
            <h1>Data Penumpang</h1>
            <div class="flight-info">
                <div class="flight-info-item">
                    <i class="fas fa-plane-departure"></i>
                    <div class="flight-info-text">
                        <?php echo htmlspecialchars($_POST['airline_name']); ?> (<?php echo htmlspecialchars($_POST['airline_code']); ?>)
                    </div>
                </div>
                <div class="flight-info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <div class="flight-info-text">
                        <?php echo date('d M Y', strtotime($_POST['departure_time'])); ?>
                    </div>
                </div>
                <div class="flight-info-item">
                    <i class="fas fa-users"></i>
                    <div class="flight-info-text">
                        <?php echo htmlspecialchars($_POST['passengers']); ?> Orang
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <form method="POST" action="booking_ticket.php">
            <?php
            $num_passengers = intval($_POST['passengers']);
            for ($i = 0; $i < $num_passengers; $i++) {
            ?>
                <div class="passenger-form">
                    <h4>Penumpang <?php echo $i + 1; ?></h4>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="title_<?php echo $i; ?>" class="form-label">Title</label>
                            <select class="form-select" name="passenger_title[]" id="title_<?php echo $i; ?>" required>
                                <option value="">Pilih Title</option>
                                <option value="Mr">Mr</option>
                                <option value="Mrs">Mrs</option>
                                <option value="Ms">Ms</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="name_<?php echo $i; ?>" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="passenger_name[]" id="name_<?php echo $i; ?>" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="nationality_<?php echo $i; ?>" class="form-label">Kewarganegaraan</label>
                            <select class="form-select" name="passenger_nationality[]" id="nationality_<?php echo $i; ?>" required>
                                <option value="">Pilih Negara</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Singapura">Singapura</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Vietnam">Vietnam</option>
                                <option value="Filipina">Filipina</option>
                                <option value="Brunei">Brunei</option>
                                <option value="Kamboja">Kamboja</option>
                                <option value="Laos">Laos</option>
                                <option value="Myanmar">Myanmar</option>
                                <option value="Tiongkok">Tiongkok</option>
                                <option value="Jepang">Jepang</option>
                                <option value="Korea Selatan">Korea Selatan</option>
                                <option value="Amerika Serikat">Amerika Serikat</option>
                                <option value="Inggris">Inggris</option>
                                <option value="Jerman">Jerman</option>
                                <option value="Belanda">Belanda</option>
                                <option value="Prancis">Prancis</option>
                                <option value="Italia">Italia</option>
                                <option value="Spanyol">Spanyol</option>
                                <option value="Australia">Australia</option>
                                <option value="Selandia Baru">Selandia Baru</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="passport_<?php echo $i; ?>" class="form-label">Nomor KTP/Paspor</label>
                            <input type="text" class="form-control" name="passenger_passport[]" id="passport_<?php echo $i; ?>" required>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php foreach ($_SESSION['flight_data'] as $key => $value) { ?>
                <?php if (is_array($value)) { continue; } ?>
                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
            <?php } ?>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-check me-2"></i>Lanjut ke Pembayaran
                </button>
            </div>
        </form>
    </div>

    <footer class="footer">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h4 class="text-orange mb-4">Tentang Kami</h4>
                    <p class="text-light">AirlineBooking adalah platform pemesanan tiket pesawat terpercaya dengan berbagai pilihan maskapai dan rute penerbangan terbaik untuk perjalanan Anda.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h4 class="text-orange mb-4">Layanan</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Pemesanan Tiket</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Cek Status Penerbangan</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Program Loyalitas</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Paket Wisata</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h4 class="text-orange mb-4">Informasi</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Syarat & Ketentuan</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Kebijakan Privasi</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="#" class="text-light text-decoration-none">Hubungi Kami</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h4 class="text-orange mb-4">Kontak</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2 text-light"><i class="fas fa-phone me-2"></i>+62 123 4567 890</li>
                        <li class="mb-2 text-light"><i class="fas fa-envelope me-2"></i>info@airlinebooking.com</li>
                        <li class="text-light"><i class="fas fa-map-marker-alt me-2"></i>Jl. Pesawat No. 123, Jakarta</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="text-center py-3 border-top border-secondary">
            <p class="text-light mb-0">&copy; 2024 AirlineBooking. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
