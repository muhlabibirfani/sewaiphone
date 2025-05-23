<?php
session_start();

// Database Configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'fanzzervice';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// Timezone setting
date_default_timezone_set('Asia/Jakarta');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek jika user sudah login dan arahkan ke halaman sesuai role
$currentFile = basename($_SERVER['PHP_SELF']);

if (!in_array($currentFile, ['login.php'])) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
        if ($_SESSION['user_role'] === 'admin' && strpos($_SERVER['PHP_SELF'], '/admin/') === false) {
            header("Location: admin/dashboard.php");
            exit;
        } elseif ($_SESSION['user_role'] === 'user' && strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
            header("Location: index.php");
            exit;
        }
    }
}

// Admin Authentication Check
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Jika file di folder admin, tapi bukan admin, redirect
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false && !isAdmin()) {
    header("Location: ../login.php");
    exit;
}

// Helper function to escape data
function clean_input($data) {
    global $conn;
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)));
}
?>
