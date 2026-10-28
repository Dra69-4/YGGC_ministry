<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = get_db_connection();

$uploads_url = SITE_URL . '/uploads/';
$photos = [
  ['src' => $uploads_url . 'nader-guesmi-qQZm40-A4DM-unsplash.jpg', 'caption' => 'Faith and community gathering', 'tone' => 1, 'category' => 'Community'],
  ['src' => $uploads_url . 'onur-kurt-UoEFNZEvrZI-unsplash.jpg', 'caption' => 'Community care and support', 'tone' => 2, 'category' => 'Community'],
  ['src' => $uploads_url . 'andrew-itaga-e0N0tpl26EU-unsplash.jpg', 'caption' => 'Bible teaching and learning', 'tone' => 3, 'category' => 'School Ministries'],
  ['src' => $uploads_url . 'adebayo-alabi-NOD9ZjVPLE8-unsplash.jpg', 'caption' => 'Christian faith and fellowship', 'tone' => 1, 'category' => 'Faith'],
  ['src' => $uploads_url . 'felicia-montenegro-EEbLJlfCnSI-unsplash.jpg', 'caption' => 'Children and youth activities', 'tone' => 2, 'category' => 'Faith'],
  ['src' => $uploads_url . 'emmanuel-ikwuegbu-VC6MGt9ZoBA-unsplash.jpg', 'caption' => 'Supporting children and young people', 'tone' => 3, 'category' => 'Community'],
];
$uploaded_photos = $pdo->query('SELECT caption, category, image_path FROM gallery_photos ORDER BY created_at DESC, id DESC')->fetchAll();
foreach ($uploaded_photos as $photo) {
  $photos[] = [
    'src' => SITE_URL . '/' . $photo['image_path'],
    'caption' => $photo['caption'],
    'tone' => 1,
    'category' => $photo['category'],
  ];
}

// Build the unique, ordered list of categories present in $photos for the filter buttons.
$categories = [];
foreach ($photos as $p) {
    $cat = $p['category'] ?? 'General';
    if (!in_array($cat, $categories, true)) $categories[] = $cat;
}
sort($categories);

$page_title   = 'Gallery';
$page_description = 'Photos and moments from YGGC activities with children, youth and families.';
$current_page = 'gallery';
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container white-container">
    <span class="breadcrumb">Gallery</span>
    <h1>Our work with children and communities.</h1>
    <p>Explore moments from YGGC's education, community support and Christian teaching activities.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <div class="gallery-filters reveal" id="galleryFilters">
      <button type="button" class="filter-pill is-active" data-filter="all">All Photos</button>
      <?php foreach ($categories as $cat): ?>
        <button type="button" class="filter-pill" data-filter="<?= e($cat) ?>"><?= e($cat) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="gallery-grid reveal" id="galleryGrid">
      <?php foreach ($photos as $p):
        $tone = (int) ($p['tone'] ?? 1);
        if ($tone < 1 || $tone > 3) $tone = 1;
        $cat = $p['category'] ?? 'General';
      ?>
        <figure class="gallery-item" data-category="<?= e($cat) ?>">
          <button type="button" class="gallery-trigger" data-src="<?= e($p['src'] ?? '') ?>" data-tone="<?= $tone ?>" data-caption="<?= e($p['caption']) ?>" data-category="<?= e($cat) ?>" aria-label="View larger: <?= e($p['caption']) ?>">
            <?php if (!empty($p['src'])): ?>
              <img src="<?= e($p['src']) ?>" alt="<?= e($p['caption']) ?>" loading="lazy">
            <?php else: ?>
              <div class="gallery-placeholder tone-<?= $tone ?>"></div>
            <?php endif; ?>
            <span class="gallery-zoom-hint" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="22" height="22" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M21 21 L16.5 16.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M11 8 v6 M8 11 h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </span>
          </button>
          <span class="gallery-tag"><?= e($cat) ?></span>
          <figcaption><?= e($p['caption']) ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>

    <p class="gallery-empty is-hidden" id="galleryEmpty">No photos in this category yet — check back soon.</p>
  </div>
</section>

<!-- Lightbox: click any photo above to open it here, click again / outside / Esc to close. -->
<div class="lightbox-backdrop" id="lightboxBackdrop">
  <div class="lightbox-box" id="lightboxBox">
    <button type="button" class="lightbox-close" id="lightboxClose" aria-label="Close">&times;</button>
    <button type="button" class="lightbox-nav lightbox-prev" id="lightboxPrev" aria-label="Previous photo">&larr;</button>
    <div class="lightbox-image-wrap" id="lightboxImageWrap"></div>
    <button type="button" class="lightbox-nav lightbox-next" id="lightboxNext" aria-label="Next photo">&rarr;</button>
    <p class="lightbox-caption" id="lightboxCaption"></p>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
