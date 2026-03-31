<?php
require_once "config.php";

if (is_logged_in()) {
    header("Location: index.php");
    exit;
}

$errors = [];
$email = "";
$info = "";

if (isset($_GET["registered"])) {
    $info = "Registration successful. Please login.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email.";
    if ($password === "") $errors[] = "Please enter your password.";

    if (!$errors) {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] === "admin") {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}

include "partials/header.php";
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card event-card p-3">
      <div class="card-body">
        <h3 class="mb-3">Login</h3>

        <?php if ($info): ?>
          <div class="alert alert-success"><?= e($info) ?></div>
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

        <form method="post" novalidate>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" value="<?= e($email) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <button class="btn btn-primary w-100">Login</button>
          <div class="mt-3 text-center">
            <span class="muted">No account?</span>
            <a href="register.php">Register</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include "partials/footer.php"; ?>

