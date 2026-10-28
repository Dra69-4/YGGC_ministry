<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();
$events    = get_upcoming_events($pdo, 3);
$ministries = array_slice(get_active_ministries($pdo), 0, 3);
$campaign  = get_campaign_goal($pdo);

$page_title   = 'Home';
$current_page = 'home';
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero-wrap">
    <div class="hero-copy reveal">
      <span class="kicker">Young Generation Growing In Christ</span>
      <h1>Helping children and families build a future with <em>hope</em>, <em>faith</em> and opportunity.</h1>
      <p class="lead">YGGC supports children and families in Isingiro with school fees, scholastic materials, clothing and other basic needs, while sharing the hope of Christ with children and youth.</p>
      <div class="hero-actions">
        <a href="<?= SITE_URL ?>/donate.php" class="btn btn-primary">Support Children and Families</a>
        <a href="<?= SITE_URL ?>/about.php" class="btn btn-outline btn-outline-inverse">About Our Work</a>
      </div>
    </div>
  </div>
  <svg class="hero-wave" viewBox="0 0 1200 60" preserveAspectRatio="none"><path d="M0 30 C 300 60 900 0 1200 30 L1200 60 L0 60 Z" fill="#FBF7EF"/></svg>
</section>

<section class="section">
  <div class="container two-col">
    <div class="reveal">
      <span class="kicker">Who we are</span>
      <h2>Practical care for families, and faith for the next generation.</h2>
      <p>Young Generation Growing In Christ (YGGC) serves children, youth and families in Isingiro, Uganda. We help children access education and essential items, support families facing hardship, and provide safe water initiatives for communities.</p>
      <p>Alongside practical support, we teach children and young people about the Bible and faith in Christ through school ministries and community activities.</p>
      <a href="<?= SITE_URL ?>/about.php" class="btn btn-outline">Learn About YGGC</a>
    </div>
    <div class="quote-block reveal">
      Our work brings together education, essential support, safe water and Christian teaching to serve children and families with dignity.
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="kicker">What we do</span>
        <h2>Support shaped around children and families</h2>
      </div>
      <a href="<?= SITE_URL ?>/ministries.php" class="btn btn-outline">View All Ministries</a>
    </div>
    <div class="grid">
      <?php foreach ($ministries as $m): ?>
        <div class="card-ministry reveal">
          <h3><?= e($m['name']) ?></h3>
          <p><?= e($m['summary']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="kicker">Save the date</span>
        <h2>Upcoming events</h2>
      </div>
      <a href="<?= SITE_URL ?>/events.php" class="btn btn-outline">See All Events</a>
    </div>
    <?php if ($events): ?>
      <div class="event-list reveal">
        <?php foreach ($events as $ev):
          $d = new DateTime($ev['event_date']); ?>
          <div class="event-row">
            <div class="event-date-block">
              <span class="day"><?= $d->format('d') ?></span>
              <span class="month"><?= $d->format('M') ?></span>
            </div>
            <div class="event-body">
              <h3 class="tight-heading"><?= e($ev['title']) ?></h3>
              <div class="event-meta">
                <span><?= e($ev['location']) ?></span>
                <?php if ($ev['event_time']): ?><span><?= (new DateTime($ev['event_time']))->format('g:i A') ?></span><?php endif; ?>
              </div>
              <p><?= e($ev['description']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No upcoming events right now — check back soon!</p>
    <?php endif; ?>
  </div>
</section>

<section class="section section-indigo">
  <div class="container two-col">
    <div class="reveal">
      <span class="kicker kicker-on-dark">Give with purpose</span>
      <h2>Your gift can help meet a family's practical needs.</h2>
      <p class="text-on-dark">Donations support school fees and scholastic materials, clothing, basic needs, safe water and Bible teaching for children and youth.</p>
      <div class="hero-actions">
        <a href="<?= SITE_URL ?>/donate.php" class="btn btn-primary">Make a Donation</a>
        <a href="<?= SITE_URL ?>/donate.php?frequency=monthly" class="btn btn-outline btn-outline-inverse">Give Monthly</a>
      </div>
    </div>
    <div class="reveal">
      <?php if ($campaign): ?>
        <div class="campaign-card on-dark">
          <div class="campaign-head">
            <h3><?= e($campaign['title']) ?></h3>
            <span class="campaign-percent"><?= (int) $campaign['percent'] ?>%</span>
          </div>
          <div class="thermometer-track">
            <div class="thermometer-fill thermo-w-<?= thermo_step((float) $campaign['percent']) ?>"></div>
          </div>
          <div class="campaign-figures">
            <span><strong><?= format_money((float) $campaign['raised'], $campaign['currency']) ?></strong> raised</span>
            <span>Goal: <strong><?= format_money((float) $campaign['goal_amount'], $campaign['currency']) ?></strong></span>
          </div>
        </div>
      <?php endif; ?>
      <p class="text-on-dark mt-20">Your donation can support:</p>
      <ul class="stack-list">
        <li><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> School fees and scholastic materials</li>
        <li><i class="fa-solid fa-shirt" aria-hidden="true"></i> Clothing and essential items</li>
        <li><i class="fa-solid fa-droplet" aria-hidden="true"></i> Safe water for communities</li>
        <li><i class="fa-solid fa-book-bible" aria-hidden="true"></i> Bible and faith teaching</li>
      </ul>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
