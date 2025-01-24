<!DOCTYPE html>
<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

// Initialize user variable
$user = null;

// Ambil data user dari database
try {
    $stmt = $pdo->prepare("SELECT id, username, email, nama, telepon FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new PDOException("User tidak ditemukan");
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Terjadi kesalahan saat mengambil data: " . $e->getMessage();
    header("Location: index.php");
    exit();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Airline Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        body {
            background: var(--sunset-gradient);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .brand {
            background: var(--sunset-gradient);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .brand-name {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            text-decoration: none;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .dashboard-container {
            display: flex;
            margin-top: 64px;
            min-height: calc(100vh - 64px);
        }

        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem 0;
            position: fixed;
            height: calc(100vh - 64px);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid var(--accent-color);
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }

        .profile-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }

            .dashboard-container {
                flex-direction: column;
            }
        }

        .info-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.95);
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: var(--sunset-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            color: var(--light-text);
            font-size: 1.3rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 0.9rem;
            color: var(--secondary-color);
            margin-bottom: 0.25rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .btn-edit {
            background: var(--sunset-gradient);
            color: var(--light-text);
            border: none;
            padding: 0.85rem 2.5rem;
            border-radius: 50px;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            color: var(--light-text);
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: var(--sunset-gradient);
            color: var(--light-text);
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
            border-color: var(--primary-color);
        }
        h2 {
            margin-left: 250px;
            margin-bottom: 30px;
        }

    </style>
</head>
<body>
    <div class="brand">
        <a href="index.php" class="brand-name">AirlineBooking</a>
        <a href="profile.php" class="user-profile">
            <i class="fas fa-user-circle"></i>
            <?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'User Profile' ?>
        </a>
    </div>

    <div class="dashboard-container">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="my_bookings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'my_bookings.php' ? 'active' : '' ?>">
                        <i class="fas fa-ticket-alt"></i>
                        Pemesanan Saya
                    </a>
                </li>
                <li>
                    <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
                        <i class="fas fa-user"></i>
                        Profil
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Keluar
                    </a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="container">
                    <h2><i class="fas fa-user-circle me-2"></i>Profil Pengguna</h2>
                </div>
            </div>

            <!-- Main Content -->
            <div class="container">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="info-card">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Nama Lengkap</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['nama']); ?></div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-at"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Username</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Nomor Telepon</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['telepon'] ?? 'Belum diatur'); ?></div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                    <i class="fas fa-edit me-2"></i>Edit Profil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Modal -->
            <div class="modal fade" id="editProfileModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Profil</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form action="process_profile_update.php" method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control" name="telepon" value="<?php echo htmlspecialchars($user['telepon'] ?? ''); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" name="new_password">
                                    <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password Saat Ini</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                    <div class="form-text">Diperlukan untuk menyimpan perubahan</div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-edit">
                                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                function toggleDropdown() {
                    document.getElementById('userDropdown').classList.toggle('show');
                }

                // Close dropdown when clicking outside
                window.onclick = function(event) {
                    if (!event.target.matches('.user-button') && !event.target.matches('.user-button *')) {
                        var dropdowns = document.getElementsByClassName("dropdown-menu");
                        for (var i = 0; i <dropdowns.length; i++) {
                            var openDropdown = dropdowns[i];
                            if (openDropdown.classList.contains('show')) {
                                openDropdown.classList.remove('show');
                            }
                        }
                    }
                }
            </script>
        </div>
    </div>
</body>
</html>
