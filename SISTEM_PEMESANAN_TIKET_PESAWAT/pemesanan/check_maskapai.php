<?php
require_once '../config/database.php';

try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'Maskapai'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "Tabel Maskapai ada.<br>";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE Maskapai");
        echo "<h3>Struktur Tabel:</h3>";
        echo "<pre>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";

        // Get data count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Maskapai");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<br>Jumlah data dalam tabel: " . $count['total'] . "<br>";

        // Show sample data
        $stmt = $pdo->query("SELECT * FROM Maskapai LIMIT 3");
        echo "<h3>Contoh Data:</h3>";
        echo "<pre>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "Tabel Maskapai belum ada!";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
