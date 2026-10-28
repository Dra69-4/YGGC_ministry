<?php
/**
 * Session-based admin auth. Include this at the very top of every
 * protected admin page (before any output).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin_login(): void
{
    if (!admin_is_logged_in()) {
        redirect(SITE_URL . '/admin/login.php');
    }
    // Simple idle timeout: 15 minutes
    if (!empty($_SESSION['admin_last_active']) && (time() - $_SESSION['admin_last_active']) > 900) {
        session_unset();
        session_destroy();
        redirect(SITE_URL . '/admin/login.php?expired=1');
    }
    $_SESSION['admin_last_active'] = time();
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) return null;
    return [
        'id'        => $_SESSION['admin_id'],
        'full_name' => $_SESSION['admin_name'] ?? 'Admin',
        'role'      => $_SESSION['admin_role'] ?? 'admin',
    ];
}

/** Lightweight CSRF token helpers used by all admin forms. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}
