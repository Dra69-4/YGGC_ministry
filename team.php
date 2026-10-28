<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();
$members = $pdo->query('SELECT name, role, bio, photo_path FROM team_members WHERE is_active = TRUE ORDER BY display_order ASC, name ASC')->fetchAll();

$page_title = 'Our Team';
$page_description = 'Meet the YGGC team serving children and families in Isingiro.';
$current_page = 'team';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header team-page-header">
  <div class="container white-container">
    <span class="breadcrumb">Our Team</span>
    <h1>People committed to serving children and families.</h1>
    <p>Meet the people behind YGGC's work in Isingiro. We serve through education support, family essentials, safe water and faith in Christ.</p>
  </div>
</section>

<section class="section team-section">
  <div class="container">
    <?php if ($members): ?>
      <div class="team-grid">
        <?php foreach ($members as $index => $member): ?>
          <article class="team-card" style="--team-index: <?= (int) $index ?>">
            <div class="team-card-photo">
              <img src="<?= e(SITE_URL . '/' . $member['photo_path']) ?>" alt="Portrait of <?= e($member['name']) ?>" loading="lazy">
            </div>
            <div class="team-card-copy">
              <span class="team-card-rule" aria-hidden="true"></span>
              <h2><?= e($member['name']) ?></h2>
              <p class="team-card-role"><?= e($member['role']) ?></p>
              <?php if (!empty($member['bio'])): ?><p class="team-card-bio"><?= nl2br(e($member['bio'])) ?></p><?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="team-empty-state">
        <span class="team-empty-mark" aria-hidden="true"><i class="fa-solid fa-people-group"></i></span>
        <h2>Our team profiles are being prepared.</h2>
        <p>Please contact us to learn more about the people serving children and families in Isingiro.</p>
        <a href="<?= SITE_URL ?>/contact.php" class="btn btn-primary">Contact YGGC</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
