<?php
/** Expects $admin_page (slug) and $page_title to be set before include. */
$admin = current_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Dashboard') ?> — Admin | YGGC Ministries</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../../assets/css/admin.css') ?>">
</head>
<body class="admin-body" data-admin-page="<?= e($admin_page ?? '') ?>">
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <a href="<?= SITE_URL ?>/admin/dashboard.php" class="admin-brand">YGGC<span>Admin</span></a>
    <nav class="admin-nav">
      <a href="<?= SITE_URL ?>/admin/dashboard.php" class="<?= ($admin_page ?? '') === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-simple" style="color: #ffff;"></i> Dashboard</a>
      <a href="<?= SITE_URL ?>/admin/donations.php" class="<?= ($admin_page ?? '') === 'donations' ? 'active' : '' ?>"><i class="fa-brands fa-gratipay" style="color: #ffff;"></i> Donations</a>
      <a href="<?= SITE_URL ?>/admin/events.php" class="<?= ($admin_page ?? '') === 'events' ? 'active' : '' ?>"><i class="fa-solid fa-calendar" style="color: #ffff;"></i> Events</a>
      <a href="<?= SITE_URL ?>/admin/messages.php" class="<?= ($admin_page ?? '') === 'messages' ? 'active' : '' ?>"><i class="fa-solid fa-message" style="color: #ffff;"></i> Messages</a>
      <a href="<?= SITE_URL ?>/admin/team.php" class="<?= ($admin_page ?? '') === 'team' ? 'active' : '' ?>"><i class="fa-solid fa-people-group" style="color: #ffff;"></i> Team</a>
      <a href="<?= SITE_URL ?>/admin/gallery.php" class="<?= ($admin_page ?? '') === 'gallery' ? 'active' : '' ?>"><i class="fa-solid fa-images" style="color: #ffff;"></i> Gallery</a>
      <a href="<?= SITE_URL ?>/admin/stats.php" class="<?= ($admin_page ?? '') === 'stats' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line" style="color: #ffff;"></i> Site Stats</a>
      <a href="<?= SITE_URL ?>/admin/change-password.php" class="<?= ($admin_page ?? '') === 'change-password' ? 'active' : '' ?>"><i class="fa-solid fa-key" style="color: #ffff;"></i> Change Password</a>
      <div class="admin-nav-divider"></div>
      <a href="<?= SITE_URL ?>/index.php" target="_blank"><i class="fa-solid fa-globe" style="color: #ffff;"></i> View Website</a>
      <a href="<?= SITE_URL ?>/admin/logout.php"><i class="fa-solid fa-right-from-bracket" style="color: #ffff;"></i> Logout</a>
    </nav>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="admin-menu-toggle" id="adminMenuToggle" aria-label="Toggle menu">☰</button>
      <h1><?= e($page_title ?? 'Dashboard') ?></h1>
      <div class="admin-notifications" id="adminNotifications" data-endpoint="<?= SITE_URL ?>/api/admin-notifications.php" data-donations-url="<?= SITE_URL ?>/admin/donations.php" data-csrf="<?= e(csrf_token()) ?>">
        <button type="button" class="admin-notifications-toggle" id="adminNotificationsToggle" aria-expanded="false" aria-controls="adminNotificationsPanel">
          <span aria-hidden="true"><i class="fa-solid fa-bell" style="color: #FFD700;"></i></span><span>Notifications</span><span class="admin-notification-count" id="adminNotificationCount" hidden>0</span>
        </button>
        <div class="admin-notifications-panel" id="adminNotificationsPanel" hidden>
          <div class="admin-notifications-heading">Payment notifications</div>
          <ul id="adminNotificationList" class="admin-notification-list"><li class="admin-notification-empty">Loading notifications...</li></ul>
        </div>
      </div>
      <div class="admin-user">Hi, <?= e($admin['full_name'] ?? 'Admin') ?></div>
    </header>
    <div class="admin-content">
