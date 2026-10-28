<?php
require_once __DIR__ . '/env.php';

$payment_env_currencies = static function (string $key): array {
    $configured = app_env_value($key);
    if ($configured === '') {
        return [];
    }

    $currencies = array_values(array_unique(array_map(
        'strtoupper',
        array_map('trim', explode(',', $configured))
    )));
    $supported = ['USD', 'UGX'];
    if (array_diff($currencies, $supported)) {
        error_log('Unsupported currency in payment configuration: ' . $key);
        throw new RuntimeException('Payment configuration contains an unsupported currency.');
    }

    return $currencies;
};

define('MANUAL_MOMO_NUMBER', app_env_value('MANUAL_MOMO_NUMBER'));
define('MANUAL_BANK_DETAILS', app_env_value('MANUAL_BANK_DETAILS'));
define('MANUAL_MOMO_CURRENCIES', $payment_env_currencies('MANUAL_MOMO_CURRENCIES'));
define('MANUAL_BANK_CURRENCIES', $payment_env_currencies('MANUAL_BANK_CURRENCIES'));
