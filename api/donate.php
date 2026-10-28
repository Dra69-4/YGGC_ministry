<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/payment.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// ---------- 1. Validate ----------
$donor_name     = trim($_POST['donor_name'] ?? '');
$donor_email    = trim($_POST['donor_email'] ?? '');
$donor_phone    = trim($_POST['donor_phone'] ?? '');
$amount         = filter_var($_POST['amount'] ?? '', FILTER_VALIDATE_FLOAT);
$currency       = strtoupper(trim($_POST['currency'] ?? 'USD'));
$fund           = trim($_POST['fund'] ?? 'General Fund');
$frequency      = ($_POST['frequency'] ?? 'one_time') === 'monthly' ? 'monthly' : 'one_time';
$payment_method = $_POST['payment_method'] ?? '';
$message        = trim($_POST['message'] ?? '');
$is_anonymous   = !empty($_POST['is_anonymous']) ? 'true' : 'false';

$allowed_methods = ['mobile_money', 'bank_transfer'];
$allowed_currencies = ['USD', 'UGX'];
$method_currencies = match ($payment_method) {
    'mobile_money' => MANUAL_MOMO_NUMBER !== '' ? array_intersect(['USD', 'UGX'], MANUAL_MOMO_CURRENCIES) : [],
    'bank_transfer' => MANUAL_BANK_DETAILS !== '' ? array_intersect(['USD', 'UGX'], MANUAL_BANK_CURRENCIES) : [],
    default => [],
};

$errors = [];
if ($donor_name === '' || mb_strlen($donor_name) < 2)                $errors[] = 'Please enter your full name.';
if (!filter_var($donor_email, FILTER_VALIDATE_EMAIL))                 $errors[] = 'Please enter a valid email address.';
if (
    $amount === false
    || !is_finite((float) $amount)
    || $amount < ($currency === 'UGX' ? 5000 : 5)
    || $amount > 9999999999.99
    || ($currency === 'UGX' && floor((float) $amount) !== (float) $amount)
    || ($currency === 'USD' && round((float) $amount, 2) !== (float) $amount)
) {
    $errors[] = 'Please enter a valid donation amount.';
}
if (!in_array($currency, $allowed_currencies, true))                  $errors[] = 'Please choose a supported currency.';
if (!in_array($payment_method, $allowed_methods, true))               $errors[] = 'Please choose a valid payment method.';
if ($payment_method === 'mobile_money' && $donor_phone === '')        $errors[] = 'Phone number is required for Mobile Money.';

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$payment_details = match ($payment_method) {
    'bank_transfer' => MANUAL_BANK_DETAILS,
    'mobile_money' => MANUAL_MOMO_NUMBER,
};
if (trim($payment_details) === '' || !in_array($currency, $method_currencies, true)) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'This payment method is not configured for the selected currency yet. Please contact the ministry to arrange your donation.',
    ]);
    exit;
}

$fund = mb_substr($fund !== '' ? $fund : 'General Fund', 0, 80);

try {
    $pdo = get_db_connection();

    // ---------- 2. Insert pending donation with a unique reference ----------
    $transaction_ref = generate_transaction_ref();
    $stmt = $pdo->prepare(
        "INSERT INTO donations
            (donor_name, donor_email, donor_phone, amount, currency, fund, frequency, payment_method, transaction_ref, status, message, is_anonymous)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)"
    );
    $stmt->execute([
        $donor_name, $donor_email, $donor_phone ?: null, $amount, $currency,
        $fund, $frequency, $payment_method, $transaction_ref, $message ?: null, $is_anonymous,
    ]);
    // ---------- 3. Manual payment instructions ----------
    $frequency_note = $frequency === 'monthly'
        ? " This is recorded as a monthly gift — our team will reach out to set up your recurring payment."
        : '';

    echo json_encode([
        'success' => true,
        'message' => "Thank you, {$donor_name}! Your donation of " . format_money((float) $amount, $currency)
            . " has been recorded as pending (ref: {$transaction_ref}). To complete it, please send payment to: {$payment_details}. "
            . "The ministry will confirm it after verifying the payment.{$frequency_note}",
    ]);
} catch (Throwable $e) {
    error_log('donate.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong while recording your donation. Please try again shortly.']);
}
