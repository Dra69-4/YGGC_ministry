<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();
$pdo = get_db_connection();
$admin = current_admin();

// ---------- Handle create / update / delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Session expired, please try again.');
        redirect(SITE_URL . '/admin/events.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['event_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM events WHERE id = ?');
        $stmt->execute([$id]);
        flash_set('success', 'Event deleted.');
        redirect(SITE_URL . '/admin/events.php');
    }

    // create or update
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date  = $_POST['event_date'] ?? '';
    $event_time  = $_POST['event_time'] ?: null;
    $location    = trim($_POST['location'] ?? '');
    $status      = in_array($_POST['status'] ?? '', ['upcoming', 'completed', 'cancelled'], true) ? $_POST['status'] : 'upcoming';

    $errors = [];
    if ($title === '')      $errors[] = 'Title is required.';
    if ($description === '') $errors[] = 'Description is required.';
    if (!$event_date || !DateTime::createFromFormat('Y-m-d', $event_date)) $errors[] = 'A valid date is required.';
    if ($location === '')   $errors[] = 'Location is required.';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
    } elseif ($action === 'create') {
        $stmt = $pdo->prepare(
            'INSERT INTO events (title, description, event_date, event_time, location, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$title, $description, $event_date, $event_time, $location, $status, $admin['id']]);
        flash_set('success', 'Event created.');
    } elseif ($action === 'update') {
        $id = (int) ($_POST['event_id'] ?? 0);
        $stmt = $pdo->prepare(
            'UPDATE events SET title=?, description=?, event_date=?, event_time=?, location=?, status=? WHERE id=?'
        );
        $stmt->execute([$title, $description, $event_date, $event_time, $location, $status, $id]);
        flash_set('success', 'Event updated.');
    }
    redirect(SITE_URL . '/admin/events.php');
}

$events = $pdo->query('SELECT * FROM events ORDER BY event_date DESC')->fetchAll();

$page_title = 'Events';
$admin_page = 'events';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash_get('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>

<div class="panel">
  <div class="panel-head">
    <h2>All Events</h2>
    <button class="btn btn-primary" data-modal-target="eventModal" onclick="openEventModal()">+ Add Event</button>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead><tr><th>Title</th><th>Date</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($events as $ev): ?>
          <tr>
            <td><?= e($ev['title']) ?></td>
            <td><?= (new DateTime($ev['event_date']))->format('j M Y') ?><?= $ev['event_time'] ? ', ' . (new DateTime($ev['event_time']))->format('g:i A') : '' ?></td>
            <td><?= e($ev['location']) ?></td>
            <td><span class="status-pill status-<?= e($ev['status']) ?>"><?= e(ucfirst($ev['status'])) ?></span></td>
            <td class="actions-cell">
              <button class="action-btn" onclick='openEventModal(<?= json_encode($ev, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
              <form method="POST" onsubmit="return confirm('Delete this event? This cannot be undone.');" class="inline-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="event_id" value="<?= (int) $ev['id'] ?>">
                <button type="submit" class="action-btn danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$events): ?><tr><td colspan="5">No events yet — add your first one.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add / Edit Event Modal -->
<div class="modal-backdrop" id="eventModal">
  <div class="modal-box">
    <button class="modal-close" data-modal-close type="button">&times;</button>
    <h2 id="eventModalTitle">Add Event</h2>
    <form method="POST" id="eventForm">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" id="eventAction" value="create">
      <input type="hidden" name="event_id" id="eventId" value="">
      <div class="form-grid">
        <div class="form-field full">
          <label for="title">Title</label>
          <input type="text" name="title" id="title" required>
        </div>
        <div class="form-field full">
          <label for="description">Description</label>
          <textarea name="description" id="description" required></textarea>
        </div>
        <div class="form-field">
          <label for="event_date">Date</label>
          <input type="date" name="event_date" id="event_date" required>
        </div>
        <div class="form-field">
          <label for="event_time">Time (optional)</label>
          <input type="time" name="event_time" id="event_time">
        </div>
        <div class="form-field full">
          <label for="location">Location</label>
          <input type="text" name="location" id="location" required>
        </div>
        <div class="form-field full">
          <label for="status">Status</label>
          <select name="status" id="status">
            <option value="upcoming">Upcoming</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="form-field full">
          <button type="submit" class="btn btn-primary btn-block">Save Event</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
function openEventModal(ev) {
  document.getElementById('eventModal').classList.add('is-open');
  var isEdit = !!(ev && ev.id);
  document.getElementById('eventModalTitle').textContent = isEdit ? 'Edit Event' : 'Add Event';
  document.getElementById('eventAction').value = isEdit ? 'update' : 'create';
  document.getElementById('eventId').value = isEdit ? ev.id : '';
  document.getElementById('title').value = isEdit ? ev.title : '';
  document.getElementById('description').value = isEdit ? ev.description : '';
  document.getElementById('event_date').value = isEdit ? ev.event_date : '';
  document.getElementById('event_time').value = isEdit && ev.event_time ? ev.event_time.substring(0,5) : '';
  document.getElementById('location').value = isEdit ? ev.location : '';
  document.getElementById('status').value = isEdit ? ev.status : 'upcoming';
}
</script>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
