<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();

$isAdminArea = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false);
$userLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? null;

// hitung cart (simple – dari session)
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
    $cartCount += (int)$qty;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Slashers.com</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<?php if ($isAdminArea): ?>
  <!-- ================= ADMIN HEADER ================= -->
  <div class="admin-topbar d-flex justify-content-between align-items-center">
    <strong>Slasher Admin</strong>
    <div>
      <a href="<?= BASE_URL ?>" class="btn btn-outline-light btn-sm me-2">Lihat Website</a>
      <a href="<?= BASE_URL ?>logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
  <main class="admin-content container">

<?php else: ?>
  <!-- ================= PUBLIC HEADER ================= -->
  <header class="site-header sticky-top">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">

        <!-- LOGO -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>">
          <img src="<?= BASE_URL ?>assets/images/logo_slasher.png" alt="Slasher" height="36">
          <strong>Slashers</strong>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">

          <!-- SEARCH -->
          <form class="d-flex mx-lg-4 my-3 my-lg-0" action="<?= BASE_URL ?>browse.php" method="get">
            <input class="form-control form-control-sm me-2" type="search" name="q" placeholder="Search store">
          </form>

          <!-- MENU -->
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>browse.php">Browse</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>news_api.php">News</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>contact.php">Support</a></li>
          </ul>

          <!-- PLATFORM BADGES -->
          <div class="d-none d-lg-flex gap-2 me-3">
            <span class="badge bg-secondary">Steam</span>
            <span class="badge bg-primary">PlayStation</span>
            <span class="badge bg-danger">Nintendo</span>
          </div>

          <!-- CART -->
          <a href="<?= BASE_URL ?>cart.php" class="btn btn-outline-light btn-sm position-relative me-3">
            🛒
            <?php if ($cartCount > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $cartCount ?>
              </span>
            <?php endif; ?>
          </a>

          <!-- ACCOUNT -->
          <?php if ($userLoggedIn): ?>
            <div class="dropdown">
              <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                My Account
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>account.php">Profile</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>my_orders.php">My Orders</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>purchase_history.php">Purchase History</a></li>
                <?php if ($userRole === 'admin'): ?>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/index.php">Admin Panel</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php">Logout</a></li>
              </ul>
            </div>
          <?php else: ?>
            <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-light btn-sm me-2">Login</a>
            <a href="<?= BASE_URL ?>register.php" class="btn btn-primary btn-sm">Register</a>
          <?php endif; ?>

        </div>
      </div>
    </nav>
  </header>

  <main>
<?php endif; ?>

<!-- FLASH MESSAGE -->
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success container mt-3">
    <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger container mt-3">
    <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
  </div>
<?php endif; ?>
