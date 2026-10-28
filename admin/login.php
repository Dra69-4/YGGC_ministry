<?php
require_once __DIR__ . '/includes/auth.php';

if (admin_is_logged_in()) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $error = 'Your session expired, please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic throttling against brute force
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['login_attempt_time'] = $_SESSION['login_attempt_time'] ?? time();
        if ($_SESSION['login_attempts'] > 8 && (time() - $_SESSION['login_attempt_time']) < 300) {
            $error = 'Too many attempts. Please wait a few minutes and try again.';
        } elseif ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            $stored_hash = $admin['password_hash'] ?? '';
            $password_hash = strncmp($stored_hash, '$2b$', 4) === 0
              ? '$2y$' . substr($stored_hash, 4)
              : $stored_hash;

            if ($admin && password_verify($password, $password_hash)) {
              if ($password_hash !== $stored_hash || password_needs_rehash($password_hash, PASSWORD_DEFAULT)) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $rehash = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
                $rehash->execute([$new_hash, $admin['id']]);
              }

                session_regenerate_id(true);
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_last_active'] = time();
                unset($_SESSION['login_attempts'], $_SESSION['login_attempt_time']);

                $upd = $pdo->prepare('UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?');
                $upd->execute([$admin['id']]);

                redirect(SITE_URL . '/admin/dashboard.php');
            } else {
                $error = 'Incorrect username or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | YGGC Ministries</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-login-wrap">
  <div class="admin-login-card">
    <h1>YGGC Admin</h1>
    <p class="sub">Sign in to manage donations, events &amp; site stats.</p>

    <?php if (!empty($_GET['expired'])): ?><div class="alert alert-error">Your session expired. Please log in again.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <div class="form-field login-field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus>
      </div>
      <div class="form-field login-field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Log In</button>
    </form>
    <p class="text-center-mt"><a href="<?= SITE_URL ?>/index.php" class="link-indigo">&larr; Back to website</a></p>
  </div>
</div>
</body>
</html>
