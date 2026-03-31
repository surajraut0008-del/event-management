<?php
require_once "config.php";
require_admin();

$errors = [];
$success = "";

// Handle delete
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: add_event.php?msg=deleted");
        exit;
    }
}

// Load event for edit
$edit_id = (int)($_GET["edit"] ?? 0);
$form = [
    "title" => "",
    "description" => "",
    "date" => "",
    "location" => "",
    "price" => "0.00"
];

if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT title, description, date, location, price FROM events WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $form = [
            "title" => $row["title"],
            "description" => $row["description"],
            "date" => $row["date"],
            "location" => $row["location"],
            "price" => (string)$row["price"]
        ];
    } else {
        $errors[] = "Event not found.";
        $edit_id = 0;
    }
}

// Handle add/update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $date = trim($_POST["date"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $price = trim($_POST["price"] ?? "0");

    $form = compact("title", "description", "date", "location", "price");

    if ($title === "" || strlen($title) < 3) $errors[] = "Title must be at least 3 characters.";
    if ($description === "" || strlen($description) < 10) $errors[] = "Description must be at least 10 characters.";
    if ($date === "") $errors[] = "Please select a date.";
    if ($location === "" || strlen($location) < 2) $errors[] = "Location is required.";
    if ($price === "" || !is_numeric($price) || (float)$price < 0) $errors[] = "Price must be a valid number (>= 0).";

    if (!$errors) {
        if (isset($_POST["event_id"]) && (int)$_POST["event_id"] > 0) {
            $id = (int)$_POST["event_id"];
            $stmt = $conn->prepare("UPDATE events SET title=?, description=?, date=?, location=?, price=? WHERE id=?");
            $p = (float)$price;
            $stmt->bind_param("ssssdi", $title, $description, $date, $location, $p, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: add_event.php?msg=updated");
            exit;
        } else {
            $stmt = $conn->prepare("INSERT INTO events (title, description, date, location, price) VALUES (?,?,?,?,?)");
            $p = (float)$price;
            $stmt->bind_param("ssssd", $title, $description, $date, $location, $p);
            $stmt->execute();
            $stmt->close();
            header("Location: add_event.php?msg=added");
            exit;
        }
    }
}

if (isset($_GET["msg"])) {
    if ($_GET["msg"] === "added") $success = "Event added successfully.";
    if ($_GET["msg"] === "updated") $success = "Event updated successfully.";
    if ($_GET["msg"] === "deleted") $success = "Event deleted successfully.";
}

// Events list
$res = $conn->query("SELECT id, title, date, location, price FROM events ORDER BY date ASC");
$events = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

include "partials/header.php";
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
  <div>
    <h2 class="mb-1 text-white">Manage Events</h2>
    <div class="muted">Add, edit, or delete events.</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-light" href="admin_dashboard.php">Back to Dashboard</a>
  </div>
</div>

<?php if ($success): ?>
  <div class="alert alert-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?>
        <li><?= e($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-md-5">
    <div class="card event-card">
      <div class="card-body">
        <h5 class="mb-3"><?= $edit_id > 0 ? "Edit Event" : "Add New Event" ?></h5>
        <form method="post" novalidate>
          <?php if ($edit_id > 0): ?>
            <input type="hidden" name="event_id" value="<?= (int)$edit_id ?>">
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" value="<?= e($form["title"]) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4" required><?= e($form["description"]) ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input class="form-control" type="date" name="date" value="<?= e($form["date"]) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Location</label>
            <input class="form-control" name="location" value="<?= e($form["location"]) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Price (₹)</label>
            <input class="form-control" name="price" value="<?= e($form["price"]) ?>" required>
          </div>
          <button class="btn btn-primary w-100"><?= $edit_id > 0 ? "Update Event" : "Add Event" ?></button>
          <?php if ($edit_id > 0): ?>
            <a class="btn btn-outline-light w-100 mt-2" href="add_event.php">Cancel Edit</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card event-card">
      <div class="card-body">
        <h5 class="mb-3">All Events</h5>
        <div class="table-responsive">
          <table class="table table-dark table-striped align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Date</th>
                <th>Location</th>
                <th>Price</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$events): ?>
                <tr><td colspan="6" class="muted">No events yet.</td></tr>
              <?php else: ?>
                <?php foreach ($events as $ev): ?>
                  <tr>
                    <td><?= (int)$ev["id"] ?></td>
                    <td class="fw-semibold"><?= e($ev["title"]) ?></td>
                    <td><?= e($ev["date"]) ?></td>
                    <td><?= e($ev["location"]) ?></td>
                    <td>₹<?= e(number_format((float)$ev["price"], 2)) ?></td>
                    <td class="d-flex gap-2">
                      <a class="btn btn-sm btn-outline-light" href="add_event.php?edit=<?= (int)$ev["id"] ?>">Edit</a>
                      <a class="btn btn-sm btn-outline-danger"
                         href="add_event.php?delete=<?= (int)$ev["id"] ?>"
                         onclick="return confirm('Delete this event?');">
                        Delete
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "partials/footer.php"; ?>

