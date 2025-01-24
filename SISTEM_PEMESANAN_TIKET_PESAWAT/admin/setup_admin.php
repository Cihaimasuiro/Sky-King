<?php
require_once '../config/database.php';

try {
    // Create admin table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE username = ?");
    $stmt->execute(['admin']);
    $adminExists = $stmt->fetchColumn() > 0;

    if (!$adminExists) {
        // Create new admin user with properly hashed password
        $username = 'admin';
        $password = 'admin123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);

        echo "Admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Admin user already exists!<br>";
    }

    // Show the current admin users (for debugging)
    $admins = $pdo->query("SELECT id, username, password FROM admin")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($admins);
    echo "</pre>";

} catch(PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
?>
