<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();
$pdo = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Session expired, please try again.');
        redirect(SITE_URL . '/admin/messages.php');
    }
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['message_id'] ?? 0);

    if ($action === 'mark_read') {
        $pdo->prepare('UPDATE messages SET is_read = TRUE WHERE id = ?')->execute([$id]);
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        flash_set('success', 'Message deleted.');
    }
    redirect(SITE_URL . '/admin/messages.php');
}

$source_filter = $_GET['source'] ?? '';
$sql = 'SELECT * FROM messages WHERE 1=1';
$params = [];
if (in_array($source_filter, ['contact', 'volunteer'], true)) {
    $sql .= ' AND source = ?';
    $params[] = $source_filter;
}
$sql .= ' ORDER BY created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

$page_title = 'Messages';
$admin_page = 'messages';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Inbox</h2></div>
  <form method="GET" class="filter-row">
    <select name="source" onchange="this.form.submit()">
      <option value="">All Messages</option>
      <option value="contact" <?= $source_filter === 'contact' ? 'selected' : '' ?>>Contact Form</option>
      <option value="volunteer" <?= $source_filter === 'volunteer' ? 'selected' : '' ?>>Volunteer Interest</option>
    </select>
  </form>

  <div class="table-scroll">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Subject</th><th>Message</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($messages as $m): ?>
          <tr class="<?= db_bool($m['is_read']) ? '' : 'row-unread' ?>">
            <td class="msg-name"><?= e($m['name']) ?><br><span class="text-muted-small"><?= e($m['email']) ?><?= $m['phone'] ? ' · ' . e($m['phone']) : '' ?></span></td>
            <td class="msg-subject"><?= e($m['subject']) ?></td>
            <td class="message-preview"><?= e(mb_strimwidth($m['body'], 0, 140, '…')) ?></td>
            <td><?= e(ucfirst($m['source'])) ?></td>
            <td><?= (new DateTime($m['created_at']))->format('j M Y, g:i A') ?></td>
            <td>
              <div class="actions-cell">
                <?php if (!db_bool($m['is_read'])): ?>
                <form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="mark_read"><input type="hidden" name="message_id" value="<?= (int) $m['id'] ?>"><button class="action-btn confirm" type="submit">Mark Read</button></form>
                <?php endif; ?>
                <form method="POST" onsubmit="return confirm('Delete this message?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="message_id" value="<?= (int) $m['id'] ?>"><button class="action-btn danger" type="submit">Delete</button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$messages): ?><tr><td colspan="6">No messages yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
