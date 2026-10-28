<?php
/**
 * Handles POST submissions from contact.php and get-involved.php.
 * Always responds with JSON: { success: bool, message: string }
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$body    = trim($_POST['message'] ?? '');
$source  = ($_POST['source'] ?? 'contact') === 'volunteer' ? 'volunteer' : 'contact';

$errors = [];
if ($name === '' || mb_strlen($name) < 2)               $errors[] = 'Please enter your full name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))          $errors[] = 'Please enter a valid email address.';
if ($subject === '')                                     $errors[] = 'Please add a subject.';
if ($body === '' || mb_strlen($body) < 5)                 $errors[] = 'Your message is too short.';

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare(
        'INSERT INTO messages (name, email, phone, subject, body, source) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $phone ?: null, $subject, $body, $source]);

    $friendly = $source === 'volunteer'
        ? "Thanks, $name! We've received your volunteer interest and will reach out soon."
        : "Thanks, $name! Your message has been sent — we'll get back to you shortly.";

    echo json_encode(['success' => true, 'message' => $friendly]);
} catch (Throwable $e) {
    error_log('message.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong on our end. Please try again later.']);
}
