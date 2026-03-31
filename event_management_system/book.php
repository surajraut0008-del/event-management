<?php
require_once "config.php";
require_login();

$user_id = (int)$_SESSION["user_id"];
$event_id = (int)($_GET["event_id"] ?? $_POST["event_id"] ?? 0);

if ($event_id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch event
$stmt = $conn->prepare("SELECT id, title, description, date, location, price FROM events WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$event) {
    header("Location: index.php");
    exit;
}

$errors = [];
$booking_id = null;
$order_id = null;

function razorpay_create_order(int $amount_paise, string $receipt): ?string {
    $payload = json_encode([
        "amount" => $amount_paise,
        "currency" => "INR",
        "receipt" => $receipt,
        "payment_capture" => 1
    ]);

    $ch = curl_init("https://api.razorpay.com/v1/orders");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ":" . RAZORPAY_KEY_SECRET);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http >= 200 && $http < 300) {
        $data = json_decode($resp, true);
        return $data["id"] ?? null;
    }
    return null;
}

// Create booking + order
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_booking"])) {
    $price = (float)$event["price"];
    $amount_paise = (int)round($price * 100);
    if ($amount_paise < 0) $amount_paise = 0;

    // Insert booking as pending
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, event_id, payment_status) VALUES (?,?, 'pending')");
    $stmt->bind_param("ii", $user_id, $event_id);
    if (!$stmt->execute()) {
        $errors[] = "Could not create booking. Please try again.";
    } else {
        $booking_id = (int)$stmt->insert_id;
    }
    $stmt->close();

    if (!$errors && $amount_paise > 0) {
        $receipt = "booking_" . $booking_id;
        $order_id = razorpay_create_order($amount_paise, $receipt);
        if (!$order_id) {
            $errors[] = "Razorpay order creation failed. Check keys in config.php.";
        } else {
            $stmt = $conn->prepare("UPDATE bookings SET razorpay_order_id = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sii", $order_id, $booking_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Free events: mark paid and redirect
    if (!$errors && $amount_paise === 0) {
        $stmt = $conn->prepare("UPDATE bookings SET payment_status='paid' WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $stmt->close();

        header("Location: my_bookings.php?paid=1");
        exit;
    }
}

include "partials/header.php";
?>

<div class="row justify-content-center">
  <div class="col-md-9">
    <div class="card event-card">
      <div class="card-body">
        <h3 class="mb-1"><?= e($event["title"]) ?></h3>
        <div class="event-meta mb-3">
          <span class="me-3"><strong>Date:</strong> <?= e($event["date"]) ?></span>
          <span class="me-3"><strong>Location:</strong> <?= e($event["location"]) ?></span>
          <span><strong>Price:</strong> ₹<?= e(number_format((float)$event["price"], 2)) ?></span>
        </div>
        <p class="muted"><?= e($event["description"]) ?></p>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if (!$booking_id && !$order_id): ?>
          <form method="post">
            <input type="hidden" name="event_id" value="<?= (int)$event_id ?>">
            <button class="btn btn-primary" name="create_booking" value="1">Proceed to Booking</button>
            <a class="btn btn-outline-light" href="index.php">Back</a>
          </form>
        <?php endif; ?>

        <?php if ($booking_id && $order_id): ?>
          <div class="alert alert-info">
            Booking created. Complete payment to confirm your ticket.
          </div>

          <button id="payBtn" class="btn btn-success">Pay with Razorpay</button>
          <a class="btn btn-outline-light ms-2" href="my_bookings.php">My Bookings</a>

          <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
          <script>
            const options = {
              key: <?= json_encode(RAZORPAY_KEY_ID) ?>,
              amount: <?= (int)round(((float)$event["price"]) * 100) ?>,
              currency: "INR",
              name: "EventMS",
              description: <?= json_encode($event["title"]) ?>,
              order_id: <?= json_encode($order_id) ?>,
              handler: async function (response) {
                const form = new FormData();
                form.append("booking_id", <?= (int)$booking_id ?>);
                form.append("razorpay_order_id", response.razorpay_order_id);
                form.append("razorpay_payment_id", response.razorpay_payment_id);
                form.append("razorpay_signature", response.razorpay_signature);

                const res = await fetch("payment_verify.php", { method: "POST", body: form });
                const data = await res.json();
                if (data.ok) {
                  window.location.href = "my_bookings.php?paid=1";
                } else {
                  alert(data.error || "Payment verification failed.");
                }
              },
              theme: { color: "#22c55e" }
            };

            document.getElementById("payBtn").addEventListener("click", () => {
              const rzp = new Razorpay(options);
              rzp.open();
            });
          </script>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include "partials/footer.php"; ?>

