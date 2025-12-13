<?php
require_once __DIR__ . '/config/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$tab = $_GET['tab'] ?? 'profile';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // UPDATE PROFILE
    if (isset($_POST['update_profile'])) {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '' || $email === '') {
            $errors[] = 'Nama dan email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        } else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
            $check->execute([$email, $user['id']]);
            if ($check->fetch()) {
                $errors[] = 'Email sudah digunakan user lain.';
            }
        }

        if (!$errors) {
            $upd = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $upd->execute([$name, $email, $user['id']]);
            $success = 'Profil berhasil diperbarui.';
            $user['name']  = $name;
            $user['email'] = $email;
        }

        $tab = 'profile';
    }

    // CHANGE PASSWORD
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $errors[] = 'Semua field password wajib diisi.';
        } elseif (!password_verify($current, $user['password_hash'])) {
            $errors[] = 'Password lama salah.';
        } elseif ($new !== $confirm) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        } elseif (strlen($new) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        }

        if (!$errors) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd  = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $upd->execute([$hash, $user['id']]);
            $success = 'Password berhasil diganti.';
        }

        $tab = 'password';
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top:140px; padding-bottom:60px; max-width:860px;">
  <h2 class="mb-4">My Account</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link <?= $tab === 'profile' ? 'active' : '' ?>" href="account.php?tab=profile">Profil</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $tab === 'password' ? 'active' : '' ?>" href="account.php?tab=password">Ganti Password</a>
    </li>
  </ul>

  <?php if ($tab === 'profile'): ?>
    <form method="post">
      <input type="hidden" name="update_profile" value="1">
      <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="name" class="form-control"
               value="<?= htmlspecialchars($user['name']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control"
               value="<?= htmlspecialchars($user['email']) ?>">
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
  <?php else: ?>
    <form method="post">
      <input type="hidden" name="change_password" value="1">
      <div class="mb-3">
        <label class="form-label">Password Lama</label>
        <input type="password" name="current_password" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Password Baru</label>
        <input type="password" name="new_password" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Konfirmasi Password Baru</label>
        <input type="password" name="confirm_password" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary">Ganti Password</button>
    </form>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
