<?php
require __DIR__ . '/config/config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)"
            );
            $stmt->execute([$name, $email, $hash]);

            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Email sudah terdaftar.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Register · Slashers.com</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
  min-height: 100vh;
  background:
    linear-gradient(rgba(5,10,20,.75), rgba(5,10,20,.75)),
    url("assets/images/auth-bg.jpg") center/cover no-repeat;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #e9ecef;
}

.auth-card {
  max-width: 440px;
  width: 100%;
  background: rgba(20,25,35,.92);
  border-radius: 16px;
  padding: 30px;
  animation: fadeUp .8s ease;
}

@keyframes fadeUp {
  from { opacity:0; transform: translateY(30px); }
  to   { opacity:1; transform: translateY(0); }
}
</style>
</head>

<body>

<div class="auth-card shadow-lg">
  <div class="text-center mb-4">
    <h1 class="fw-bold">Join Slashers</h1>
    <small class="text-muted">Create account & unlock your games</small>
  </div>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <button class="btn btn-primary w-100 mb-2">Daftar</button>

    <div class="text-center small">
      Sudah punya akun?
      <a href="login.php" class="text-info">Login</a>
    </div>
  </form>
</div>

</body>
</html>
