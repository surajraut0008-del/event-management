<?php
require_once "config.php";
require_login();

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["ok" => false, "error" => "Invalid request"]);
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$booking_id = (int)($_POST["booking_id"] ?? 0);
$order_id = trim($_POST["razorpay_order_id"] ?? "");
$payment_id = trim($_POST["razorpay_payment_id"] ?? "");
$signature = trim($_POST["razorpay_signature"] ?? "");

if ($booking_id <= 0 || $order_id === "" || $payment_id === "" || $signature === "") {
    echo json_encode(["ok" => false, "error" => "Missing payment data"]);
    exit;
}

// Check booking belongs to user and order_id matches
$stmt = $conn->prepare("SELECT razorpay_order_id, payment_status FROM bookings WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo json_encode(["ok" => false, "error" => "Booking not found"]);
    exit;
}
if (($booking["razorpay_order_id"] ?? "") !== $order_id) {
    echo json_encode(["ok" => false, "error" => "Order mismatch"]);
    exit;
}
if (($booking["payment_status"] ?? "") === "paid") {
    echo json_encode(["ok" => true]);
    exit;
}

// Verify signature
$payload = $order_id . "|" . $payment_id;
$expected = hash_hmac("sha256", $payload, RAZORPAY_KEY_SECRET);
if (!hash_equals($expected, $signature)) {
    echo json_encode(["ok" => false, "error" => "Invalid signature"]);
    exit;
}

// Update booking as paid
$stmt = $conn->prepare("UPDATE bookings SET payment_status='paid', razorpay_payment_id=?, razorpay_signature=? WHERE id=? AND user_id=?");
$stmt->bind_param("ssii", $payment_id, $signature, $booking_id, $user_id);
$stmt->execute();
$stmt->close();

echo json_encode(["ok" => true]);

