<?php
require_once 'includes/header.php';
require_once 'models/Airport.php';

// Initialize Airport model
$airportModel = new Airport();
$airports = $airportModel->getAllAirports();
?>

<!-- Hero Section -->
<div class="container-fluid bg-primary text-white py-5">
    <div class="container">
        <h1 class="display-4 text-center">Temukan Penerbangan Terbaik</h1>
        <p class="lead text-center">Cari dan pesan tiket pesawat dengan mudah dan cepat</p>
    </div>
</div>

<!-- Search Form -->
<div class="container my-5">
    <div class="card shadow">
        <div class="card-body">
            <form action="search_results.php" method="GET" id="searchForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="from" class="form-label">Kota Asal</label>
                        <select class="form-select" id="from" name="from" required>
                            <option value="">Pilih kota asal</option>
                            <?php foreach($airports as $airport): ?>
                                <option value="<?php echo $airport['id_bandara']; ?>">
                                    <?php echo htmlspecialchars($airport['kota'] . ' (' . $airport['kode'] . ') - ' . $airport['nama_bandara']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="to" class="form-label">Kota Tujuan</label>
                        <select class="form-select" id="to" name="to" required>
                            <option value="">Pilih kota tujuan</option>
                            <?php foreach($airports as $airport): ?>
                                <option value="<?php echo $airport['id_bandara']; ?>">
                                    <?php echo htmlspecialchars($airport['kota'] . ' (' . $airport['kode'] . ') - ' . $airport['nama_bandara']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date" class="form-label">Tanggal Berangkat</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="col-md-3">
                        <label for="passengers" class="form-label">Jumlah Penumpang</label>
                        <input type="number" class="form-control" id="passengers" name="passengers" min="1" max="10" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label for="class" class="form-label">Kelas Pesawat</label>
                        <select class="form-select" id="class" name="class" required>
                            <option value="economy">Ekonomi</option>
                            <option value="business">Bisnis</option>
                            <option value="first">First Class</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Cari Pesawat</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container mb-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card">
                <i class="fas fa-tag mb-3"></i>
                <h3>Harga Terbaik</h3>
                <p>Dapatkan harga tiket pesawat terbaik untuk perjalanan Anda dengan berbagai pilihan maskapai.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <i class="fas fa-check-circle mb-3"></i>
                <h3>Mudah & Cepat</h3>
                <p>Proses pemesanan yang mudah dan cepat dengan berbagai pilihan pembayaran yang aman.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <i class="fas fa-headset mb-3"></i>
                <h3>24/7 Support</h3>
                <p>Layanan pelanggan 24 jam untuk membantu perjalanan Anda kapanpun dibutuhkan.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize flatpickr date picker
    flatpickr("#date", {
        minDate: "today",
        dateFormat: "Y-m-d"
    });

    // Form validation
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        const from = document.getElementById('from').value;
        const to = document.getElementById('to').value;
        const passengers = document.getElementById('passengers').value;

        if (from === to) {
            e.preventDefault();
            alert('Kota asal dan tujuan tidak boleh sama');
            return;
        }

        if (parseInt(passengers) < 1 || parseInt(passengers) > 10) {
            e.preventDefault();
            alert('Jumlah penumpang harus antara 1-10 orang');
            return;
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
