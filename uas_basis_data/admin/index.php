<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'uasbasisdata';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airline Booking System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        header {
            background-color: #007BFF;
            color: white;
            padding: 1rem 0;
            text-align: center;
        }
        nav {
            display: flex;
            justify-content: center;
            background-color: #0056b3;
            padding: 0.5rem 0;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin: 0 1rem;
            font-weight: bold;
        }
        nav a:hover {
            text-decoration: underline;
        }
        main {
            padding: 2rem;
        }
        .section {
            margin-bottom: 2rem;
            background: white;
            padding: 1rem;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1rem 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 0.5rem;
            text-align: left;
        }
        th {
            background-color: #f4f4f9;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to Airline Booking System</h1>
    </header>
    <nav>
        <a href="#customers">Customers</a>
        <a href="#flights">Flights</a>
        <a href="#bookings">Bookings</a>
        <a href="#payments">Payments</a>
    </nav>
    <main>
        <section id="customers" class="section">
            <h2>Customer List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Passport Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM Customer";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['id_customer']}</td>";
                            echo "<td>{$row['nama']}</td>";
                            echo "<td>{$row['email']}</td>";
                            echo "<td>{$row['username']}</td>";
                            echo "<td>{$row['no_pasport']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No data available</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <section id="flights" class="section">
            <h2>Flight Information</h2>
            <table>
                <thead>
                    <tr>
                        <th>Flight ID</th>
                        <th>Flight Number</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Origin</th>
                        <th>Destination</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT P.id_penerbangan, P.nomor_penerbangan, P.jam_berangkat, P.jam_kedatangan, B1.nama AS asal, B2.nama AS tujuan
                            FROM Penerbangan P
                            JOIN Bandara B1 ON P.asal_penerbangan = B1.id_bandara
                            JOIN Bandara B2 ON P.tujuan_penerbangan = B2.id_bandara";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['id_penerbangan']}</td>";
                            echo "<td>{$row['nomor_penerbangan']}</td>";
                            echo "<td>{$row['jam_berangkat']}</td>";
                            echo "<td>{$row['jam_kedatangan']}</td>";
                            echo "<td>{$row['asal']}</td>";
                            echo "<td>{$row['tujuan']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No data available</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <section id="bookings" class="section">
            <h2>Booking List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                        <th>Customer ID</th>
                        <th>Flight ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM Pemesanan";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['id_pemesanan']}</td>";
                            echo "<td>{$row['tanggal_pesan']}</td>";
                            echo "<td>{$row['status']}</td>";
                            echo "<td>{$row['id_customer']}</td>";
                            echo "<td>{$row['id_penerbangan']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No data available</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <section id="payments" class="section">
            <h2>Payment Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Booking ID</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Payment Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM Pembayaran";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$row['id_pembayaran']}</td>";
                            echo "<td>{$row['id_pemesanan']}</td>";
                            echo "<td>{$row['jumlah']}</td>";
                            echo "<td>{$row['tanggal_pembayaran']}</td>";
                            echo "<td>{$row['jenis_pembayaran']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No data available</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Airline Booking System. All Rights Reserved.</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>
