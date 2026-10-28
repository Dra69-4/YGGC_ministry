<?php
/**
 * Expects (optionally) before include:
 *   $page_title       string
 *   $page_description string
 *   $current_page     string  slug used to highlight the active nav link
 */
$page_title       = $page_title ?? 'Young Generation Growing In Christ Ministries';
$page_description = $page_description ?? 'YGGC supports children and families in Isingiro with education, basic needs, safe water and Christian teaching.';
$current_page     = $current_page ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?> | YGGC Ministry</title>
<meta name="description" content="<?= e($page_description) ?>">
<link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/images/dove-icon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Karla:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<?php include __DIR__ . '/navbar.php'; ?>
<main id="main-content">
