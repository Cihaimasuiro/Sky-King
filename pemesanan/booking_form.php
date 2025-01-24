<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['flight_id'])) {
    header("Location: index.php");
    exit();
}

$flight_id = $_GET['flight_id'];

try {
    // Get flight details
    $stmt = $pdo->prepare("
        SELECT f.*, m.nama_maskapai, m.kode_maskapai,
        a1.nama_airport as from_airport_name, a1.kode_airport as from_airport_code,
        a2.nama_airport as to_airport_name, a2.kode_airport as to_airport_code
        FROM flight f
        JOIN maskapai m ON f.id_maskapai = m.id_maskapai
        JOIN airport a1 ON f.from_airport = a1.id_airport
        JOIN airport a2 ON f.to_airport = a2.id_airport
        WHERE f.id_flight = ?
    ");
    $stmt->execute([$flight_id]);
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$flight) {
        throw new Exception("Penerbangan tidak ditemukan");
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: search_results.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pemesanan - AirlineBooking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5">
        <h2>Form Pemesanan Tiket</h2>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Detail Penerbangan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Maskapai:</strong> <?= htmlspecialchars($flight['nama_maskapai']) ?> (<?= htmlspecialchars($flight['kode_maskapai']) ?>)</p>
                        <p><strong>Dari:</strong> <?= htmlspecialchars($flight['from_airport_name']) ?> (<?= htmlspecialchars($flight['from_airport_code']) ?>)</p>
                        <p><strong>Ke:</strong> <?= htmlspecialchars($flight['to_airport_name']) ?> (<?= htmlspecialchars($flight['to_airport_code']) ?>)</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Tanggal Keberangkatan:</strong> <?= date('d F Y', strtotime($flight['departure_time'])) ?></p>
                        <p><strong>Waktu Keberangkatan:</strong> <?= date('H:i', strtotime($flight['departure_time'])) ?></p>
                        <p><strong>Waktu Kedatangan:</strong> <?= date('H:i', strtotime($flight['arrival_time'])) ?></p>
                        <p><strong>Kelas:</strong> <?= ucfirst(htmlspecialchars($flight['kelas'])) ?></p>
                        <p><strong>Harga per Tiket:</strong> Rp <?= number_format($flight['harga'], 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <form action="process_booking.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="flight_id" value="<?= $flight_id ?>">
            <input type="hidden" name="price_per_ticket" value="<?= $flight['harga'] ?>">
            
            <div class="mb-3">
                <label for="passenger_count" class="form-label">Jumlah Penumpang</label>
                <input type="number" class="form-control" id="passenger_count" name="passenger_count" min="1" max="<?= $flight['kapasitas'] ?>" value="1" required>
                <div class="invalid-feedback">
                    Silakan masukkan jumlah penumpang (minimal 1, maksimal <?= $flight['kapasitas'] ?>)
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Total Harga</label>
                <div class="form-control-plaintext">
                    Rp <span id="total_price_display">0</span>
                    <input type="hidden" name="total_price" id="total_price_input" value="<?= $flight['harga'] ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Lanjut ke Pembayaran</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Calculate total price
        const passengerCountInput = document.getElementById('passenger_count');
        const totalPriceDisplay = document.getElementById('total_price_display');
        const totalPriceInput = document.getElementById('total_price_input');
        const pricePerTicket = <?= $flight['harga'] ?>;

        function updateTotalPrice() {
            const passengerCount = parseInt(passengerCountInput.value) || 0;
            const totalPrice = pricePerTicket * passengerCount;
            totalPriceDisplay.textContent = new Intl.NumberFormat('id-ID').format(totalPrice);
            totalPriceInput.value = totalPrice;
        }

        passengerCountInput.addEventListener('input', updateTotalPrice);
        updateTotalPrice(); // Initial calculation
    </script>
</body>
</html>
