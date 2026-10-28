<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();
$ref = trim($_GET['ref'] ?? '');

$donation = null;
if ($ref !== '') {
    $stmt = $pdo->prepare('SELECT * FROM donations WHERE transaction_ref = ?');
    $stmt->execute([$ref]);
    $donation = $stmt->fetch();
}

$page_title   = 'Thank You';
$current_page = 'donate';
include __DIR__ . '/includes/header.php';
?>
<section class="section section-centered">
  <div class="container text-center">
    <?php if ($donation && $donation['status'] === 'completed'): ?>
      <h1>Thank you, <?= e($donation['donor_name']) ?>! 🎉</h1>
      <p>Your donation of <strong><?= format_money((float) $donation['amount'], $donation['currency']) ?></strong> has been received. A confirmation has been recorded under reference <strong><?= e($donation['transaction_ref']) ?></strong>.</p>
    <?php elseif ($donation): ?>
      <h1>Almost there!</h1>
      <p>We've recorded your donation (ref: <strong><?= e($donation['transaction_ref']) ?></strong>) as pending. The ministry will confirm it after verifying your payment.</p>
    <?php else: ?>
      <h1>We couldn't find that donation</h1>
      <p>If money left your account, please contact us with your payment reference and we'll confirm it manually.</p>
    <?php endif; ?>
    <a href="<?= SITE_URL ?>/index.php" class="btn btn-primary mt-20">Back to Home</a>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
