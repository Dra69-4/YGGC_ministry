<?php
// Polyfills — mbstring is enabled on virtually all hosting (cPanel, XAMPP,
// LAMP) by default, but these fallbacks keep the site working even if it
// isn't, without changing any calling code elsewhere in the project.
if (!function_exists('mb_strlen'))   { function mb_strlen($s, $enc = null)   { return strlen($s); } }
if (!function_exists('mb_substr'))   { function mb_substr($s, $start, $len = null, $enc = null) { return substr($s, $start, $len); } }
if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth($s, $start, $width, $trimmarker = '', $enc = null) {
        if (strlen($s) <= $width) return $s;
        return substr($s, $start, $width - strlen($trimmarker)) . $trimmarker;
    }
}


function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function db_bool(mixed $value): bool
{
    return $value === true || $value === 1 || $value === '1'
        || (is_string($value) && in_array(strtolower(trim($value)), ['t', 'true', 'yes', 'on'], true));
}

function format_money(float $amount, string $currency = 'USD'): string
{
    $symbols = ['UGX' => 'USh', 'USD' => '$'];
    $symbol  = $symbols[$currency] ?? $currency . ' ';
    return $symbol . ' ' . number_format($amount, $currency === 'UGX' ? 0 : 2);
}

function format_date(string $date, string $format = 'j M Y'): string
{
    $dt = DateTime::createFromFormat('Y-m-d', $date) ?: new DateTime($date);
    return $dt->format($format);
}

function generate_transaction_ref(): string
{
    return 'YGGC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

/** Mark a donation completed once and notify every admin in the same transaction. */
function complete_donation(PDO $pdo, int $donation_id, bool $allow_non_pending = false): bool
{
    $pdo->beginTransaction();
    try {
        $condition = $allow_non_pending ? "status <> 'completed'" : "status = 'pending'";
        $stmt = $pdo->prepare("UPDATE donations SET status = 'completed' WHERE id = ? AND {$condition}");
        $stmt->execute([$donation_id]);

        if ($stmt->rowCount() === 0) {
            $pdo->commit();
            return false;
        }

        $stmt = $pdo->prepare(
              'INSERT INTO admin_notifications (admin_id, donation_id)
               SELECT id, ? FROM admins
               ON CONFLICT (admin_id, donation_id) DO NOTHING'
        );
        $stmt->execute([$donation_id]);
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/** Fetch a small set of site-wide stats as [key => ['label'=>.., 'value'=>..]] */
function get_site_stats(PDO $pdo): array
{
    $stmt  = $pdo->query('SELECT stat_key, stat_label, stat_value FROM site_stats');
    $stats = [];
    foreach ($stmt->fetchAll() as $row) {
        $stats[$row['stat_key']] = ['label' => $row['stat_label'], 'value' => (int) $row['stat_value']];
    }
    return $stats;
}

/** Total confirmed donations (for the home page "raised so far" widget). */
function get_total_raised(PDO $pdo, string $currency = 'USD'): float
{
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS total FROM donations WHERE status = 'completed' AND currency = ?");
    $stmt->execute([$currency]);
    return (float) $stmt->fetchColumn();
}

function get_upcoming_events(PDO $pdo, int $limit = 3): array
{
    $stmt = $pdo->prepare(
        "SELECT * FROM events WHERE status = 'upcoming' AND event_date >= CURRENT_DATE
         ORDER BY event_date ASC LIMIT ?"
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_active_ministries(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM ministries WHERE is_active = TRUE ORDER BY display_order ASC, id ASC");
    return $stmt->fetchAll();
}

/**
 * The current fundraising campaign, used to draw the progress "thermometer"
 * on the Home and Donate pages. Returns null if no active campaign is set.
 * The 'raised' key is computed live from completed donations in the same
 * currency — it is not stored on the campaign row itself.
 */
function get_campaign_goal(PDO $pdo): ?array
{
    $stmt = $pdo->query("SELECT * FROM campaign_goal WHERE is_active = TRUE LIMIT 1");
    $goal = $stmt->fetch();
    if (!$goal || $goal['currency'] !== 'USD') return null;

    $raised = get_total_raised($pdo, $goal['currency']);
    $percent = (float) $goal['goal_amount'] > 0
        ? min(100, round(($raised / (float) $goal['goal_amount']) * 100))
        : 0;

    $goal['raised']  = $raised;
    $goal['percent'] = $percent;
    return $goal;
}

/** Round a 0-100 percentage down to the nearest 5, for the .thermo-w-N CSS step classes. */
function thermo_step(float $percent): int
{
    $step = (int) (round($percent / 5) * 5);
    return max(0, min(100, $step));
}

/** Simple flash-message helper using the session. */
function flash_set(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    if (!empty($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}
