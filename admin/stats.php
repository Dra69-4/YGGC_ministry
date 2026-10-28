<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();
$pdo = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Session expired, please try again.');
        redirect(SITE_URL . '/admin/stats.php');
    }

    $form = $_POST['form'] ?? '';

    if ($form === 'counters') {
        $stmt = $pdo->prepare('UPDATE site_stats SET stat_value = ?, stat_label = ? WHERE stat_key = ?');
        foreach ($_POST['stats'] ?? [] as $key => $row) {
            $value = filter_var($row['value'] ?? 0, FILTER_VALIDATE_INT);
            $label = trim($row['label'] ?? '');
            if ($value !== false && $label !== '') {
                $stmt->execute([$value, $label, $key]);
            }
        }
        flash_set('success', 'Home page counters updated.');
    } elseif ($form === 'campaign') {
        $title    = trim($_POST['campaign_title'] ?? '');
        $goal     = filter_var($_POST['campaign_goal'] ?? '', FILTER_VALIDATE_FLOAT);
        $currency = 'USD';
        $deadline = $_POST['campaign_deadline'] ?: null;
        $is_active = !empty($_POST['campaign_active']) ? 'true' : 'false';

        $errors = [];
        if ($title === '') $errors[] = 'Campaign title is required.';
        if ($goal === false || $goal <= 0) $errors[] = 'Please enter a valid goal amount.';
        if ($deadline && !DateTime::createFromFormat('Y-m-d', $deadline)) $errors[] = 'Please enter a valid deadline date.';

        if ($errors) {
            flash_set('error', implode(' ', $errors));
        } else {
            $exists = $pdo->query('SELECT COUNT(*) FROM campaign_goal WHERE id = 1')->fetchColumn();
            if ($exists) {
                $stmt = $pdo->prepare('UPDATE campaign_goal SET title=?, goal_amount=?, currency=?, deadline=?, is_active=? WHERE id=1');
            } else {
                $stmt = $pdo->prepare('INSERT INTO campaign_goal (title, goal_amount, currency, deadline, is_active, id) VALUES (?, ?, ?, ?, ?, 1)');
            }
            $stmt->execute([$title, $goal, $currency, $deadline, $is_active]);
            flash_set('success', 'Fundraising campaign updated.');
        }
    }
    redirect(SITE_URL . '/admin/stats.php');
}

$stats    = $pdo->query('SELECT * FROM site_stats')->fetchAll();
$campaign = $pdo->query('SELECT * FROM campaign_goal WHERE id = 1')->fetch();
$campaign_needs_usd = $campaign && $campaign['currency'] !== 'USD';

$page_title = 'Site Stats';
$admin_page = 'stats';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash_get('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head"><h2>Fundraising Campaign (Thermometer)</h2></div>
  <p class="panel-subtitle">This powers the progress bar shown on the Home and Donate pages. Amount raised is always calculated live from completed donations — you only set the goal here.</p>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="form" value="campaign">
    <div class="form-grid">
      <div class="form-field full">
        <label for="campaign_title">Campaign Title</label>
        <input type="text" id="campaign_title" name="campaign_title" value="<?= e($campaign['title'] ?? '') ?>" required>
      </div>
      <div class="form-field">
        <label for="campaign_goal">Goal Amount</label>
        <input type="number" id="campaign_goal" name="campaign_goal" min="1" step="0.01" value="<?= e((string) ($campaign_needs_usd ? '' : ($campaign['goal_amount'] ?? ''))) ?>" required>
        <?php if ($campaign_needs_usd): ?><small>Enter the campaign goal in USD. The previous UGX goal was not converted.</small><?php endif; ?>
      </div>
      <div class="form-field">
        <label for="campaign_currency">Currency</label>
        <select id="campaign_currency" name="campaign_currency">
          <option value="USD" selected>USD — US Dollar</option>
        </select>
      </div>
      <div class="form-field">
        <label for="campaign_deadline">Deadline (optional)</label>
        <input type="date" id="campaign_deadline" name="campaign_deadline" value="<?= e($campaign['deadline'] ?? '') ?>">
      </div>
      <div class="form-field checkbox-row">
        <input type="checkbox" id="campaign_active" name="campaign_active" value="1" class="checkbox-input" <?= db_bool($campaign['is_active'] ?? false) ? 'checked' : '' ?>>
        <label for="campaign_active">Show this campaign on the website</label>
      </div>
      <div class="form-field full">
        <button type="submit" class="btn btn-primary">Save Campaign</button>
      </div>
    </div>
  </form>
</div>

<div class="panel">
  <div class="panel-head"><h2>Home Page Counters</h2></div>
  <p class="panel-subtitle">These numbers power the animated counters on the public home page and About page.</p>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="form" value="counters">
    <div class="form-grid">
      <?php foreach ($stats as $s): ?>
        <div class="form-field">
          <label><?= e($s['stat_key']) ?></label>
          <input type="text" name="stats[<?= e($s['stat_key']) ?>][label]" value="<?= e($s['stat_label']) ?>" placeholder="Label">
          <input type="number" name="stats[<?= e($s['stat_key']) ?>][value]" value="<?= (int) $s['stat_value'] ?>" min="0" class="mt-8">
        </div>
      <?php endforeach; ?>
      <div class="form-field full">
        <button type="submit" class="btn btn-primary">Save Counters</button>
      </div>
    </div>
  </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
