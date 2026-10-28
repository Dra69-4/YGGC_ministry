<?php
require_once __DIR__ . '/../admin/includes/auth.php';
require_admin_login();

header('Content-Type: application/json');
$pdo = get_db_connection();
$admin = current_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        http_response_code(403);
        echo json_encode(['success' => false]);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE admin_notifications SET is_read = TRUE WHERE admin_id = ? AND is_read = FALSE');
    $stmt->execute([(int) $admin['id']]);
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM admin_notifications WHERE admin_id = ? AND is_read = FALSE');
$stmt->execute([(int) $admin['id']]);
$unread_count = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT n.id, n.donation_id, n.is_read, n.created_at, d.donor_name, d.amount, d.currency, d.transaction_ref
     FROM admin_notifications n
     INNER JOIN donations d ON d.id = n.donation_id
     WHERE n.admin_id = ?
     ORDER BY n.id DESC LIMIT 10'
);
$stmt->execute([(int) $admin['id']]);
$notifications = $stmt->fetchAll();
foreach ($notifications as &$notification) {
    $notification['is_read'] = db_bool($notification['is_read']);
}
unset($notification);

echo json_encode([
    'success' => true,
    'unread_count' => $unread_count,
    'notifications' => $notifications,
]);