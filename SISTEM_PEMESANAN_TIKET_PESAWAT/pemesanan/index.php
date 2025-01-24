<?php
session_start();
require_once '../config/database.php';

// Fetch cities from the airport table
$cities_query = "SELECT DISTINCT kota FROM airport";
$cities = $pdo->query($cities_query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Airline Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF6B35;
            --secondary-color: #1B4B72;
            --accent-color: #FFB566;
            --sunset-gradient: linear-gradient(135deg, 
                #1B4B72 0%, 
                #2C5F8F 30%, 
                #FF6B35 70%, 
                #FFB566 100%
            );
            --sunset-overlay: linear-gradient(135deg, 
                rgba(27, 75, 114, 0.95), 
                rgba(44, 95, 143, 0.85),
                rgba(255, 107, 53, 0.85),
                rgba(255, 181, 102, 0.95)
            );
            --text-color: #333;
            --light-text: #fff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--sunset-gradient);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(0, 78, 137, 0.95);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            background: linear-gradient(135deg, #fff, #FFD700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-btn {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-btn {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .login-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .register-btn {
            color: white;
            background: linear-gradient(135deg, #FFB566, #FF6B35);
            border: none;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        .user-profile {
            position: relative;
        }

        .profile-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .profile-button i {
            font-size: 1.2rem;
        }

        .profile-dropdown {
            position: absolute;
            top: 120%;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .user-profile:hover .profile-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .profile-dropdown a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.8rem 1.5rem;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .profile-dropdown a:hover {
            background: rgba(255, 107, 53, 0.1);
            color: #FF6B35;
        }

        .profile-dropdown i {
            font-size: 1.1rem;
            color: #FF6B35;
        }

        /* Hero Section Styles */
        .hero {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(135deg, 
                rgba(27, 75, 114, 0.98) 0%, 
                rgba(44, 95, 143, 0.98) 30%, 
                rgba(255, 107, 53, 0.95) 70%, 
                rgba(255, 181, 102, 0.95) 100%
            );
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 120px 0 60px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 120%;
            height: 120%;
            background-image: url('../image/pesawat.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 80%;
            opacity: 0.2;
            transform: translate(-50%, -50%);
            animation: floatPlane 20s ease-in-out infinite;
            z-index: 1;
        }

        @keyframes floatPlane {
            0%, 100% {
                transform: translate(-50%, -50%);
            }
            50% {
                transform: translate(-48%, -52%);
            }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #fff, #FFD700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 4px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -1px;
            position: relative;
            display: inline-block;
            animation: titleGlow 3s ease-in-out infinite;
        }

        @keyframes titleGlow {
            0%, 100% {
                text-shadow: 2px 4px 8px rgba(0, 0, 0, 0.3);
            }
            50% {
                text-shadow: 0 0 20px rgba(255, 215, 0, 0.5),
                            0 0 30px rgba(255, 215, 0, 0.3);
            }
        }

        .hero-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 4px;
            background: linear-gradient(to right, #FFD700, #FF6B35);
            border-radius: 2px;
            animation: lineWidth 3s ease-in-out infinite;
        }

        @keyframes lineWidth {
            0%, 100% { width: 200px; }
            50% { width: 300px; }
        }

        .hero-subtitle {
            font-size: 1.8rem;
            margin-bottom: 3rem;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 300;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            animation: subtitleFade 1s ease-out;
        }

        @keyframes subtitleFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .search-container {
            position: relative;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2),
                        0 0 50px rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform: translateY(0);
            transition: all 0.3s ease;
            animation: containerFloat 1s ease-out;
            overflow: hidden;
        }

        /* Ticket Design Elements */
        .search-container::before,
        .search-container::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.2);
        }

        .search-container::before {
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .search-container::after {
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .search-form {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
            width: 100%;
            padding: 15px;
            backdrop-filter: blur(5px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: white;
            font-weight: 500;
            font-size: 16px;
        }

        .form-group select {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .form-group select option {
            background: var(--secondary-color);
            color: white;
            padding: 10px;
        }

        .search-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 20px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #FFD700;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.2);
        }

        .search-btn {
            width: 100%;
            padding: 15px;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: linear-gradient(45deg, #FF6B35, #FFB566);
            border: none;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
            position: relative;
            overflow: hidden;
        }

        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 107, 53, 0.4);
            background: linear-gradient(45deg, #FFB566, #FF6B35);
        }

        .search-btn i {
            margin-right: 10px;
            font-size: 1.3rem;
        }

        /* Flying Airplane Animation */
        @keyframes flyPlane {
            0% {
                transform: translate(-100%, 50px) rotate(15deg) scale(0.5);
                opacity: 0;
            }
            10% {
                opacity: 1;
                transform: translate(-80%, 40px) rotate(5deg) scale(0.6);
            }
            45% {
                transform: translate(0%, 0px) rotate(0deg) scale(0.8);
            }
            55% {
                transform: translate(20%, -20px) rotate(-5deg) scale(0.7);
            }
            90% {
                opacity: 1;
                transform: translate(80%, -40px) rotate(-10deg) scale(0.6);
            }
            100% {
                transform: translate(100%, -50px) rotate(-15deg) scale(0.5);
                opacity: 0;
            }
        }

        .flying-plane {
            position: absolute;
            width: 80px;
            height: 80px;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48cGF0aCBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDUpIiBkPSJNNDgwIDI1NkwyNDAgNDMyVjMzNkgxNjBRMTQwLjggMzM2IDEyOCAzMjMuMlExMTUuMiAzMTAuNCAxMTUuMiAyOTEuMlExMTUuMiAyNzIgMTI4IDI1OS4yUTE0MC44IDI0Ni40IDE2MCAyNDZIMjQwVjE1MkwyNDAgODBMNDgwIDI1NloiLz48L3N2Zz4=') no-repeat center center;
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
            z-index: 1;
            opacity: 0.05;
            transform: scale(1.5);
        }

        .flying-plane:nth-child(1) {
            top: 20%;
            animation: flyPlane 15s linear infinite;
        }

        .flying-plane:nth-child(2) {
            top: 40%;
            animation: flyPlane 18s linear infinite 3s;
        }

        .flying-plane:nth-child(3) {
            top: 60%;
            animation: flyPlane 20s linear infinite 6s;
        }

        .flying-plane:nth-child(4) {
            top: 30%;
            animation: flyPlane 22s linear infinite 9s;
        }

        .flying-plane:nth-child(5) {
            top: 50%;
            animation: flyPlane 25s linear infinite 12s;
        }

        /* Floating Cloud Animation */
        @keyframes floatCloud {
            0% {
                transform: translateX(-100%) translateY(0);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            90% {
                opacity: 0.3;
            }
            100% {
                transform: translateX(100vw) translateY(20px);
                opacity: 0;
            }
        }

        .floating-cloud {
            position: absolute;
            width: 120px;
            height: 60px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50px;
            filter: blur(8px);
            z-index: 0;
            opacity: 0.08;
            backdrop-filter: blur(4px);
        }

        .floating-cloud::before,
        .floating-cloud::after {
            content: '';
            position: absolute;
            background: inherit;
            border-radius: inherit;
        }

        .floating-cloud::before {
            width: 80px;
            height: 50px;
            top: -25px;
            left: 25px;
        }

        .floating-cloud::after {
            width: 70px;
            height: 40px;
            top: -15px;
            right: 25px;
        }

        /* Cloud Variations */
        .floating-cloud.small {
            width: 80px;
            height: 40px;
            opacity: 0.07;
        }

        .floating-cloud.small::before {
            width: 50px;
            height: 30px;
            top: -15px;
            left: 15px;
        }

        .floating-cloud.small::after {
            width: 40px;
            height: 25px;
            top: -10px;
            right: 15px;
        }

        .floating-cloud.large {
            width: 160px;
            height: 80px;
            opacity: 0.05;
        }

        .floating-cloud.large::before {
            width: 100px;
            height: 60px;
            top: -30px;
            left: 30px;
        }

        .floating-cloud.large::after {
            width: 90px;
            height: 50px;
            top: -20px;
            right: 30px;
        }

        /* Cloud Positions and Animations */
        .floating-cloud:nth-child(1) {
            top: 15%;
            animation: floatCloud 30s linear infinite;
        }

        .floating-cloud:nth-child(2) {
            top: 35%;
            animation: floatCloud 35s linear infinite 5s;
        }

        .floating-cloud:nth-child(3) {
            top: 55%;
            animation: floatCloud 40s linear infinite 10s;
        }

        .floating-cloud:nth-child(4) {
            top: 25%;
            animation: floatCloud 45s linear infinite 15s;
        }

        .floating-cloud:nth-child(5) {
            top: 45%;
            animation: floatCloud 50s linear infinite 20s;
        }

        .floating-cloud:nth-child(6) {
            top: 65%;
            animation: floatCloud 38s linear infinite 8s;
        }

        .floating-cloud:nth-child(7) {
            top: 75%;
            animation: floatCloud 42s linear infinite 12s;
        }

        .floating-cloud:nth-child(8) {
            top: 85%;
            animation: floatCloud 47s linear infinite 17s;
        }

        /* Features Section */
        .features {
            padding: 6rem 0;
            background: linear-gradient(135deg, rgba(0, 78, 137, 0.1), rgba(255, 107, 53, 0.1));
            position: relative;
            overflow: hidden;
        }

        .features-grid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
        }

        .feature-card {
            padding: 3rem 2rem;
            border-radius: 30px;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.5s ease;
        }

        .feature-card:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--accent-color);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Modern Footer */
        .footer {
            background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
            color: #fff;
            padding: 6rem 0 2rem;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(255, 107, 53, 0.15), transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(0, 78, 137, 0.15), transparent 40%);
            pointer-events: none;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 4rem;
            position: relative;
            z-index: 1;
        }

        .footer-section h3 {
            color: #FF6B35;
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 1rem;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #FF6B35, transparent);
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 1rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            padding: 0.3rem 0;
        }

        .footer-links a:hover {
            color: #FF6B35;
            transform: translateX(10px);
        }

        .social-links {
            display: flex;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .social-links a {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .social-links a:hover {
            background: #FF6B35;
            transform: translateY(-5px) rotate(10deg);
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        }

        .footer-bottom {
            margin-top: 4rem;
            padding-top: 2rem;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* Plane Shadow Animation */
        .plane-shadow {
            position: fixed;
            width: 150px;
            height: 150px;
            background: rgba(0, 0, 0, 0.2);
            mask: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21,16V14L13,9V3.5A1.5,1.5,0,0,0,11.5,2A1.5,1.5,0,0,0,10,3.5V9L2,14V16L10,13.5V19L8,20.5V22L11.5,21L15,22V20.5L13,19V13.5L21,16Z"/></svg>');
            -webkit-mask: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21,16V14L13,9V3.5A1.5,1.5,0,0,0,11.5,2A1.5,1.5,0,0,0,10,3.5V9L2,14V16L10,13.5V19L8,20.5V22L11.5,21L15,22V20.5L13,19V13.5L21,16Z"/></svg>');
            mask-size: contain;
            -webkit-mask-size: contain;
            mask-repeat: no-repeat;
            -webkit-mask-repeat: no-repeat;
            pointer-events: none;
            z-index: 1;
            filter: blur(2px);
            animation: flyPlane 15s linear infinite;
        }

        @keyframes flyPlane {
            0% {
                transform: translate(-100vw, 100px) rotate(25deg);
                opacity: 0;
            }
            10% {
                opacity: 0.2;
            }
            90% {
                opacity: 0.2;
            }
            100% {
                transform: translate(100vw, 500px) rotate(25deg);
                opacity: 0;
            }
        }

        .plane-shadow:nth-child(1) {
            top: 10%;
            animation-delay: 0s;
            transform-origin: center;
            animation-duration: 20s;
        }

        .plane-shadow:nth-child(2) {
            top: 30%;
            animation-delay: -5s;
            transform-origin: center;
            animation-duration: 25s;
        }

        .plane-shadow:nth-child(3) {
            top: 50%;
            animation-delay: -10s;
            transform-origin: center;
            animation-duration: 22s;
        }

        .plane-shadow:nth-child(4) {
            top: 70%;
            animation-delay: -15s;
            transform-origin: center;
            animation-duration: 28s;
        }

        .plane-shadow:nth-child(5) {
            top: 20%;
            animation-delay: -8s;
            transform-origin: center;
            animation-duration: 24s;
        }

        .plane-shadow:nth-child(6) {
            top: 40%;
            animation-delay: -12s;
            transform-origin: center;
            animation-duration: 26s;
        }
        /* Multiple planes with different speeds and paths */
        .plane-shadow:nth-child(2) {
            animation-delay: -5s;
            top: 20%;
            opacity: 0.08;
        }

        .plane-shadow:nth-child(3) {
            animation-delay: -10s;
            top: 40%;
            opacity: 0.06;
        }

        .plane-shadow:nth-child(4) {
            animation-delay: -15s;
            top: 60%;
            opacity: 0.04;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.3rem;
            }

            .search-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .features-grid {
                gap: 2rem;
            }

            .footer-content {
                gap: 2rem;
            }

            .nav-content {
                padding: 0 1rem;
            }

            .logo {
                font-size: 1.5rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .nav-btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }

            .profile-button span {
                display: none;
            }
        }

        .search-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }

        .form-group {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .form-group label {
            display: inline-block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--light-text);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .form-group:hover label {
            transform: translateY(-2px);
            color: #FFD700;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-group select {
            padding-right: 1rem;
            background-image: none;
        }

        .form-group select option {
            background: #004E89;
            color: white;
            padding: 0.8rem;
            font-size: 0.9rem;
        }

        .form-group select:hover,
        .form-group input:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(255, 107, 53, 0.1);
        }

        .search-btn {
            width: 100%;
            padding: 0.8rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
            border: none;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.2);
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.3);
        }

        .search-btn i {
            margin-right: 10px;
            font-size: 1.3rem;
        }
    </style>
</head>
<body>
    <!-- Plane Shadows -->
    <div class="plane-shadow"></div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-content">
            <a href="index.php" class="logo">AirlineBooking</a>
            <div class="nav-links">
                <?php if(isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true): ?>
                    <div class="user-profile">
                        <button class="profile-button">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </button>
                        <div class="profile-dropdown">
                            <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
                            <a href="my_bookings.php"><i class="fas fa-ticket-alt"></i> Pesanan Saya</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="nav-btn login-btn">Masuk</a>
                    <a href="register.php" class="nav-btn register-btn">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <!-- Animated Background Elements -->
        <div class="flying-plane"></div>
        <div class="flying-plane"></div>
        <div class="flying-plane"></div>
        <div class="flying-plane"></div>
        <div class="flying-plane"></div>

        <!-- Floating Clouds -->
        <div class="floating-cloud large"></div>
        <div class="floating-cloud small"></div>
        <div class="floating-cloud"></div>
        <div class="floating-cloud large"></div>
        <div class="floating-cloud small"></div>
        <div class="floating-cloud"></div>
        <div class="floating-cloud large"></div>
        <div class="floating-cloud small"></div>
        
        <div class="hero-content">
            <h1 class="hero-title">Temukan Penerbangan Terbaik</h1>
            <p class="hero-subtitle">Jelajahi Dunia Tanpa Batas</p>
            
            <div class="search-container">
                <form class="search-form" action="search_results.php" method="GET">
                    <div class="form-group">
                        <label for="from"><i class="fas fa-plane-departure"></i> Kota Asal</label>
                        <select name="from" id="from" required>
                            <option value="">Pilih kota asal</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city['kota']); ?>"><?php echo htmlspecialchars($city['kota']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="to"><i class="fas fa-plane-arrival"></i> Kota Tujuan</label>
                        <select name="to" id="to" required>
                            <option value="">Pilih kota tujuan</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city['kota']); ?>"><?php echo htmlspecialchars($city['kota']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="class"><i class="fas fa-chair"></i> Kelas Penerbangan</label>
                        <select name="class" id="class" required>
                            <option value="">Pilih kelas</option>
                            <option value="economy">Ekonomi</option>
                            <option value="business">Bisnis</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date"><i class="fas fa-calendar"></i> Tanggal Keberangkatan</label>
                        <input type="date" name="date" id="date" required>
                    </div>

                    <div class="form-group">
                        <label for="passengers"><i class="fas fa-users"></i> Jumlah Penumpang</label>
                        <select name="passengers" id="passengers" required>
                            <option value="">Pilih Jumlah</option>
                            <option value="1">1 Orang</option>
                            <option value="2">2 Orang</option>
                            <option value="3">3 Orang</option>
                            <option value="4">4 Orang</option>
                            <option value="5">5 Orang</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i> Cari Penerbangan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-plane"></i>
                <h3>Penerbangan Terjadwal</h3>
                <p>Jadwal penerbangan yang teratur dan tepat waktu ke berbagai destinasi</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-tag"></i>
                <h3>Harga Terbaik</h3>
                <p>Dapatkan harga tiket pesawat terbaik dengan berbagai pilihan maskapai</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-headset"></i>
                <h3>Layanan 24/7</h3>
                <p>Dukungan pelanggan siap membantu Anda kapan saja</p>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Tentang Kami</h3>
                <p>AirlineBooking adalah platform pemesanan tiket pesawat terpercaya dengan berbagai pilihan maskapai dan rute penerbangan terbaik untuk perjalanan Anda.</p>
                <div class="social-links">
                    <a href="#" class="floating"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="floating" style="animation-delay: 0.1s"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="floating" style="animation-delay: 0.2s"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="floating" style="animation-delay: 0.3s"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Layanan</h3>
                <ul class="footer-links">
                    <li><a href="#">Pemesanan Tiket</a></li>
                    <li><a href="#">Cek Status Penerbangan</a></li>
                    <li><a href="#">Program Loyalitas</a></li>
                    <li><a href="#">Paket Wisata</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Informasi</h3>
                <ul class="footer-links">
                    <li><a href="#">Syarat & Ketentuan</a></li>
                    <li><a href="#">Kebijakan Privasi</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Hubungi Kami</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Kontak</h3>
                <p><i class="fas fa-phone"></i> +62 123 4567 890</p>
                <p><i class="fas fa-envelope"></i> info@airlinebooking.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Jl. Pesawat No. 123, Jakarta</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 AirlineBooking. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Set minimum date for date picker to today
        const dateInput = document.getElementById('date');
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
    </script>

    <script>
        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>