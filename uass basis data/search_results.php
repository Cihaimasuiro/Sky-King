<?php
require_once 'includes/header.php';
require_once 'models/Flight.php';
require_once 'models/Airport.php';

// Initialize models
$flightModel = new Flight();
$airportModel = new Airport();

// Get search parameters
$from = isset($_GET['from']) ? sanitize_input($_GET['from']) : '';
$to = isset($_GET['to']) ? sanitize_input($_GET['to']) : '';
$date = isset($_GET['date']) ? sanitize_input($_GET['date']) : '';
$passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;
$class = isset($_GET['class']) ? sanitize_input($_GET['class']) : 'economy';

// Search criteria
$criteria = [
    'id_bandara_asal' => $from,
    'id_bandara_tujuan' => $to,
    'tanggal' => $date,
    'jumlah_penumpang' => $passengers,
    'kelas' => $class
];

// Get search results
$flights = $flightModel->searchFlights($criteria);
?>

<div class="container my-4">
    <h2>Flight Search Results</h2>
    <div class="search-summary bg-light p-3 rounded mb-4">
        <?php
        $fromAirport = $airportModel->getAirportById($from);
        $toAirport = $airportModel->getAirportById($to);
        ?>
        <p class="mb-0">
            <strong>From:</strong> <?php echo htmlspecialchars($fromAirport['nama_bandara'] . ' (' . $fromAirport['kode'] . ')'); ?> |
            <strong>To:</strong> <?php echo htmlspecialchars($toAirport['nama_bandara'] . ' (' . $toAirport['kode'] . ')'); ?> |
            <strong>Date:</strong> <?php echo format_date($date, 'd M Y'); ?> |
            <strong>Passengers:</strong> <?php echo $passengers; ?> |
            <strong>Class:</strong> <?php echo ucfirst($class); ?>
        </p>
    </div>

    <?php if (empty($flights)): ?>
        <div class="alert alert-info">
            No flights found for your search criteria. Please try different dates or destinations.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($flights as $flight): ?>
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img src="uploads/airlines/<?php echo htmlspecialchars($flight['logo']); ?>" 
                                         alt="<?php echo htmlspecialchars($flight['nama_maskapai']); ?>"
                                         class="img-fluid" style="max-height: 50px;">
                                    <div class="airline-name"><?php echo htmlspecialchars($flight['nama_maskapai']); ?></div>
                                    <div class="flight-number"><?php echo htmlspecialchars($flight['nomor_penerbangan']); ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="flight-times">
                                        <div class="departure">
                                            <strong><?php echo date('H:i', strtotime($flight['waktu_berangkat'])); ?></strong>
                                            <div><?php echo htmlspecialchars($flight['kode_asal']); ?></div>
                                        </div>
                                        <div class="duration">
                                            <?php
                                            $departure = new DateTime($flight['waktu_berangkat']);
                                            $arrival = new DateTime($flight['waktu_tiba']);
                                            $duration = $departure->diff($arrival);
                                            echo $duration->format('%hh %im');
                                            ?>
                                        </div>
                                        <div class="arrival">
                                            <strong><?php echo date('H:i', strtotime($flight['waktu_tiba'])); ?></strong>
                                            <div><?php echo htmlspecialchars($flight['kode_tujuan']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="seats-available">
                                        <?php echo $flight['kursi_tersedia']; ?> seats left
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="price mb-2">
                                        <strong>IDR <?php echo number_format($flight['harga'], 0, ',', '.'); ?></strong>
                                        <small class="d-block">per person</small>
                                    </div>
                                    <?php if ($flight['kursi_tersedia'] >= $passengers): ?>
                                        <a href="booking.php?flight_id=<?php echo $flight['id_penerbangan']; ?>&passengers=<?php echo $passengers; ?>" 
                                           class="btn btn-primary">Select Flight</a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled>Not Available</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.flight-times {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 15px 0;
}

.duration {
    color: #666;
    font-size: 0.9em;
    text-align: center;
    position: relative;
}

.duration:before {
    content: '';
    height: 2px;
    background: #ddd;
    width: 100%;
    position: absolute;
    top: 50%;
    left: 0;
    z-index: 0;
}

.duration span {
    background: white;
    padding: 0 10px;
    position: relative;
    z-index: 1;
}

.seats-available {
    color: #28a745;
    font-size: 0.9em;
}

.airline-name {
    font-weight: bold;
    margin-top: 5px;
}

.flight-number {
    color: #666;
    font-size: 0.9em;
}
</style>

<?php require_once 'includes/footer.php'; ?>
