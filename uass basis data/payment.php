<?php
session_start();

// Simulate booking data (in a real application, this would come from form submission and database)
$booking = [
    'booking_id' => 'BK' . rand(100000, 999999),
    'flight' => [
        'airline' => 'Garuda Indonesia',
        'flight_number' => 'GA-123',
        'departure_city' => 'Jakarta (CGK)',
        'arrival_city' => 'Denpasar (DPS)',
        'departure_time' => '07:00',
        'arrival_time' => '08:30',
        'date' => '2024-12-20'
    ],
    'price' => 1500000,
    'tax' => 150000,
    'total' => 1650000,
    'payment_method' => 'transfer',
    'bank_accounts' => [
        'BCA' => '1234567890',
        'Mandiri' => '0987654321',
        'BNI' => '1122334455'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - Airline Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">AirlineBooking</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Konfirmasi Pembayaran</h3>
                        
                        <!-- Booking ID -->
                        <div class="alert alert-info text-center">
                            <strong>Booking ID: <?php echo htmlspecialchars($booking['booking_id']); ?></strong>
                            <br>
                            <small>Harap simpan nomor booking untuk referensi</small>
                        </div>

                        <!-- Payment Instructions -->
                        <div class="payment-instructions mb-4">
                            <h5>Instruksi Pembayaran</h5>
                            <div class="alert alert-warning">
                                <p class="mb-0">Silakan transfer sejumlah <strong>Rp <?php echo number_format($booking['total'], 0, ',', '.'); ?></strong> ke salah satu rekening berikut:</p>
                            </div>
                            
                            <div class="bank-accounts">
                                <?php foreach($booking['bank_accounts'] as $bank => $account): ?>
                                <div class="bank-account-item border rounded p-3 mb-2">
                                    <h6 class="mb-2">Bank <?php echo htmlspecialchars($bank); ?></h6>
                                    <p class="mb-1">No. Rekening: <strong><?php echo htmlspecialchars($account); ?></strong></p>
                                    <p class="mb-0">Atas Nama: <strong>PT Airline Booking</strong></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="payment-details mb-4">
                            <h5>Detail Pembayaran</h5>
                            <table class="table">
                                <tr>
                                    <td>Harga Tiket</td>
                                    <td class="text-end">Rp <?php echo number_format($booking['price'], 0, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td>Pajak & Biaya</td>
                                    <td class="text-end">Rp <?php echo number_format($booking['tax'], 0, ',', '.'); ?></td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Total Pembayaran</td>
                                    <td class="text-end">Rp <?php echo number_format($booking['total'], 0, ',', '.'); ?></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Important Notes -->
                        <div class="important-notes mb-4">
                            <h5>Catatan Penting</h5>
                            <ul class="list-unstyled">
                                <li>✓ Pembayaran harus dilakukan dalam waktu <strong>2 jam</strong></li>
                                <li>✓ Pastikan transfer sesuai dengan jumlah yang tertera</li>
                                <li>✓ Simpan bukti pembayaran</li>
                            </ul>
                        </div>

                        <!-- Upload Payment Proof -->
                        <div class="upload-proof mb-4">
                            <h5>Upload Bukti Pembayaran</h5>
                            <form action="process_payment.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <input type="file" class="form-control" name="payment_proof" accept="image/*" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Upload Bukti Pembayaran</button>
                            </form>
                        </div>

                        <!-- Contact Support -->
                        <div class="text-center">
                            <p class="mb-0">Butuh bantuan? Hubungi customer service kami</p>
                            <p class="mb-0"><strong>WhatsApp: +62 812-3456-7890</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
