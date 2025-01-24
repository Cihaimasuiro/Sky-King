<?php
include '../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Service - Airline Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Your existing CSS styles from index.php */
        body {
            background: linear-gradient(135deg, 
                rgba(27, 75, 114, 0.98) 0%, 
                rgba(44, 95, 143, 0.98) 30%, 
                rgba(255, 107, 53, 0.95) 70%, 
                rgba(255, 181, 102, 0.95) 100%
            );
            color: #fff;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .container {
            max-width: 600px;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .container h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .container p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
    </style>
    <script>
        // Redirect after a few seconds
        setTimeout(function() {
            window.location.href = '../pemesanan/index.php';
        }, 5000); // 5 seconds
    </script>
</head>
<body>
    <div class="container">
        <h1>Halaman Customer Service</h1>
        <p>Halaman ini sedang dalam pengembangan. Anda akan diarahkan ke halaman utama dalam beberapa detik.</p>
        <p>Terima kasih atas kesabaran Anda.</p>
    </div>
</body>
</html>