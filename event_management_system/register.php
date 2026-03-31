<?php
require_once "config.php";

if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

$errors = [];
$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if ($name === "" || strlen($name) < 2) $errors[] = "Name must be at least 2 characters.";
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered. Please login.";
        }
        $stmt->close();
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = "user";

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $name, $email, $hash, $role);
        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}

include "partials/header.php";
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card event-card p-3">
      <div class="card-body">
        <h3 class="mb-3">Create Account</h3>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="<?= e($name) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="<?= e($email) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
            <div class="form-text muted">Minimum 6 characters.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input class="form-control" type="password" name="confirm_password" required>
          </div>
          <button class="btn btn-primary w-100">Register</button>
          <div class="mt-3 text-center">
            <span class="muted">Already have an account?</span>
            <a href="login.php">Login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include "partials/footer.php"; ?>

