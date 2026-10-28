<?php
/**
 * ONE-TIME SETUP UTILITY.
 * Open this file in your browser once to generate a real password hash,
 * paste the hash into the `admins` table (or edit yggc_postgresql.sql before
 * importing), then DELETE this file — never leave it on a live server.
 */
require_once __DIR__ . '/../config/database.php';

$hash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Generate Admin Password Hash</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/setup.css">
</head>
<body>
<div class="setup-warning">⚠️ Delete this file after use. It should never remain on a production server.</div>
<h1>Generate Admin Password Hash</h1>
<form method="POST">
  <label>Choose a strong password for the admin account</label>
  <input class="setup-input" type="text" name="password" required>
  <button class="setup-button" type="submit">Generate Hash</button>
</form>
<?php if ($hash): ?>
<p>Copy this hash into the <code>password_hash</code> column of the <code>admins</code> table for your admin user:</p>
<code class="setup-code"><?= htmlspecialchars($hash) ?></code>
<p>Example SQL:</p>
<code class="setup-code">UPDATE admins SET password_hash = '<?= htmlspecialchars($hash) ?>' WHERE username = 'admin';</code>
<?php endif; ?>
</body>
</html>
