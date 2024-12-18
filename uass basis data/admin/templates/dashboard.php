<?php require_once '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Admin Dashboard</h2>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Bookings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_bookings']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?php echo number_format($stats['total_revenue']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Flights</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['active_flights']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_users']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
            <a href="bookings.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Flight</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['flight_number']); ?></td>
                                <td>Rp <?php echo number_format($booking['total_amount']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $booking['status'] === 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="flights.php" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-plane-departure me-2"></i> Manage Flights
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="bookings.php" class="btn btn-success btn-block w-100">
                                <i class="fas fa-ticket-alt me-2"></i> View Bookings
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="users.php" class="btn btn-info btn-block w-100">
                                <i class="fas fa-users me-2"></i> Manage Users
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="reports.php" class="btn btn-warning btn-block w-100">
                                <i class="fas fa-chart-bar me-2"></i> View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>Database Connection</span>
                            <span class="badge bg-success">Connected</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>Server Load</span>
                            <span class="badge bg-info">Normal</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 65%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>Storage Usage</span>
                            <span class="badge bg-warning">70%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 70%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
