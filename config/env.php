<?php

$app_env_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
$app_env = [];
if (is_file($app_env_path)) {
    $app_env = parse_ini_file($app_env_path, false, INI_SCANNER_RAW);
    if ($app_env === false) {
        error_log('Could not parse application configuration from the .env file.');
        throw new RuntimeException('Application configuration is invalid.');
    }
}

function app_env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value !== false) {
        return trim($value);
    }

    global $app_env;
    return trim((string) ($app_env[$key] ?? $default));
}
