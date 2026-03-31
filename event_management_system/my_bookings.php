<?php
require_once "config.php";
require_login();

$user_id = (int)$_SESSION["user_id"];
$paidInfo = isset($_GET["paid"]);

$stmt = $conn->prepare("
  SELECT b.id AS booking_id, b.payment_status, b.booking_date,
         e.id AS event_id, e.title, e.date, e.location, e.price
  FROM bookings b
  JOIN events e ON e.id = b.event_id
  WHERE b.user_id = ?
  ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include "partials/header.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
  <div>
    <h2 class="mb-1 text-white">My Bookings</h2>
    <div class="muted">Your ticket history and QR codes.</div>
  </div>
  <a class="btn btn-outline-light" href="index.php">Browse Events</a>
</div>

<?php if ($paidInfo): ?>
  <div class="alert alert-success">Payment successful. Your ticket QR is ready.</div>
<?php endif; ?>

<?php if (!$bookings): ?>
  <div class="alert alert-secondary">No bookings yet.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($bookings as $b): ?>
      <div class="col-md-6">
        <div class="card event-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div>
                <h5 class="mb-1"><?= e($b["title"]) ?></h5>
                <div class="event-meta">
                  <div><strong>Date:</strong> <?= e($b["date"]) ?> | <strong>Location:</strong> <?= e($b["location"]) ?></div>
                  <div><strong>Booked:</strong> <?= e($b["booking_date"]) ?></div>
                </div>
              </div>
              <div class="text-end">
                <?php if ($b["payment_status"] === "paid"): ?>
                  <span class="badge text-bg-success">PAID</span>
                <?php else: ?>
                  <span class="badge text-bg-warning">PENDING</span>
                <?php endif; ?>
                <div class="mt-2 muted">₹<?= e(number_format((float)$b["price"], 2)) ?></div>
              </div>
            </div>

            <div class="mt-3 d-flex flex-wrap gap-2">
              <?php if ($b["payment_status"] !== "paid"): ?>
                <a class="btn btn-primary" href="book.php?event_id=<?= (int)$b["event_id"] ?>">Pay Now</a>
              <?php else: ?>
                <a class="btn btn-outline-light"
                   target="_blank"
                   href="<?= e(FLASK_BASE_URL) ?>/qr?booking_id=<?= (int)$b["booking_id"] ?>&user_id=<?= (int)$user_id ?>">
                  View QR Ticket
                </a>
                <a class="btn btn-outline-light"
                   target="_blank"
                   href="<?= e(FLASK_BASE_URL) ?>/qr/download?booking_id=<?= (int)$b["booking_id"] ?>&user_id=<?= (int)$user_id ?>">
                  Download QR
                </a>
              <?php endif; ?>
            </div>

            <div class="small muted mt-2">
              Booking ID: <?= (int)$b["booking_id"] ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include "partials/footer.php"; ?>

