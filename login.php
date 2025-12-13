<?php
require __DIR__ . '/config/config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];

        if ($remember) {
            setcookie('remember_email', $email, time() + (86400 * 30), "/");
        }

        header('Location: index.php');
        exit;
    } else {
        $errors[] = 'Email atau password salah.';
    }
}

$savedEmail = $_COOKIE['remember_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login · Slashers.com</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root {
  --bg-overlay: rgba(5,10,20,.75);
  --card-bg: rgba(20,25,35,.92);
  --text-main: #e9ecef;
}

body {
  min-height: 100vh;
  background:
    linear-gradient(var(--bg-overlay), var(--bg-overlay)),
    url("assets/images/auth-bg.jpg") center/cover no-repeat;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-main);
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: var(--card-bg);
  border-radius: 16px;
  padding: 30px;
  animation: fadeUp .8s ease;
}

@keyframes fadeUp {
  from { opacity:0; transform: translateY(30px); }
  to   { opacity:1; transform: translateY(0); }
}

.auth-title {
  font-size: 28px;
  font-weight: 700;
}

.auth-tagline {
  font-size: 14px;
  color: #9aa4b2;
}

.toggle-theme {
  cursor: pointer;
  font-size: 13px;
  opacity: .8;
}
</style>
</head>

<body>

<div class="auth-card shadow-lg">
  <div class="text-center mb-4">
    <h1 class="auth-title">Slashers.com</h1>
    <div class="auth-tagline">Unlock Your Games</div>
  </div>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control"
             value="<?= htmlspecialchars($savedEmail) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="remember" id="remember">
        <label class="form-check-label small" for="remember">Remember me</label>
      </div>
      <span class="toggle-theme text-info">🌙 Toggle Theme</span>
    </div>

    <button class="btn btn-primary w-100 mb-2">Masuk</button>

    <div class="text-center small">
      Belum punya akun?
      <a href="register.php" class="text-info">Daftar</a>
    </div>
  </form>
</div>

<script>
document.querySelector('.toggle-theme').onclick = () => {
  document.body.classList.toggle('bg-light');
  document.body.classList.toggle('text-dark');
};
</script>

</body>
</html>
