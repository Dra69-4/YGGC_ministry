<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();
$pdo = get_db_connection();

// Currency totals are shown separately; unlike-currency amounts must not be added together.
$completed_totals = $pdo->query(
    "SELECT currency, SUM(amount) AS total FROM donations WHERE status='completed' GROUP BY currency ORDER BY currency"
)->fetchAll();
$total_pending_count = $pdo->query("SELECT COUNT(*) FROM donations WHERE status='pending'")->fetchColumn();
$donor_count = $pdo->query("SELECT COUNT(DISTINCT donor_email) FROM donations WHERE status='completed'")->fetchColumn();
$upcoming_events_count = $pdo->query("SELECT COUNT(*) FROM events WHERE status='upcoming' AND event_date >= CURRENT_DATE")->fetchColumn();
$unread_messages = $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = FALSE")->fetchColumn();
$this_month_totals = $pdo->query(
    "SELECT currency, SUM(amount) AS total FROM donations
     WHERE status='completed'
       AND created_at >= DATE_TRUNC('month', CURRENT_DATE)
       AND created_at < DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month'
     GROUP BY currency ORDER BY currency"
)->fetchAll();

// Monthly totals (last 6 months) for bar chart
$monthly = $pdo->query(
    "SELECT TO_CHAR(DATE_TRUNC('month', created_at), 'YYYY-MM') AS ym,
        TO_CHAR(DATE_TRUNC('month', created_at), 'Mon') AS label, SUM(amount) AS total
     FROM donations
     WHERE status = 'completed' AND currency = 'USD'
       AND created_at >= DATE_TRUNC('month', CURRENT_DATE) - INTERVAL '5 months'
     GROUP BY DATE_TRUNC('month', created_at) ORDER BY DATE_TRUNC('month', created_at) ASC"
)->fetchAll();
$max_month = 1;
foreach ($monthly as $m) { $max_month = max($max_month, (float) $m['total']); }

$fund_totals = $pdo->query(
  "SELECT COALESCE(NULLIF(TRIM(fund), ''), 'General Fund') AS fund, COUNT(*) AS gift_count
   FROM donations
   WHERE status = 'completed'
  GROUP BY COALESCE(NULLIF(TRIM(fund), ''), 'General Fund')
   ORDER BY gift_count DESC, fund ASC"
)->fetchAll();
$fund_gift_total = array_sum(array_map(static fn($fund) => (int) $fund['gift_count'], $fund_totals));

// Recent donations & messages
$recent_donations = $pdo->query("SELECT * FROM donations ORDER BY created_at DESC LIMIT 6")->fetchAll();
$recent_messages  = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

$page_title = 'Dashboard';
$admin_page = 'dashboard';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="kpi-grid">
  <div class="kpi-card">
    <div class="kpi-label">Total Raised</div>
    <div class="kpi-value">
      <?php if ($completed_totals): ?>
        <?php foreach ($completed_totals as $total): ?>
          <span><?= format_money((float) $total['total'], $total['currency']) ?></span>
        <?php endforeach; ?>
      <?php else: ?>
        <?= format_money(0) ?>
      <?php endif; ?>
    </div>
    <div class="kpi-sub">Confirmed donations</div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-label">This Month</div>
    <div class="kpi-value">
      <?php if ($this_month_totals): ?>
        <?php foreach ($this_month_totals as $total): ?>
          <span><?= format_money((float) $total['total'], $total['currency']) ?></span>
        <?php endforeach; ?>
      <?php else: ?>
        <?= format_money(0) ?>
      <?php endif; ?>
    </div>
    <div class="kpi-sub"><?= date('F Y') ?></div>
  </div>
  <div class="kpi-card kpi-clay">
    <div class="kpi-label">Pending Donations</div>
    <div class="kpi-value"><?= (int) $total_pending_count ?></div>
    <div class="kpi-sub">Awaiting confirmation</div>
  </div>
  <div class="kpi-card kpi-indigo">
    <div class="kpi-label">Unique Donors</div>
    <div class="kpi-value"><?= (int) $donor_count ?></div>
    <div class="kpi-sub">All-time</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-label">Upcoming Events</div>
    <div class="kpi-value"><?= (int) $upcoming_events_count ?></div>
    <div class="kpi-sub"><a href="<?= SITE_URL ?>/admin/events.php" class="link-indigo">Manage &rarr;</a></div>
  </div>
  <div class="kpi-card kpi-clay">
    <div class="kpi-label">Unread Messages</div>
    <div class="kpi-value"><?= (int) $unread_messages ?></div>
    <div class="kpi-sub"><a href="<?= SITE_URL ?>/admin/messages.php" class="link-indigo">View &rarr;</a></div>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <div>
      <h2>Confirmed Donations by Fund</h2>
      <p class="chart-subtitle">Share of confirmed gifts by program area, across all currencies</p>
    </div>
  </div>
  <?php if ($fund_gift_total > 0): ?>
    <div class="fund-donations-chart" role="list">
      <?php foreach ($fund_totals as $fund):
        $gift_count = (int) $fund['gift_count'];
        $share = round(($gift_count / $fund_gift_total) * 100);
      ?>
        <div class="fund-donation-row" role="listitem">
          <div class="fund-donation-heading">
            <span><?= e($fund['fund']) ?></span>
            <strong><?= $gift_count ?> <?= $gift_count === 1 ? 'gift' : 'gifts' ?> · <?= $share ?>%</strong>
          </div>
          <div class="fund-donation-track" role="img" aria-label="<?= e($fund['fund']) ?>: <?= $gift_count ?> confirmed gifts, <?= $share ?> percent">
            <span style="width: <?= $share ?>%"></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>No confirmed donations to display yet.</p>
  <?php endif; ?>
</div>

<div class="panel">
  <div class="panel-head"><h2>Donations — Last 6 Months (USD, completed only)</h2></div>
  <?php if ($monthly): ?>
    <div class="bar-chart">
      <?php foreach ($monthly as $m):
        $pct  = $max_month > 0 ? ((float) $m['total'] / $max_month) * 160 : 10;
        $step = (int) max(10, min(160, round($pct / 10) * 10)); // nearest 10px step, matches .bar-h-* classes
      ?>
        <div class="bar-col">
          <div class="bar bar-h-<?= $step ?>" title="<?= format_money((float) $m['total']) ?>"></div>
          <div class="bar-label"><?= e($m['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>No completed donations recorded yet.</p>
  <?php endif; ?>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Recent Donations</h2>
    <a href="<?= SITE_URL ?>/admin/donations.php" class="action-btn">View All</a>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead><tr><th>Donor</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($recent_donations as $d): ?>
          <tr>
            <td><?= db_bool($d['is_anonymous']) ? 'Anonymous' : e($d['donor_name']) ?></td>
            <td><?= format_money((float) $d['amount'], $d['currency']) ?></td>
            <td><?= e(ucwords(str_replace('_', ' ', $d['payment_method']))) ?></td>
            <td><span class="status-pill status-<?= e($d['status']) ?>"><?= e(ucfirst($d['status'])) ?></span></td>
            <td><?= (new DateTime($d['created_at']))->format('j M, g:i A') ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recent_donations): ?><tr><td colspan="5">No donations yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Recent Messages</h2>
    <a href="<?= SITE_URL ?>/admin/messages.php" class="action-btn">View All</a>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Subject</th><th>Type</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($recent_messages as $m): ?>
          <tr>
            <td><?= e($m['name']) ?> <?= db_bool($m['is_read']) ? '' : '<span class="status-pill status-pending">New</span>' ?></td>
            <td><?= e($m['subject']) ?></td>
            <td><?= e(ucfirst($m['source'])) ?></td>
            <td><?= (new DateTime($m['created_at']))->format('j M, g:i A') ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recent_messages): ?><tr><td colspan="4">No messages yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
