<?php
require_once "config.php";

$q = trim($_GET["q"] ?? "");
$location = trim($_GET["location"] ?? "");
$min_price = trim($_GET["min_price"] ?? "");
$max_price = trim($_GET["max_price"] ?? "");

// Build safe dynamic query
$sql = "SELECT id, title, description, date, location, price FROM events WHERE 1=1";
$params = [];
$types = "";

if ($q !== "") {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $like = "%" . $q . "%";
    $params[] = $like; $types .= "s";
    $params[] = $like; $types .= "s";
}
if ($location !== "") {
    $sql .= " AND location LIKE ?";
    $params[] = "%" . $location . "%"; $types .= "s";
}
if ($min_price !== "" && is_numeric($min_price)) {
    $sql .= " AND price >= ?";
    $params[] = (float)$min_price; $types .= "d";
}
if ($max_price !== "" && is_numeric($max_price)) {
    $sql .= " AND price <= ?";
    $params[] = (float)$max_price; $types .= "d";
}

$sql .= " ORDER BY date ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include "partials/header.php";
?>

<div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-3">
  <div>
    <h2 class="mb-1 text-white">Upcoming Events</h2>
    <div class="muted">Search, filter, and book your tickets.</div>
  </div>
  <?php if (!is_logged_in()): ?>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-light" href="login.php">Login</a>
      <a class="btn btn-primary" href="register.php">Register</a>
    </div>
  <?php endif; ?>
</div>

<div class="card event-card mb-4">
  <div class="card-body">
    <form class="row g-2" method="get">
      <div class="col-md-5">
        <input class="form-control" name="q" placeholder="Search events (title/description)" value="<?= e($q) ?>">
      </div>
      <div class="col-md-3">
        <input class="form-control" name="location" placeholder="Location" value="<?= e($location) ?>">
      </div>
      <div class="col-md-2">
        <input class="form-control" name="min_price" placeholder="Min ₹" value="<?= e($min_price) ?>">
      </div>
      <div class="col-md-2">
        <input class="form-control" name="max_price" placeholder="Max ₹" value="<?= e($max_price) ?>">
      </div>
      <div class="col-12 d-flex gap-2 mt-2">
        <button class="btn btn-primary">Search</button>
        <a class="btn btn-outline-light" href="index.php">Reset</a>
      </div>
    </form>
  </div>
</div>

<?php if (!$events): ?>
  <div class="alert alert-secondary">No events found. Try adjusting your filters.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($events as $ev): ?>
      <div class="col-md-4">
        <div class="card event-card h-100">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= e($ev["title"]) ?></h5>
            <div class="event-meta mb-2">
              <div><strong>Date:</strong> <?= e($ev["date"]) ?></div>
              <div><strong>Location:</strong> <?= e($ev["location"]) ?></div>
              <div><strong>Price:</strong> ₹<?= e(number_format((float)$ev["price"], 2)) ?></div>
            </div>
            <p class="card-text muted" style="flex:1;">
              <?= e(mb_strimwidth($ev["description"], 0, 120, "...")) ?>
            </p>
            <div class="d-flex gap-2">
              <?php if (is_logged_in()): ?>
                <a class="btn btn-primary w-100" href="book.php?event_id=<?= (int)$ev["id"] ?>">Book</a>
              <?php else: ?>
                <a class="btn btn-primary w-100" href="login.php">Login to Book</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include "partials/footer.php"; ?>

