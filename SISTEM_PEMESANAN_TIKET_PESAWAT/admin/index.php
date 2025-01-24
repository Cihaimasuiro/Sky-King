<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1B4B72 0%, #FF6B35 100%);
            display: flex;
        }

        .sidebar {
            width: 280px;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            padding: 2rem;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 2rem;
            color: white;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            background: rgba(27, 75, 114, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            color: #1B4B72;
            font-size: 1.25rem;
        }

        .stats-info {
            flex: 1;
        }

        .stats-title {
            color: #666;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .stats-value {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1B4B72;
            margin-bottom: 0.5rem;
        }

        .stats-trend {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .trend-up { color: #22C55E; }
        .trend-down { color: #EF4444; }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1B4B72;
            margin: 0;
        }

        .chart-tabs {
            display: flex;
            gap: 0.5rem;
        }

        .tab-button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            background: #f1f5f9;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tab-button.active {
            background: #1B4B72;
            color: white;
        }

        .chart-container {
            height: 300px;
            position: relative;
        }

        .recent-sales {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .sales-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .sales-table th,
        .sales-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .sales-table th {
            font-weight: 600;
            color: #1B4B72;
            background: #f8fafc;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-completed { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef9c3; color: #854d0e; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .sidebar { 
                transform: translateX(-100%);
                z-index: 1000;
            }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="page-header">
            <h1 class="page-title">Dashboard Overview</h1>
        </header>

        <div class="stats-grid">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stats-info">
                    <div class="stats-title">Total Sales</div>
                    <div class="stats-value">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM booking");
                        echo $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                        ?>
                    </div>
                    <div class="stats-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        12.5% vs last month
                    </div>
                </div>
            </div>

            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stats-info">
                    <div class="stats-title">Total Revenue</div>
                    <div class="stats-value">
                        <?php
                        $stmt = $pdo->query("SELECT SUM(total_price) as revenue FROM booking");
                        $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'];
                        echo 'Rp ' . number_format($revenue, 0, ',', '.');
                        ?>
                    </div>
                    <div class="stats-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        8.2% vs last month
                    </div>
                </div>
            </div>

            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stats-info">
                    <div class="stats-title">Avg. Ticket Price</div>
                    <div class="stats-value">
                        <?php
                        $stmt = $pdo->query("SELECT AVG(total_price) as avg_price FROM booking");
                        $avg_price = $stmt->fetch(PDO::FETCH_ASSOC)['avg_price'];
                        echo 'Rp ' . number_format($avg_price, 0, ',', '.');
                        ?>
                    </div>
                    <div class="stats-trend trend-up">
                        <i class="fas fa-arrow-up"></i>
                        5.3% vs last month
                    </div>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h2 class="chart-title">Sales Analytics</h2>
                    <div class="chart-tabs">
                        <button class="tab-button active">Weekly</button>
                        <button class="tab-button">Monthly</button>
                        <button class="tab-button">Yearly</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h2 class="chart-title">Popular Routes</h2>
                </div>
                <div class="routes-list">
                    <?php
                    $query = "SELECT 
                                a1.kota as from_city,
                                a2.kota as to_city,
                                COUNT(*) as total_bookings
                             FROM booking b
                             JOIN flight f ON b.id_flight = f.id_flight
                             JOIN airport a1 ON f.from_airport = a1.id_airport
                             JOIN airport a2 ON f.to_airport = a2.id_airport
                             GROUP BY f.from_airport, f.to_airport
                             ORDER BY total_bookings DESC
                             LIMIT 5";
                    $result = $pdo->query($query);
                    while ($route = $result->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <div class="route-item">
                        <div class="route-info">
                            <div class="route-cities">
                                <?= htmlspecialchars($route['from_city']) ?> → 
                                <?= htmlspecialchars($route['to_city']) ?>
                            </div>
                            <div class="route-count">
                                <?= $route['total_bookings'] ?> bookings
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="recent-sales">
            <div class="chart-header">
                <h2 class="chart-title">Recent Sales</h2>
            </div>
            <table class="sales-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Route</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT b.*, u.nama as customer_name,
                             a1.kota as from_city, a2.kota as to_city,
                             b.booking_date, b.total_price, b.status
                             FROM booking b
                             JOIN users u ON b.id_user = u.id
                             JOIN flight f ON b.id_flight = f.id_flight
                             JOIN airport a1 ON f.from_airport = a1.id_airport
                             JOIN airport a2 ON f.to_airport = a2.id_airport
                             ORDER BY b.booking_date DESC LIMIT 5";
                    $result = $pdo->query($query);
                    while ($sale = $result->fetch(PDO::FETCH_ASSOC)):
                        $statusClass = match($sale['status']) {
                            'completed' => 'status-completed',
                            'pending' => 'status-pending',
                            'cancelled' => 'status-cancelled',
                            default => ''
                        };
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                        <td>
                            <?= htmlspecialchars($sale['from_city']) ?> → 
                            <?= htmlspecialchars($sale['to_city']) ?>
                        </td>
                        <td><?= date('d M Y', strtotime($sale['booking_date'])) ?></td>
                        <td>Rp <?= number_format($sale['total_price'], 0, ',', '.') ?></td>
                        <td>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= ucfirst($sale['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    fill: true,
                    borderColor: '#1B4B72',
                    backgroundColor: 'rgba(27, 75, 114, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });
    </script>
</body>
</html>
