<?php
require_once "config.php";
require_admin();

// Stats
$total_users = (int)($conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()["c"] ?? 0);
$total_events = (int)($conn->query("SELECT COUNT(*) AS c FROM events")->fetch_assoc()["c"] ?? 0);
$total_bookings = (int)($conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()["c"] ?? 0);

// Latest bookings
$res = $conn->query("
  SELECT b.id AS booking_id, b.payment_status, b.booking_date,
         u.name AS user_name, u.email,
         e.title AS event_title, e.date AS event_date, e.location
  FROM bookings b
  JOIN users u ON u.id = b.user_id
  JOIN events e ON e.id = b.event_id
  ORDER BY b.booking_date DESC
  LIMIT 50
");
$bookings = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

include "partials/header.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
  <div>
    <h2 class="mb-1 text-white">Admin Dashboard</h2>
    <div class="muted">Overview and bookings.</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-primary" href="add_event.php">Manage Events</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card event-card">
      <div class="card-body">
        <div class="muted">Total Users</div>
        <div class="display-6 fw-semibold"><?= $total_users ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card event-card">
      <div class="card-body">
        <div class="muted">Total Events</div>
        <div class="display-6 fw-semibold"><?= $total_events ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card event-card">
      <div class="card-body">
        <div class="muted">Total Bookings</div>
        <div class="display-6 fw-semibold"><?= $total_bookings ?></div>
      </div>
    </div>
  </div>
</div>

<div class="card event-card">
  <div class="card-body">
    <h5 class="mb-3">Recent Bookings</h5>
    <div class="table-responsive">
      <table class="table table-dark table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Event</th>
            <th>Event Date</th>
            <th>Location</th>
            <th>Status</th>
            <th>Booked At</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$bookings): ?>
            <tr><td colspan="7" class="muted">No bookings found.</td></tr>
          <?php else: ?>
            <?php foreach ($bookings as $b): ?>
              <tr>
                <td><?= (int)$b["booking_id"] ?></td>
                <td>
                  <div class="fw-semibold"><?= e($b["user_name"]) ?></div>
                  <div class="small muted"><?= e($b["email"]) ?></div>
                </td>
                <td><?= e($b["event_title"]) ?></td>
                <td><?= e($b["event_date"]) ?></td>
                <td><?= e($b["location"]) ?></td>
                <td>
                  <?php if ($b["payment_status"] === "paid"): ?>
                    <span class="badge text-bg-success">paid</span>
                  <?php else: ?>
                    <span class="badge text-bg-warning">pending</span>
                  <?php endif; ?>
                </td>
                <td><?= e($b["booking_date"]) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include "partials/footer.php"; ?>

