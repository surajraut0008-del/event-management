<?php
// Navbar expects config.php already loaded (via header.php).
$loggedIn = is_logged_in();
$role = $_SESSION["role"] ?? "guest";
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="index.php">EventMS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">Events</a></li>
        <?php if ($loggedIn): ?>
          <li class="nav-item"><a class="nav-link" href="my_bookings.php">My Bookings</a></li>
        <?php endif; ?>
        <?php if ($loggedIn && $role === "admin"): ?>
          <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Admin</a></li>
          <li class="nav-item"><a class="nav-link" href="add_event.php">Manage Events</a></li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if (!$loggedIn): ?>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <?php else: ?>
          <li class="nav-item">
            <span class="navbar-text me-2">Hi, <?= e($_SESSION["name"] ?? "User") ?></span>
          </li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

