<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = get_db_connection();
$ministries = get_active_ministries($pdo);

$page_title   = 'Ministries';
$page_description = 'Explore YGGC programs supporting education, family needs, safe water and Christian teaching in Isingiro.';
$current_page = 'ministries';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container white-container">
    <span class="breadcrumb">Ministries</span>
    <h1>Practical support and faith for children and families.</h1>
    <p>Our programs respond to local needs in Isingiro, from education and essential items to safe water and Bible teaching.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="grid">
      <?php foreach ($ministries as $m): ?>
        <div class="card-ministry reveal" id="<?= e($m['slug']) ?>">
          <h3><?= e($m['name']) ?></h3>
          <p><?= e($m['description']) ?></p>
        </div>
      <?php endforeach; ?>
      <?php if (!$ministries): ?>
        <p>Ministry information is being updated — please check back soon.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section section-indigo text-center">
  <div class="container">
    <h2>Help children and families in Isingiro</h2>
    <p class="section-indigo-lead">Volunteer your time, share useful goods, or contribute to the programs that support education, basic needs, safe water and faith.</p>
    <a href="<?= SITE_URL ?>/get-involved.php" class="btn btn-primary">Get Involved</a>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
