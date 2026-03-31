<?php
// Core configuration (DB + sessions + helpers)
// Update DB credentials if your XAMPP differs.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Database ----
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";        // XAMPP default is empty
$DB_NAME = "event_db";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ---- Razorpay (Test Mode) ----
// Put your Razorpay Test Key ID and Secret here.
define("RAZORPAY_KEY_ID", "rzp_test_your_key_id");
define("RAZORPAY_KEY_SECRET", "your_key_secret");

// ---- Flask service ----
define("FLASK_BASE_URL", "http://127.0.0.1:5000");

// ---- Helpers ----
function is_logged_in(): bool {
    return isset($_SESSION["user_id"]);
}

function require_login(): void {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function is_admin(): bool {
    return isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
}

function require_admin(): void {
    require_login();
    if (!is_admin()) {
        header("Location: index.php");
        exit;
    }
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}

?>

