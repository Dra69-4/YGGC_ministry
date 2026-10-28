<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();

$pdo = get_db_connection();
$admin = current_admin();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $admin['id']]);
        $password_hash = $stmt->fetchColumn();

        if (!$password_hash || !password_verify($current_password, $password_hash)) {
            $errors[] = 'Your current password is incorrect.';
        }
        if (mb_strlen($new_password, 'UTF-8') < 12) {
            $errors[] = 'Your new password must be at least 12 characters.';
        }
        if (strlen($new_password) > 72) {
            $errors[] = 'Your new password must be no more than 72 bytes.';
        }
        if ($new_password !== $confirm_password) {
            $errors[] = 'The new password confirmation does not match.';
        }
        if (!$errors && password_verify($new_password, $password_hash)) {
            $errors[] = 'Choose a password different from your current password.';
        }

        if (!$errors) {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
            $stmt->execute([$new_hash, (int) $admin['id']]);

            session_regenerate_id(true);
            unset($_SESSION['csrf_token']);
            flash_set('success', 'Your password has been changed.');
            redirect(SITE_URL . '/admin/change-password.php');
        }
    }
}

$page_title = 'Change Password';
$admin_page = 'change-password';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>

<div class="panel">
  <div class="panel-head"><h2>Update your password</h2></div>
  <form method="POST" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="form-grid">
      <div class="form-field full">
        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
      </div>
      <div class="form-field">
        <label for="new_password">New password</label>
        <input type="password" id="new_password" name="new_password" autocomplete="new-password" minlength="12" maxlength="72" required>
      </div>
      <div class="form-field">
        <label for="confirm_password">Confirm new password</label>
        <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" minlength="12" maxlength="72" required>
      </div>
      <div class="form-field full">
        <button type="submit" class="btn btn-primary">Change Password</button>
      </div>
    </div>
  </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>