<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Delete Operation
if (isset($_POST['delete_flight'])) {
    $flight_id = $_POST['flight_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM flight WHERE id_flight = ?");
        $stmt->execute([$flight_id]);
        $success_message = "Flight deleted successfully!";
    } catch(PDOException $e) {
        $error_message = "Error deleting flight: " . $e->getMessage();
    }
}

// Handle Add/Edit Operation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_flight'])) {
    $id_flight = $_POST['id_flight'] ?? null;
    $id_maskapai = $_POST['id_maskapai'];
    $from_airport = $_POST['from_airport'];
    $to_airport = $_POST['to_airport'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $harga = $_POST['harga'];
    $kapasitas = $_POST['kapasitas'];
    $kelas = $_POST['kelas'];

    try {
        if ($id_flight) {
            // Update existing flight
            $stmt = $pdo->prepare("
                UPDATE flight SET 
                    id_maskapai = ?, 
                    from_airport = ?, 
                    to_airport = ?, 
                    departure_time = ?, 
                    arrival_time = ?, 
                    harga = ?, 
                    kapasitas = ?, 
                    kelas = ?
                WHERE id_flight = ?
            ");
            $stmt->execute([$id_maskapai, $from_airport, $to_airport, $departure_time, 
                          $arrival_time, $harga, $kapasitas, $kelas, $id_flight]);
            $success_message = "Flight updated successfully!";
        } else {
            // Add new flight
            $stmt = $pdo->prepare("
                INSERT INTO flight (id_maskapai, from_airport, to_airport, departure_time, 
                                  arrival_time, harga, kapasitas, kelas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id_maskapai, $from_airport, $to_airport, $departure_time, 
                          $arrival_time, $harga, $kapasitas, $kelas]);
            $success_message = "Flight added successfully!";
        }
    } catch(PDOException $e) {
        $error_message = "Error saving flight: " . $e->getMessage();
    }
}

// Get all flights with related information
$flights_query = "
    SELECT 
        f.*,
        m.nama as airline_name,
        a1.nama_airport as from_airport_name,
        a2.nama_airport as to_airport_name
    FROM flight f
    LEFT JOIN maskapai m ON f.id_maskapai = m.id_maskapai
    LEFT JOIN airport a1 ON f.from_airport = a1.id_airport
    LEFT JOIN airport a2 ON f.to_airport = a2.id_airport
    ORDER BY f.departure_time DESC
";

try {
    $flights = $pdo->query($flights_query)->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching flights: " . $e->getMessage();
    $flights = [];
}

// Get airlines for dropdown
$airlines = $pdo->query("SELECT * FROM maskapai")->fetchAll(PDO::FETCH_ASSOC);

// Get airports for dropdown
$airports = $pdo->query("SELECT * FROM airport")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Flights - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h2>Manage Flights</h2>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="openModal()">
                    <i class="ri-add-line"></i> Add New Flight
                </button>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Airline</th>
                            <th>Route</th>
                            <th>Schedule</th>
                            <th>Price</th>
                            <th>Capacity</th>
                            <th>Class</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flights as $flight): ?>
                        <tr>
                            <td>
                                <div class="airline-name">
                                    <?php echo htmlspecialchars($flight['airline_name']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="route-info">
                                    <div class="from"><?php echo htmlspecialchars($flight['from_airport_name']); ?></div>
                                    <div class="arrow">→</div>
                                    <div class="to"><?php echo htmlspecialchars($flight['to_airport_name']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="schedule-info">
                                    <div class="departure">
                                        <?php 
                                            $departure = new DateTime($flight['departure_time']);
                                            echo $departure->format('H:i - d M Y'); 
                                        ?>
                                    </div>
                                    <div class="arrival">
                                        <?php 
                                            $arrival = new DateTime($flight['arrival_time']);
                                            echo $arrival->format('H:i - d M Y'); 
                                        ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="price">
                                    Rp <?php echo number_format($flight['harga'], 0, ',', '.'); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($flight['kapasitas']); ?></td>
                            <td><?php echo htmlspecialchars($flight['kelas']); ?></td>
                            <td class="actions">
                                <button class="btn btn-edit" onclick="editFlight(<?php 
                                    echo htmlspecialchars(json_encode([
                                        'id_flight' => $flight['id_flight'],
                                        'id_maskapai' => $flight['id_maskapai'],
                                        'from_airport' => $flight['from_airport'],
                                        'to_airport' => $flight['to_airport'],
                                        'departure_time' => $flight['departure_time'],
                                        'arrival_time' => $flight['arrival_time'],
                                        'harga' => $flight['harga'],
                                        'kapasitas' => $flight['kapasitas'],
                                        'kelas' => $flight['kelas']
                                    ])); 
                                ?>)">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="flight_id" value="<?php echo $flight['id_flight']; ?>">
                                    <button type="submit" name="delete_flight" class="btn btn-delete" 
                                            onclick="return confirm('Are you sure you want to delete this flight?')">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal for Add/Edit Flight -->
    <div id="flightModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Add New Flight</h2>
            <form id="flightForm" method="POST">
                <input type="hidden" name="id_flight" id="id_flight">
                
                <div class="form-group">
                    <label for="id_maskapai">Airline</label>
                    <select name="id_maskapai" id="id_maskapai" required>
                        <?php foreach ($airlines as $airline): ?>
                            <option value="<?php echo $airline['id_maskapai']; ?>">
                                <?php echo htmlspecialchars($airline['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="from_airport">From Airport</label>
                    <select name="from_airport" id="from_airport" required>
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?php echo $airport['id_airport']; ?>">
                                <?php echo htmlspecialchars($airport['nama_airport'] . ' (' . $airport['kota'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="to_airport">To Airport</label>
                    <select name="to_airport" id="to_airport" required>
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?php echo $airport['id_airport']; ?>">
                                <?php echo htmlspecialchars($airport['nama_airport'] . ' (' . $airport['kota'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="departure_time">Departure Time</label>
                    <input type="datetime-local" name="departure_time" id="departure_time" required>
                </div>

                <div class="form-group">
                    <label for="arrival_time">Arrival Time</label>
                    <input type="datetime-local" name="arrival_time" id="arrival_time" required>
                </div>

                <div class="form-group">
                    <label for="harga">Price (Rp)</label>
                    <input type="number" name="harga" id="harga" required>
                </div>

                <div class="form-group">
                    <label for="kapasitas">Capacity</label>
                    <input type="number" name="kapasitas" id="kapasitas" required>
                </div>

                <div class="form-group">
                    <label for="kelas">Class</label>
                    <select name="kelas" id="kelas" required>
                        <option value="economy">Economy</option>
                        <option value="business">Business</option>
                        <option value="first">First Class</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_flight" class="btn btn-primary">Save Flight</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal() {
        document.getElementById('flightModal').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Add New Flight';
        document.getElementById('flightForm').reset();
        document.getElementById('id_flight').value = '';
    }

    function closeModal() {
        document.getElementById('flightModal').style.display = 'none';
    }

    function editFlight(flight) {
        document.getElementById('flightModal').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Edit Flight';
        
        document.getElementById('id_flight').value = flight.id_flight;
        document.getElementById('id_maskapai').value = flight.id_maskapai;
        document.getElementById('from_airport').value = flight.from_airport;
        document.getElementById('to_airport').value = flight.to_airport;
        document.getElementById('departure_time').value = flight.departure_time.slice(0, 16);
        document.getElementById('arrival_time').value = flight.arrival_time.slice(0, 16);
        document.getElementById('harga').value = flight.harga;
        document.getElementById('kapasitas').value = flight.kapasitas;
        document.getElementById('kelas').value = flight.kelas;
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('flightModal')) {
            closeModal();
        }
    }
    </script>
</body>
</html>
