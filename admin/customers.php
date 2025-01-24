<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle Delete Operation
if (isset($_POST['delete_customer'])) {
    $user_id = $_POST['user_id'];
    try {
        // Check if user has any bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $booking_count = $stmt->fetchColumn();

        if ($booking_count > 0) {
            $error_message = "Cannot delete user: They have existing bookings.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $success_message = "User deleted successfully!";
        }
    } catch(PDOException $e) {
        $error_message = "Error deleting user: " . $e->getMessage();
    }
}

// Handle Add/Edit Operation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_customer'])) {
    $user_id = $_POST['user_id'] ?? null;
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $telepon = $_POST['telepon'];
    $password = $_POST['password'];

    try {
        if ($user_id) {
            // Update existing user
            $sql = "UPDATE users SET nama = ?, email = ?, username = ?, telepon = ?";
            $params = [$nama, $email, $username, $telepon];
            
            if (!empty($password)) {
                $sql .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $user_id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $success_message = "User updated successfully!";
        } else {
            // Add new user
            $stmt = $pdo->prepare("
                INSERT INTO users (nama, email, username, password, telepon)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nama,
                $email,
                $username,
                password_hash($password, PASSWORD_DEFAULT),
                $telepon
            ]);
            $success_message = "User added successfully!";
        }
    } catch(PDOException $e) {
        $error_message = "Error saving user: " . $e->getMessage();
    }
}

// Get all users with booking counts
$users_query = "
    SELECT 
        u.*,
        COUNT(b.id_booking) as booking_count,
        SUM(b.total_price) as total_spent,
        u.created_at as registration_date
    FROM users u
    LEFT JOIN booking b ON u.id = b.id_user
    GROUP BY u.id
    ORDER BY u.nama
";
$users = $pdo->query($users_query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css">

</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h2>Manage Users</h2>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="openModal()">
                    <i class="ri-add-line"></i> Add New User
                </button>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact Info</th>
                            <th>Username</th>
                            <th>Registration</th>
                            <th>Statistics</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-name">
                                    <?php echo htmlspecialchars($user['nama']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
                                    <div class="phone"><?php echo htmlspecialchars($user['telepon']); ?></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <div class="date-info">
                                    <?php 
                                        $date = new DateTime($user['registration_date']);
                                        echo $date->format('d M Y'); 
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="user-stats">
                                    <div class="bookings">
                                        <span class="stat-label">Bookings:</span>
                                        <span class="stat-value"><?php echo $user['booking_count']; ?></span>
                                    </div>
                                    <div class="spent">
                                        <span class="stat-label">Total Spent:</span>
                                        <span class="stat-value">Rp <?php echo number_format($user['total_spent'] ?? 0, 0, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="actions">
                                <button class="btn btn-edit" onclick="editUser(<?php 
                                    echo htmlspecialchars(json_encode([
                                        'id' => $user['id'],
                                        'nama' => $user['nama'],
                                        'email' => $user['email'],
                                        'username' => $user['username'],
                                        'telepon' => $user['telepon']
                                    ])); 
                                ?>)">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_customer" class="btn btn-delete" 
                                            onclick="return confirm('Are you sure you want to delete this user?')">
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

    <!-- Modal for Add/Edit User -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Add New User</h3>
            <form id="customerForm" method="POST">
                <input type="hidden" id="user_id" name="user_id">
                
                <div class="form-group">
                    <label for="nama">Name</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="telepon">Phone</label>
                    <input type="tel" id="telepon" name="telepon" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                    <small class="text-muted">Leave empty to keep current password when editing</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="save_customer" class="btn btn-primary">Save</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('customerForm').reset();
            document.getElementById('user_id').value = '';
            document.getElementById('userModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        function editUser(user) {
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('user_id').value = user.id;
            document.getElementById('nama').value = user.nama;
            document.getElementById('email').value = user.email;
            document.getElementById('username').value = user.username;
            document.getElementById('telepon').value = user.telepon;
            document.getElementById('password').value = ''; // Clear password field
            document.getElementById('userModal').style.display = 'block';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('userModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>
