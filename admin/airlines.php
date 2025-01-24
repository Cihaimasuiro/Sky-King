<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Delete Operation
if (isset($_POST['delete_airline'])) {
    $airline_id = $_POST['airline_id'];
    try {
        // Check if airline has any flights
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM flight WHERE id_maskapai = ?");
        $stmt->execute([$airline_id]);
        $flight_count = $stmt->fetchColumn();

        if ($flight_count > 0) {
            $error_message = "Cannot delete airline: There are flights associated with this airline.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM maskapai WHERE id_maskapai = ?");
            $stmt->execute([$airline_id]);
            $success_message = "Airline deleted successfully!";
        }
    } catch(PDOException $e) {
        $error_message = "Error deleting airline: " . $e->getMessage();
    }
}

// Handle Add/Edit Operation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_airline'])) {
    $id_maskapai = $_POST['id_maskapai'] ?? null;
    $nama = $_POST['nama'];
    $kode = $_POST['kode'];
    
    // Handle logo upload
    $logo = $_FILES['logo'] ?? null;
    $logo_path = null;
    
    if ($logo && $logo['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $logo['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $upload_dir = '../uploads/airlines/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = uniqid() . '.' . $ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($logo['tmp_name'], $destination)) {
                $logo_path = 'uploads/airlines/' . $new_filename;
            }
        }
    }

    try {
        if ($id_maskapai) {
            // Update existing airline
            $sql = "UPDATE maskapai SET nama = ?, kode = ?";
            $params = [$nama, $kode];
            
            if ($logo_path) {
                $sql .= ", logo = ?";
                $params[] = $logo_path;
            }
            
            $sql .= " WHERE id_maskapai = ?";
            $params[] = $id_maskapai;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $success_message = "Airline updated successfully!";
        } else {
            // Add new airline
            $stmt = $pdo->prepare("
                INSERT INTO maskapai (nama, kode, logo)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$nama, $kode, $logo_path]);
            $success_message = "Airline added successfully!";
        }
    } catch(PDOException $e) {
        $error_message = "Error saving airline: " . $e->getMessage();
    }
}

// Get all airlines with flight counts
$airlines_query = "
    SELECT 
        m.*,
        COUNT(f.id_flight) as flight_count
    FROM maskapai m
    LEFT JOIN flight f ON m.id_maskapai = f.id_maskapai
    GROUP BY m.id_maskapai
    ORDER BY m.nama
";
$airlines = $pdo->query($airlines_query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Airlines - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h2>Manage Airlines</h2>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="openModal()">
                    <i class="ri-add-line"></i> Add New Airline
                </button>
            </div>

            <div class="airlines-grid">
                <?php foreach ($airlines as $airline): ?>
                    <div class="airline-card">
                        <div class="airline-logo">
                            <?php if ($airline['logo']): ?>
                                <img src="<?php echo '../' . $airline['logo']; ?>" alt="<?php echo $airline['nama']; ?> logo">
                            <?php else: ?>
                                <i class="ri-plane-line" style="font-size: 3rem; color: var(--text-muted);"></i>
                            <?php endif; ?>
                        </div>
                        <div class="airline-info">
                            <h3 class="airline-name"><?php echo $airline['nama']; ?></h3>
                            <div class="airline-code">Code: <?php echo $airline['kode']; ?></div>
                            <div class="airline-stats">
                                <span><i class="ri-flight-takeoff-line"></i> <?php echo $airline['flight_count']; ?> flights</span>
                            </div>
                            <div class="airline-actions">
                                <button class="btn btn-primary" onclick="editAirline(<?php echo htmlspecialchars(json_encode($airline)); ?>)">
                                    <i class="ri-edit-line"></i> Edit
                                </button>
                                <?php if ($airline['flight_count'] == 0): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this airline?');">
                                        <input type="hidden" name="airline_id" value="<?php echo $airline['id_maskapai']; ?>">
                                        <button type="submit" name="delete_airline" class="btn btn-danger">
                                            <i class="ri-delete-bin-line"></i> Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Modal for Add/Edit Airline -->
    <div id="airlineModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Add New Airline</h3>
            <form method="POST" enctype="multipart/form-data" id="airlineForm">
                <input type="hidden" name="id_maskapai" id="id_maskapai">
                
                <div class="form-group">
                    <label for="nama">Airline Name</label>
                    <input type="text" name="nama" id="nama" required>
                </div>

                <div class="form-group">
                    <label for="kode">Airline Code</label>
                    <input type="text" name="kode" id="kode" required>
                </div>

                <div class="form-group">
                    <label for="logo">Logo</label>
                    <input type="file" name="logo" id="logo" accept="image/*">
                </div>

                <div class="form-group">
                    <button type="submit" name="save_airline" class="btn btn-primary">Save Airline</button>
                    <button type="button" onclick="closeModal()" class="btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Add New Airline';
            document.getElementById('airlineForm').reset();
            document.getElementById('id_maskapai').value = '';
            document.getElementById('airlineModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('airlineModal').style.display = 'none';
        }

        function editAirline(airline) {
            document.getElementById('modalTitle').textContent = 'Edit Airline';
            document.getElementById('id_maskapai').value = airline.id_maskapai;
            document.getElementById('nama').value = airline.nama;
            document.getElementById('kode').value = airline.kode;
            document.getElementById('airlineModal').style.display = 'block';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('airlineModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>
