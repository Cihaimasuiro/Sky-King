<?php
require_once '../config/database.php';


if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Silakan login terlebih dahulu";
    header("Location: login.php");
    exit();
}

if (!isset($_GET['booking_id'])) {
    header("Location: my_bookings.php");
    exit();
}

try {
    // Debug: Log parameters
    error_log("Searching for ticket - Booking ID: " . $_GET['booking_id'] . ", User ID: " . $_SESSION['user_id']);

    // Ambil detail booking dengan semua informasi terkait
    $query = "SELECT b.*, f.*, m.nama as airline_name, m.kode as airline_code,
                     a1.nama_airport as from_airport, a1.kode_airport as from_code,
                     a2.nama_airport as to_airport, a2.kode_airport as to_code,
                     p.full_name, p.id_passport, p.title, p.nationality
              FROM booking b
              JOIN flight f ON b.id_flight = f.id_flight
              JOIN maskapai m ON f.id_maskapai = m.id_maskapai
              JOIN airport a1 ON f.from_airport = a1.id_airport
              JOIN airport a2 ON f.to_airport = a2.id_airport
              JOIN passengers p ON b.id_booking = p.booking_id
              WHERE b.id_booking = ? AND b.id_user = ?";
              
    // Debug: Log query
    error_log("Query: " . $query);
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_GET['booking_id'], $_SESSION['user_id']]);
    $ticket_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Log result
    error_log("Query result count: " . count($ticket_data));
    if (!empty($ticket_data)) {
        error_log("First ticket data: " . print_r($ticket_data[0], true));
    }

    // Periksa status booking
    if (empty($ticket_data)) {
        // Debug: Check if booking exists at all
        $check_query = "SELECT status FROM booking WHERE id_booking = ?";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->execute([$_GET['booking_id']]);
        $booking_status = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($booking_status) {
            error_log("Booking exists with status: " . $booking_status['status']);
            if ($booking_status['status'] !== 'completed') {
                throw new Exception("Pembayaran belum selesai. Status: " . $booking_status['status']);
            }
        } else {
            error_log("No booking found with ID: " . $_GET['booking_id']);
            throw new Exception("Tiket tidak ditemukan");
        }
    }

} catch(Exception $e) {
    error_log("Ticket Error: " . $e->getMessage());
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: my_bookings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket - AirlineBooking</title>
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

        .ticket-header {
            padding-top: 120px;
            padding-bottom: 3rem;
            background: var(--sunset-gradient);
            color: white;
            margin-bottom: 2rem;
            position: relative;
        }

        .ticket-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .ticket-container {
            padding: 2rem 0;
        }

        .ticket {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            margin-bottom: 3rem;
            overflow: hidden;
            position: relative;
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .ticket:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.15);
        }

        .ticket::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--sunset-gradient);
        }

        .ticket-airline {
            padding: 2.5rem;
            background: linear-gradient(135deg, rgba(27, 75, 114, 0.03), rgba(255, 107, 53, 0.03));
            border-bottom: 2px dashed rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .ticket-airline::after {
            content: '';
            position: absolute;
            left: -15px;
            bottom: -15px;
            width: 30px;
            height: 30px;
            background: var(--sunset-gradient);
            border-radius: 50%;
            box-shadow: 
                30px 0 0 var(--sunset-gradient),
                60px 0 0 var(--sunset-gradient),
                90px 0 0 var(--sunset-gradient),
                120px 0 0 var(--sunset-gradient),
                150px 0 0 var(--sunset-gradient),
                180px 0 0 var(--sunset-gradient),
                210px 0 0 var(--sunset-gradient),
                240px 0 0 var(--sunset-gradient),
                270px 0 0 var(--sunset-gradient),
                300px 0 0 var(--sunset-gradient),
                330px 0 0 var(--sunset-gradient),
                360px 0 0 var(--sunset-gradient),
                390px 0 0 var(--sunset-gradient),
                420px 0 0 var(--sunset-gradient),
                450px 0 0 var(--sunset-gradient),
                480px 0 0 var(--sunset-gradient),
                510px 0 0 var(--sunset-gradient),
                540px 0 0 var(--sunset-gradient),
                570px 0 0 var(--sunset-gradient),
                600px 0 0 var(--sunset-gradient);
        }

        .airline-logo {
            width: 70px;
            height: 70px;
            background: var(--sunset-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.2);
            position: relative;
            overflow: hidden;
        }

        .airline-logo::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 40%, rgba(255, 255, 255, 0.2) 50%, transparent 60%);
            transform: translateX(-100%);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            20% { transform: translateX(100%); }
            100% { transform: translateX(100%); }
        }

        .flight-info {
            padding: 3rem 2.5rem;
            position: relative;
            background: linear-gradient(135deg, rgba(27, 75, 114, 0.02), rgba(255, 107, 53, 0.02));
        }

        .flight-route {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 3rem;
            position: relative;
        }

        .flight-city {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .flight-city h3 {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin: 0;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .flight-city p {
            color: #666;
            margin: 0.5rem 0 0;
            font-size: 1rem;
        }

        .flight-line {
            flex: 1;
            height: 3px;
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            position: relative;
            opacity: 0.5;
        }

        .flight-line::before,
        .flight-line::after {
            content: '';
            position: absolute;
            width: 15px;
            height: 15px;
            background: var(--primary-color);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            box-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
        }

        .flight-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--primary-color);
            font-size: 2rem;
            animation: fly 3s infinite;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.2));
        }

        @keyframes fly {
            0% { transform: translate(-50%, -50%) translateX(-10px); }
            50% { transform: translate(-50%, -50%) translateX(10px); }
            100% { transform: translate(-50%, -50%) translateX(-10px); }
        }

        .passenger-info {
            padding: 2.5rem;
            background: linear-gradient(135deg, #f8f9fa, #fff);
            position: relative;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .info-item {
            padding: 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .info-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .info-item h5 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-item p {
            color: var(--secondary-color);
            font-weight: 600;
            margin: 0;
            font-size: 1.1rem;
        }

        .barcode {
            text-align: center;
            padding: 2.5rem;
            background: white;
            border-top: 2px dashed rgba(0,0,0,0.1);
            position: relative;
        }

        .barcode img {
            max-width: 300px;
            height: auto;
            filter: drop-shadow(0 5px 10px rgba(0,0,0,0.1));
            transition: all 0.3s ease;
        }

        .barcode img:hover {
            transform: scale(1.05);
        }

        .barcode::before {
            content: 'Scan for details';
            position: absolute;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .btn-print {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-print:hover {
            background: #ff824f;
            transform: translateY(-2px);
        }

        .btn-back {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-bottom: 1.5rem;
        }

        @media print {
            .no-print {
                display: none;
            }

            .ticket {
                break-inside: avoid;
                box-shadow: none !important;
                border: 1px solid #ddd;
                margin: 0;
                padding: 0;
            }

            body {
                background: none !important;
                color: black !important;
            }

            .navbar, .footer {
                display: none !important;
            }

            .ticket-header {
                background: none !important;
                padding-top: 20px !important;
                color: black !important;
            }

            .ticket::before,
            .ticket-airline::after {
                display: none !important;
            }

            .airline-logo {
                background: #f8f9fa !important;
                border: 1px solid #ddd !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .flight-line {
                border-top: 2px solid #333 !important;
                background: none !important;
            }

            .flight-icon {
                color: #333 !important;
            }

            .info-item {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }

            .barcode {
                padding: 20px !important;
            }

            .barcode img {
                max-width: 100% !important;
                height: auto !important;
            }

            * {
                print-color-adjust: exact !important;
                -webkit-print-color-adjust: exact !important;
            }

            @page {
                margin: 0.5cm;
                size: auto;
            }
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

    <div class="ticket-header">
        <div class="container">
            <h1>E-Ticket Anda</h1>
            <p class="lead">Terima kasih telah memilih AirlineBooking</p>
        </div>
    </div>

    <div class="container ticket-container">
        <div class="action-buttons no-print">
            <a href="../pemesanan/my_bookings.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Riwayat
            </a>
            <button id="printButton" class="btn btn-print">
                <i class="fas fa-print me-2"></i>Cetak E-Ticket
            </button>
        </div>

        <div id="printArea">
            <?php foreach ($ticket_data as $passenger): ?>
            <div class="ticket">
                <div class="ticket-airline">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="airline-logo">
                                <i class="fas fa-plane"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h3 class="mb-1" style="font-size: 2rem; color: var(--secondary-color);">
                                <?php echo $passenger['airline_name']; ?>
                            </h3>
                            <p class="mb-0" style="font-size: 1.1rem; color: var(--primary-color);">
                                <i class="fas fa-ticket-alt me-2"></i>
                                Kode Penerbangan: <?php echo $passenger['airline_code']; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flight-info">
                    <div class="flight-route">
                        <div class="flight-city">
                            <h3><?php echo $passenger['from_code']; ?></h3>
                            <p><?php echo $passenger['from_airport']; ?></p>
                        </div>
                        
                        <div class="flight-line">
                            <i class="fas fa-plane flight-icon"></i>
                        </div>
                        
                        <div class="flight-city">
                            <h3><?php echo $passenger['to_code']; ?></h3>
                            <p><?php echo $passenger['to_airport']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="passenger-info">
                    <div class="info-grid">
                        <div class="info-item">
                            <h5>Nama Penumpang</h5>
                            <p><?php echo $passenger['title'] . '. ' . $passenger['full_name']; ?></p>
                        </div>
                        <div class="info-item">
                            <h5>Nomor Passport</h5>
                            <p><?php echo $passenger['id_passport']; ?></p>
                        </div>
                        <div class="info-item">
                            <h5>Tanggal Keberangkatan</h5>
                            <p><?php echo date('d M Y', strtotime($passenger['arrival_time'])); ?></p>
                        </div>
                        <div class="info-item">
                            <h5>Waktu Keberangkatan</h5>
                            <p><?php echo date('H:i', strtotime($passenger['departure_time'])); ?> WIB</p>
                        </div>
                        <div class="info-item">
                            <h5>Kewarganegaraan</h5>
                            <p><?php echo $passenger['nationality']; ?></p>
                        </div>
                        <div class="info-item">
                            <h5>Nomor Booking</h5>
                            <p>#<?php echo str_pad($passenger['id_booking'], 6, '0', STR_PAD_LEFT); ?></p>
                        </div>
                    </div>
                </div>

                <div class="barcode">
                    <!-- Menggunakan QR Code sebagai alternatif -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo 'BOOKING-' . $passenger['id_booking'] . '-' . $passenger['airline_code']; ?>" 
                         alt="QR Code" 
                         style="max-width: 200px; height: auto;"
                         class="print-image">
                    <p class="mt-2" style="color: #666; font-size: 0.9rem;">Booking ID: #<?php echo str_pad($passenger['id_booking'], 6, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
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
        document.getElementById('printButton').addEventListener('click', function() {
            const printArea = document.getElementById('printArea');
            const originalContents = document.body.innerHTML;

            // Tunggu semua gambar dimuat
            const images = printArea.getElementsByTagName('img');
            let loadedImages = 0;
            
            function tryPrint() {
                loadedImages++;
                if (loadedImages === images.length) {
                    // Semua gambar sudah dimuat
                    const printContents = printArea.innerHTML;
                    
                    document.body.innerHTML = `
                        <style>
                            @media print {
                                body {
                                    padding: 0;
                                    margin: 0;
                                }
                                img {
                                    display: block !important;
                                    visibility: visible !important;
                                    print-color-adjust: exact;
                                    -webkit-print-color-adjust: exact;
                                }
                                .ticket {
                                    page-break-inside: avoid;
                                    margin: 0;
                                    padding: 20px;
                                }
                                .flight-info, .passenger-info, .barcode {
                                    display: block !important;
                                }
                            }
                        </style>
                        <div class="print-content">${printContents}</div>
                    `;
                    
                    window.print();
                    document.body.innerHTML = originalContents;
                    
                    // Rebind event listener setelah konten dikembalikan
                    document.getElementById('printButton').addEventListener('click', arguments.callee);
                }
            }

            // Jika tidak ada gambar, langsung cetak
            if (images.length === 0) {
                tryPrint();
            } else {
                // Cek setiap gambar
                for (let img of images) {
                    if (img.complete) {
                        tryPrint();
                    } else {
                        img.addEventListener('load', tryPrint);
                        img.addEventListener('error', tryPrint); // Jika gambar gagal dimuat
                    }
                }
            }
        });
    </script>
</body>
</html>
