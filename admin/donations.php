<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();
$pdo = get_db_connection();

// ---------- Handle status update ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!csrf_check()) {
        flash_set('error', 'Session expired, please try again.');
    } else {
        $id = (int) ($_POST['donation_id'] ?? 0);
        $new_status = $_POST['status'] ?? '';
        if (in_array($new_status, ['pending', 'completed', 'failed', 'refunded'], true) && $id > 0) {
          if ($new_status === 'completed') {
            complete_donation($pdo, $id, true);
          } else {
            $stmt = $pdo->prepare('UPDATE donations SET status = ? WHERE id = ?');
            $stmt->execute([$new_status, $id]);
          }
            flash_set('success', 'Donation status updated.');
        }
    }
    redirect(SITE_URL . '/admin/donations.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

// ---------- Filters ----------
$status_filter    = $_GET['status'] ?? '';
$frequency_filter = $_GET['frequency'] ?? '';
$search           = trim($_GET['q'] ?? '');

$sql = 'SELECT * FROM donations WHERE 1=1';
$params = [];
if (in_array($status_filter, ['pending', 'completed', 'failed', 'refunded'], true)) {
    $sql .= ' AND status = ?';
    $params[] = $status_filter;
}
if (in_array($frequency_filter, ['one_time', 'monthly'], true)) {
    $sql .= ' AND frequency = ?';
    $params[] = $frequency_filter;
}
if ($search !== '') {
    $sql .= ' AND (donor_name LIKE ? OR donor_email LIKE ? OR transaction_ref LIKE ?)';
    $like = "%$search%";
    array_push($params, $like, $like, $like);
}
$sql .= ' ORDER BY created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$donations = $stmt->fetchAll();

$totals = $pdo->query("SELECT status, currency, COUNT(*) c, COALESCE(SUM(amount),0) t FROM donations GROUP BY status, currency")->fetchAll();
$totals_by_status = [];
foreach ($totals as $t) {
    $status = $t['status'];
    if (!isset($totals_by_status[$status])) {
        $totals_by_status[$status] = ['c' => 0, 'amounts' => []];
    }
    $totals_by_status[$status]['c'] += (int) $t['c'];
    $totals_by_status[$status]['amounts'][$t['currency']] = (float) $t['t'];
}
$monthly_donor_count = (int) $pdo->query("SELECT COUNT(DISTINCT donor_email) FROM donations WHERE frequency = 'monthly'")->fetchColumn();

$page_title = 'Donations';
$admin_page = 'donations';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash_get('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>

<div class="kpi-grid">
  <?php foreach (['pending' => 'Pending', 'completed' => 'Completed', 'failed' => 'Failed', 'refunded' => 'Refunded'] as $key => $label): ?>
    <div class="kpi-card <?= $key === 'completed' ? 'kpi-green' : ($key === 'failed' ? 'kpi-clay' : '') ?>">
      <div class="kpi-label"><?= $label ?></div>
      <div class="kpi-value"><?= (int) ($totals_by_status[$key]['c'] ?? 0) ?></div>
      <div class="kpi-sub">
        <?php
          $amounts = [];
          foreach (($totals_by_status[$key]['amounts'] ?? []) as $currency => $amount) {
              $amounts[] = format_money($amount, $currency);
          }
          echo $amounts ? implode(' · ', $amounts) : 'No donations';
        ?>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="kpi-card kpi-green">
    <div class="kpi-label">Monthly Partners</div>
    <div class="kpi-value"><?= $monthly_donor_count ?></div>
    <div class="kpi-sub">Unique recurring donors</div>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>All Donations</h2></div>

  <form method="GET" class="filter-row">
    <select name="status" onchange="this.form.submit()">
      <option value="">All Statuses</option>
      <?php foreach (['pending', 'completed', 'failed', 'refunded'] as $s): ?>
        <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="frequency" onchange="this.form.submit()">
      <option value="">One-Time &amp; Monthly</option>
      <option value="one_time" <?= $frequency_filter === 'one_time' ? 'selected' : '' ?>>One-Time Only</option>
      <option value="monthly" <?= $frequency_filter === 'monthly' ? 'selected' : '' ?>>Monthly Only</option>
    </select>
    <input type="text" name="q" placeholder="Search name, email or reference..." value="<?= e($search) ?>">
    <button type="submit" class="btn btn-outline">Filter</button>
  </form>

  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr><th>Ref</th><th>Donor</th><th>Amount</th><th>Fund</th><th>Frequency</th><th>Method</th><th>Status</th><th>Date</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($donations as $d): ?>
          <tr>
            <td class="text-small"><?= e($d['transaction_ref']) ?></td>
            <td><?= db_bool($d['is_anonymous']) ? 'Anonymous' : e($d['donor_name']) ?><br><span class="text-muted-small"><?= e($d['donor_email']) ?></span></td>
            <td><?= format_money((float) $d['amount'], $d['currency']) ?></td>
            <td><?= e($d['fund']) ?></td>
            <td><?= $d['frequency'] === 'monthly' ? '<span class="status-pill status-completed">Monthly</span>' : 'One-Time' ?></td>
            <td><?= e(ucwords(str_replace('_', ' ', $d['payment_method']))) ?></td>
            <td><span class="status-pill status-<?= e($d['status']) ?>"><?= e(ucfirst($d['status'])) ?></span></td>
            <td><?= (new DateTime($d['created_at']))->format('j M Y, g:i A') ?></td>
            <td>
              <form method="POST" class="inline-flex-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="donation_id" value="<?= (int) $d['id'] ?>">
                <select name="status" class="select-compact">
                  <?php foreach (['pending', 'completed', 'failed', 'refunded'] as $s): ?>
                    <option value="<?= $s ?>" <?= $d['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="action-btn confirm">Save</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$donations): ?><tr><td colspan="9">No donations match this filter.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
