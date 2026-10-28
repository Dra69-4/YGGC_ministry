<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = get_db_connection();

$upcoming = $pdo->query("SELECT * FROM events WHERE status = 'upcoming' AND event_date >= CURRENT_DATE ORDER BY event_date ASC")->fetchAll();
$past     = $pdo->query("SELECT * FROM events WHERE status = 'completed' OR event_date < CURRENT_DATE ORDER BY event_date DESC LIMIT 6")->fetchAll();

$page_title   = 'Events';
$page_description = 'See upcoming YGGC school ministries, family support and community activities in Isingiro.';
$current_page = 'events';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container white-container">
    <span class="breadcrumb">Events</span>
    <h1>Community activities and school ministries.</h1>
    <p>Find upcoming YGGC activities supporting children, youth and families in Isingiro.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head reveal"><h2>Upcoming Events</h2></div>
    <?php if ($upcoming): ?>
      <div class="event-list reveal">
        <?php foreach ($upcoming as $ev): $d = new DateTime($ev['event_date']); ?>
          <div class="event-row">
            <div class="event-date-block">
              <span class="day"><?= $d->format('d') ?></span>
              <span class="month"><?= $d->format('M Y') ?></span>
            </div>
            <div class="event-body">
              <h3 class="tight-heading"><?= e($ev['title']) ?> <span class="badge badge-upcoming">Upcoming</span></h3>
              <div class="event-meta">
                <span><i class="fa-solid fa-location-crosshairs"></i> <?= e($ev['location']) ?></span>
                <?php if ($ev['event_time']): ?><span><i class="fa-regular fa-clock"></i> <?= (new DateTime($ev['event_time']))->format('g:i A') ?></span><?php endif; ?>
              </div>
              <p><?= e($ev['description']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No upcoming events at the moment. Follow our social pages for updates.</p>
    <?php endif; ?>
  </div>
</section>

<?php if ($past): ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal"><h2>Past Events</h2></div>
    <div class="event-list reveal">
      <?php foreach ($past as $ev): $d = new DateTime($ev['event_date']); ?>
        <div class="event-row">
          <div class="event-date-block event-date-block-past">
            <span class="day"><?= $d->format('d') ?></span>
            <span class="month"><?= $d->format('M Y') ?></span>
          </div>
          <div class="event-body">
            <h3 class="tight-heading"><?= e($ev['title']) ?> <span class="badge badge-completed">Completed</span></h3>
            <div class="event-meta"><span>📍 <?= e($ev['location']) ?></span></div>
            <p><?= e($ev['description']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
