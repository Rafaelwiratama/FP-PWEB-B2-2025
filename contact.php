<?php
require_once __DIR__ . '/config/config.php';

$name  = '';
$email = '';
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($u = $stmt->fetch()) {
        $name  = $u['name'];
        $email = $u['email'];
    }
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $errors[] = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (!$errors) {
        $stmtIns = $pdo->prepare("
          INSERT INTO support_tickets (user_id, name, email, subject, message)
          VALUES (?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([
            $_SESSION['user_id'] ?? null,
            $name, $email, $subject, $message
        ]);
        $success = 'Pesan kamu sudah kami terima. Tim support akan membalas secepatnya.';
        $subject = $message = '';
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding-top:140px; padding-bottom:60px; max-width:800px;">
  <h2 class="mb-4">Contact & Support</h2>

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

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Subjek</label>
      <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($subject ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Pesan</label>
      <textarea name="message" class="form-control" rows="5"><?= htmlspecialchars($message ?? '') ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Kirim</button>
  </form>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
