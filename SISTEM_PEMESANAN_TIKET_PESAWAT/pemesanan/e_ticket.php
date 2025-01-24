<?php
require_once '../config/database.php';
session_start();

if (!isset($_GET['booking_id']) && !isset($_SESSION['booking_id'])) {
    $_SESSION['error_message'] = "ID Pemesanan tidak valid";
    header("Location: my_bookings.php");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : $_SESSION['booking_id'];

try {
    // Ambil data booking dari database
    $stmt = $pdo->prepare("
        SELECT b.*, f.*, m.nama as nama_maskapai, m.kode as kode_maskapai,
               a1.nama_airport as from_airport, a1.kode_airport as from_code,
               a2.nama_airport as to_airport, a2.kode_airport as to_code,
               p.full_name, p.passenger_title, p.nationality, p.id_passport,
               py.payment_date, py.status as payment_status
        FROM booking b
        JOIN flight f ON b.id_flight = f.id_flight
        JOIN maskapai m ON f.id_maskapai = m.id_maskapai
        JOIN airport a1 ON f.from_airport = a1.id_airport
        JOIN airport a2 ON f.to_airport = a2.id_airport
        JOIN passengers p ON b.id_booking = p.booking_id
        LEFT JOIN payment py ON b.id_booking = py.booking_id
        WHERE b.id_booking = ? AND b.id_user = ?
    ");
    
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        throw new Exception("Data tiket tidak ditemukan");
    }

    if ($ticket['payment_status'] !== 'SUCCESS') {
        throw new Exception("Pembayaran belum selesai");
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Terjadi kesalahan saat memuat data tiket: " . $e->getMessage();
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .e-ticket {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .ticket-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .ticket-body {
            padding: 20px;
        }
        .flight-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 20px 0;
        }
        .airport-code {
            font-size: 2em;
            font-weight: bold;
        }
        .flight-path {
            flex-grow: 1;
            text-align: center;
            position: relative;
            margin: 0 20px;
        }
        .flight-path::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            border-top: 2px dashed #ccc;
        }
        .flight-path i {
            background: white;
            padding: 0 10px;
            position: relative;
            color: #1e3c72;
        }
        .passenger-info, .flight-details {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .e-ticket, .e-ticket * {
                visibility: visible;
            }
            .e-ticket {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="e-ticket">
            <div class="ticket-header text-center">
                <h2 class="mb-0">E-Ticket</h2>
                <p class="mb-0">Booking ID: <?php echo htmlspecialchars($ticket['id_booking']); ?></p>
            </div>
            
            <div class="ticket-body">
                <div class="airline-info d-flex justify-content-between align-items-center">
                    <div>
                        <h4><?php echo htmlspecialchars($ticket['nama_maskapai']); ?></h4>
                        <p class="text-muted mb-0">Flight <?php echo htmlspecialchars($ticket['kode_maskapai']); ?></p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0"><?php echo date('l, d F Y', strtotime($ticket['departure_time'])); ?></p>
                    </div>
                </div>

                <div class="flight-info">
                    <div class="text-center">
                        <div class="airport-code"><?php echo htmlspecialchars($ticket['from_code']); ?></div>
                        <div class="airport-name"><?php echo htmlspecialchars($ticket['from_airport']); ?></div>
                        <div class="time"><?php echo date('H:i', strtotime($ticket['departure_time'])); ?></div>
                    </div>
                    <div class="flight-path">
                        <i class="fas fa-plane"></i>
                    </div>
                    <div class="text-center">
                        <div class="airport-code"><?php echo htmlspecialchars($ticket['to_code']); ?></div>
                        <div class="airport-name"><?php echo htmlspecialchars($ticket['to_airport']); ?></div>
                        <div class="time"><?php echo date('H:i', strtotime($ticket['arrival_time'])); ?></div>
                    </div>
                </div>

                <div class="passenger-info">
                    <h5>Passenger Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($ticket['passenger_title'] . ' ' . $ticket['full_name']); ?></p>
                            <p><strong>Nationality:</strong> <?php echo htmlspecialchars($ticket['nationality']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Passport/ID:</strong> <?php echo htmlspecialchars($ticket['id_passport']); ?></p>
                            <p><strong>Class:</strong> <?php echo htmlspecialchars($ticket['kelas']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="flight-details">
                    <h5>Flight Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Gate:</strong> <?php echo rand(1, 20); ?></p>
                            <p><strong>Boarding Time:</strong> <?php echo date('H:i', strtotime('-30 minutes', strtotime($ticket['departure_time']))); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Seat:</strong> <?php echo chr(rand(65, 70)) . rand(1, 30); ?></p>
                            <p><strong>Terminal:</strong> <?php echo rand(1, 3); ?></p>
                        </div>
                    </div>
                </div>

                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode('BOOKING:' . $ticket['id_booking']); ?>" alt="QR Code">
                </div>

                <div class="text-center mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i>Print E-Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
