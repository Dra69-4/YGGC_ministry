<?php
require_once __DIR__ . '/includes/auth.php';
session_unset();
session_destroy();
redirect(SITE_URL . '/admin/login.php');
