<?php $current_page = $current_page ?? ''; ?>
<header class="site-header" id="site-header">
  <div class="nav-wrap">
    <a href="<?= SITE_URL ?>/index.php" class="brand">
      <img class="logo-mark" src="<?= SITE_URL ?>/assets/images/logo.png" alt="YGGC Ministry">
    </a>

    <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="primaryNav" aria-label="Toggle navigation menu">
      <span></span><span></span><span></span>
    </button>

    <nav id="primaryNav" class="primary-nav">
      <ul>
        <li><a href="<?= SITE_URL ?>/index.php"        class="<?= $current_page === 'home' ? 'active' : '' ?>">Home</a></li>
        <li><a href="<?= SITE_URL ?>/about.php"         class="<?= $current_page === 'about' ? 'active' : '' ?>">About</a></li>
        <li><a href="<?= SITE_URL ?>/ministries.php"    class="<?= $current_page === 'ministries' ? 'active' : '' ?>">Activities</a></li>
        <li><a href="<?= SITE_URL ?>/events.php"        class="<?= $current_page === 'events' ? 'active' : '' ?>">Events</a></li>
        <li><a href="<?= SITE_URL ?>/gallery.php"       class="<?= $current_page === 'gallery' ? 'active' : '' ?>">Gallery</a></li>
        <li><a href="<?= SITE_URL ?>/get-involved.php"  class="<?= $current_page === 'get-involved' ? 'active' : '' ?>">Get Involved</a></li>
        <li class="nav-dropdown <?= in_array($current_page, ['contact', 'team'], true) ? 'is-current' : '' ?>">
          <a href="<?= SITE_URL ?>/contact.php" class="nav-dropdown-toggle <?= in_array($current_page, ['contact', 'team'], true) ? 'active' : '' ?>">
            Contact Us <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
          </a>
          <div class="nav-dropdown-menu">
            <a href="<?= SITE_URL ?>/team.php" class="<?= $current_page === 'team' ? 'active' : '' ?>">Our Team</a>
          </div>
        </li>
        <li><a href="<?= SITE_URL ?>/donate.php" class="btn-nav-donate <?= $current_page === 'donate' ? 'active' : '' ?>">Donate</a></li>
      </ul>
    </nav>
  </div>
</header>
