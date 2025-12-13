<?php
require_once __DIR__ . '/../config/config.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slasher Admin · Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at top, #1f2937 0, #020617 55%, #000 100%);
            color: #f9fafb;
            min-height: 100vh;
        }
        .admin-navbar {
            background: #020617;
            border-bottom: 1px solid rgba(148,163,184,.3);
        }
        .admin-navbar .navbar-brand {
            font-weight: 700;
            letter-spacing: .06em;
        }
        .admin-card {
            background: rgba(15,23,42,.95);
            border-radius: 24px;
            border: 1px solid rgba(148,163,184,.25);
            box-shadow: 0 24px 60px rgba(0,0,0,.85);
            padding: 20px 22px;
            height: 100%;
        }
        .admin-card h5 {
            font-weight: 700;
            margin-bottom: .25rem;
        }
        .admin-card small {
            color: #9ca3af;
        }
        .admin-card ul {
            padding-left: 1.1rem;
            margin-top: .5rem;
            margin-bottom: 1rem;
            color: #9ca3af;
            font-size: .9rem;
        }
        .admin-card .btn {
            border-radius: 999px;
            font-weight: 600;
            font-size: .9rem;
        }
        .btn-pill-primary {
            background: linear-gradient(135deg,#2563eb,#4f46e5);
            border: none;
        }
        .btn-pill-outline {
            border-radius: 999px;
            border: 1px solid rgba(148,163,184,.6);
            color: #e5e7eb;
            background: transparent;
        }
        .btn-pill-outline:hover {
            background: rgba(148,163,184,.12);
            color: #fff;
        }
        .section-title {
            font-weight: 800;
            font-size: 1.35rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark admin-navbar px-3 px-md-4">
    <a class="navbar-brand" href="index.php">Slasher Admin</a>
    <div class="d-flex gap-2">
        <a href="../index.php" class="btn btn-sm btn-outline-light">Lihat Website</a>
        <a href="../logout.php" class="btn btn-sm btn-danger">Logout</a>
    </div>
</nav>

<main class="container-fluid py-4 py-md-5">
    <div class="container">
        <h2 class="section-title">Admin Dashboard</h2>

        <div class="row g-4">
            <!-- Product & Content Management -->
            <div class="col-lg-4 col-md-6">
                <div class="admin-card">
                    <h5>Product & Content Management</h5>
                    <small>Kelola katalog game & konten halaman.</small>
                    <ul>
                        <li>Product Listing &amp; Inventory Management</li>
                        <li>Content Updates (hero, banner, teks informasi)</li>
                    </ul>
                    <div class="d-grid gap-2">
                        <a href="products.php" class="btn btn-pill-primary">Kelola Produk</a>
                        <a href="content.php" class="btn btn-pill-outline">Kelola Konten</a>
                    </div>
                </div>
            </div>

            <!-- Order & Customer Management -->
            <div class="col-lg-4 col-md-6">
                <div class="admin-card">
                    <h5>Order &amp; Customer Management</h5>
                    <small>Pantau pesanan dan data pengguna.</small>
                    <ul>
                        <li>Order Processing &amp; Validasi Pembayaran</li>
                        <li>Customer Support &amp; riwayat transaksi</li>
                        <li>User Account Management</li>
                    </ul>
                    <div class="d-grid gap-2">
                        <a href="orders.php" class="btn btn-pill-primary">Kelola Order</a>
                        <a href="customers.php" class="btn btn-pill-outline">Data Customer</a>
                        <a href="users.php" class="btn btn-pill-outline">Kelola Akun User</a>
                    </div>
                </div>
            </div>

            <!-- Technical & Security -->
            <div class="col-lg-4 col-md-12">
                <div class="admin-card">
                    <h5>Technical &amp; Security</h5>
                    <small>Monitoring sistem &amp; integrasi pembayaran.</small>
                    <ul>
                        <li>Website Maintenance &amp; Monitoring</li>
                        <li>Payment &amp; System Integration (Midtrans)</li>
                    </ul>
                    <div class="d-grid gap-2">
                        <a href="integration.php?view=status" class="btn btn-pill-primary">Status Sistem</a>
                        <a href="integration.php?view=payments" class="btn btn-pill-outline">Payment &amp; Integrasi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
