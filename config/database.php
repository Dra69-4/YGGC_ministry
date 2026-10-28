<?php
require_once __DIR__ . '/env.php';

define('DB_HOST', app_env_value('DB_HOST'));
define('DB_PORT', app_env_value('DB_PORT', '5432'));
define('DB_NAME', app_env_value('DB_NAME'));
define('DB_USER', app_env_value('DB_USER'));
define('DB_PASS', app_env_value('DB_PASS'));

// Derive the app path from the requested script for local root or subdirectory hosting.
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
if (in_array(basename($script_dir), ['admin', 'api'], true)) {
    $script_dir = dirname($script_dir);
}
$script_dir = str_replace('\\', '/', $script_dir);
define('SITE_URL', rtrim($script_dir, '/'));

function get_db_connection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        if (DB_HOST === '' || DB_NAME === '' || DB_USER === '' || DB_PASS === '') {
            error_log('Database configuration is incomplete. Set DB_HOST, DB_NAME, DB_USER, and DB_PASS in .env or the server environment.');
            http_response_code(500);
            die('Sorry, the site is temporarily unavailable. Please try again shortly.');
        }

        $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never leak credentials or raw exception details to visitors.
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('Sorry, the site is temporarily unavailable. Please try again shortly.');
        }
    }

    return $pdo;
}