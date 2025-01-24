<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>

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
        --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --hover-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        --border-radius: 15px;
        --spacing-sm: 0.5rem;
        --spacing-md: 1rem;
        --spacing-lg: 1.5rem;
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
        font-family: 'Inter', sans-serif;
    }

    .brand-name {
        padding-left: 250px;
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
        min-height: calc(100vh - 64px);
    }

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 250px;
        height: 100vh;
        background: rgba(27, 75, 114, 0.97);
        backdrop-filter: blur(10px);
        color: white;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        z-index: 1000;
        font-family: 'Inter', sans-serif;
    }

    .sidebar-header {
        padding: var(--spacing-lg);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logo {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        font-size: 1.25rem;
        font-weight: 600;
    }

    .logo i {
        font-size: 1.5rem;
        color: var(--accent-color);
    }

    .sidebar-nav {
        flex: 1;
        padding: var(--spacing-md) 0;
        overflow-y: auto;
    }

    .sidebar-nav::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-nav::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    .sidebar-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-nav li {
        margin: var(--spacing-sm) 0;
    }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        padding: var(--spacing-md) var(--spacing-lg);
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        margin-right: var(--spacing-md);
        font-weight: 500;
    }

    .sidebar-nav a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transform: translateX(5px);
    }

    .sidebar-nav li.active a {
        background: var(--primary-color);
        color: white;
        position: relative;
    }

    .sidebar-nav li.active a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: var(--accent-color);
    }

    .sidebar-nav i {
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
    }

    .sidebar-footer {
        padding: var(--spacing-lg);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        padding: var(--spacing-md);
        border-radius: var(--border-radius);
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .logout-btn:hover {
        background: rgba(255, 107, 53, 0.2);
        color: var(--accent-color);
    }

    .main-content {
        flex: 1;
        margin-left: 250px;
        padding: 2rem;
        background: var(--sunset-gradient);
        min-height: 100vh;
    }

    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar.show {
            transform: translateX(0);
        }
    }
</style>

<div class="brand">
    <a href="index.php" class="brand-name">AirlineBooking Admin</a>
    <a href="#" class="user-profile">
        <i class="fas fa-user-circle"></i>
        <?= isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin' ?>
    </a>
</div>

<div>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-plane-departure"></i>
                <span>Flight Admin</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'flights.php' ? 'active' : ''; ?>">
                    <a href="flights.php">
                        <i class="fas fa-plane"></i>
                        <span>Flights</span>
                    </a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
                    <a href="bookings.php">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Bookings</span>
                    </a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
                    <a href="customers.php">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'airlines.php' ? 'active' : ''; ?>">
                    <a href="airlines.php">
                        <i class="fas fa-building"></i>
                        <span>Airlines</span>
                    </a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'airports.php' ? 'active' : ''; ?>">
                    <a href="airports.php">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Airports</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    }
});
</script>
