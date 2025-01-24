<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Delete Operation
if (isset($_POST['delete_airport'])) {
    $airport_id = $_POST['airport_id'];
    try {
        // Check if airport is used in any flights
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM flight 
            WHERE from_airport = ? OR to_airport = ?
        ");
        $stmt->execute([$airport_id, $airport_id]);
        $flight_count = $stmt->fetchColumn();

        if ($flight_count > 0) {
            $error_message = "Cannot delete airport: It is being used in existing flights.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM airport WHERE id_airport = ?");
            $stmt->execute([$airport_id]);
            $success_message = "Airport deleted successfully!";
        }
    } catch(PDOException $e) {
        $error_message = "Error deleting airport: " . $e->getMessage();
    }
}

// Handle Add/Edit Operation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_airport'])) {
    $id_airport = $_POST['id_airport'] ?? null;
    $kode_airport = strtoupper($_POST['kode_airport']);
    $nama_airport = $_POST['nama_airport'];
    $kota = $_POST['kota'];
    $negara = $_POST['negara'];

    try {
        if ($id_airport) {
            // Update existing airport
            $stmt = $pdo->prepare("
                UPDATE airport 
                SET kode_airport = ?, nama_airport = ?, kota = ?, negara = ?
                WHERE id_airport = ?
            ");
            $stmt->execute([$kode_airport, $nama_airport, $kota, $negara, $id_airport]);
            $success_message = "Airport updated successfully!";
        } else {
            // Add new airport
            $stmt = $pdo->prepare("
                INSERT INTO airport (kode_airport, nama_airport, kota, negara)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$kode_airport, $nama_airport, $kota, $negara]);
            $success_message = "Airport added successfully!";
        }
    } catch(PDOException $e) {
        $error_message = "Error saving airport: " . $e->getMessage();
    }
}

// Get all airports with flight counts
$airports_query = "
    SELECT 
        a.*,
        COUNT(DISTINCT f1.id_flight) + COUNT(DISTINCT f2.id_flight) as flight_count
    FROM airport a
    LEFT JOIN flight f1 ON a.id_airport = f1.from_airport
    LEFT JOIN flight f2 ON a.id_airport = f2.to_airport
    GROUP BY a.id_airport
    ORDER BY a.negara, a.kota, a.nama_airport
";
$airports = $pdo->query($airports_query)->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<main class="main-content">
    <div class="page-header">
        <h2>Manage Airports</h2>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="action-buttons">
        <button class="btn btn-primary" onclick="openModal()">
            <i class="ri-add-line"></i> Add New Airport
        </button>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Airport Name</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>Flights</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($airports as $airport): ?>
                <tr>
                    <td><?php echo htmlspecialchars($airport['kode_airport']); ?></td>
                    <td><?php echo htmlspecialchars($airport['nama_airport']); ?></td>
                    <td><?php echo htmlspecialchars($airport['kota']); ?></td>
                    <td><?php echo htmlspecialchars($airport['negara']); ?></td>
                    <td><?php echo $airport['flight_count']; ?> flights</td>
                    <td class="actions">
                        <button class="btn btn-edit" onclick="editAirport(<?php 
                            echo htmlspecialchars(json_encode([
                                'id' => $airport['id_airport'],
                                'code' => $airport['kode_airport'],
                                'name' => $airport['nama_airport'],
                                'city' => $airport['kota'],
                                'country' => $airport['negara']
                            ])); 
                        ?>)">
                            <i class="ri-edit-line"></i>
                        </button>
                        <?php if ($airport['flight_count'] == 0): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="airport_id" value="<?php echo $airport['id_airport']; ?>">
                            <button type="submit" name="delete_airport" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this airport?')">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal for Add/Edit Airport -->
<div id="airportModal" class="modal">
    <div class="modal-content">
        <h3 id="modalTitle">Add New Airport</h3>
        <form method="POST" id="airportForm">
            <input type="hidden" name="id_airport" id="id_airport">
            
            <div class="form-group">
                <label for="kode_airport">Airport Code</label>
                <input type="text" name="kode_airport" id="kode_airport" required maxlength="3" 
                       pattern="[A-Za-z]{3}" title="Airport code must be exactly 3 letters">
            </div>

            <div class="form-group">
                <label for="nama_airport">Airport Name</label>
                <input type="text" name="nama_airport" id="nama_airport" required>
            </div>

            <div class="form-group">
                <label for="kota">City</label>
                <input type="text" name="kota" id="kota" required>
            </div>

            <div class="form-group">
                <label for="negara">Country</label>
                <input type="text" name="negara" id="negara" required>
            </div>

            <div class="form-group">
                <button type="submit" name="save_airport" class="btn btn-primary">Save Airport</button>
                <button type="button" onclick="closeModal()" class="btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('modalTitle').textContent = 'Add New Airport';
        document.getElementById('airportForm').reset();
        document.getElementById('id_airport').value = '';
        document.getElementById('airportModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('airportModal').style.display = 'none';
    }

    function editAirport(airport) {
        document.getElementById('modalTitle').textContent = 'Edit Airport';
        document.getElementById('id_airport').value = airport.id;
        document.getElementById('kode_airport').value = airport.code;
        document.getElementById('nama_airport').value = airport.name;
        document.getElementById('kota').value = airport.city;
        document.getElementById('negara').value = airport.country;
        document.getElementById('airportModal').style.display = 'block';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('airportModal')) {
            closeModal();
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
