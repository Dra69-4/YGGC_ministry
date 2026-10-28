<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();
$pdo = get_db_connection();

$upload_dir = __DIR__ . '/../uploads/team';
$upload_prefix = 'uploads/team/';

function remove_team_photo(?string $photo_path): void
{
    if (!$photo_path || !preg_match('/^uploads\/team\/[a-f0-9]{32}\.(jpg|png|webp)$/', $photo_path)) {
        return;
    }

    $full_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $photo_path);
    if (is_file($full_path)) {
        unlink($full_path);
    }
}

function save_team_photo(array $upload, string $upload_dir, string $upload_prefix): string
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Choose a valid image to upload.');
    }
    if (($upload['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('The photo must be 5 MB or smaller.');
    }

    $file_info = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $file_info->file($upload['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime_type]) || !getimagesize($upload['tmp_name'])) {
        throw new RuntimeException('Upload a JPG, PNG, or WebP image.');
    }

    if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
        throw new RuntimeException('The team photo upload folder is not writable.');
    }

    $file_name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime_type];
    if (!move_uploaded_file($upload['tmp_name'], $upload_dir . DIRECTORY_SEPARATOR . $file_name)) {
        throw new RuntimeException('The photo could not be saved. Check the upload folder permissions.');
    }

    return $upload_prefix . $file_name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Session expired, please try again.');
        redirect(SITE_URL . '/admin/team.php');
    }

    $action = $_POST['action'] ?? '';
    $member_id = (int) ($_POST['member_id'] ?? 0);

    if ($action === 'delete') {
        $stmt = $pdo->prepare('SELECT photo_path FROM team_members WHERE id = ?');
        $stmt->execute([$member_id]);
        $member = $stmt->fetch();
        $stmt = $pdo->prepare('DELETE FROM team_members WHERE id = ?');
        $stmt->execute([$member_id]);
        if ($member) {
            remove_team_photo($member['photo_path']);
        }
        flash_set('success', 'Team member removed.');
        redirect(SITE_URL . '/admin/team.php');
    }

      if (!in_array($action, ['create', 'update'], true)) {
        flash_set('error', 'Choose a valid team action.');
        redirect(SITE_URL . '/admin/team.php');
      }

    $name = trim($_POST['name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $display_order = filter_var($_POST['display_order'] ?? 0, FILTER_VALIDATE_INT);
    $is_active = !empty($_POST['is_active']) ? 'true' : 'false';
    $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if ($role === '') $errors[] = 'Role or position is required.';
    if ($display_order === false || $display_order < 0) $errors[] = 'Display order must be zero or higher.';

    $existing = null;
    if ($action === 'update') {
        $stmt = $pdo->prepare('SELECT * FROM team_members WHERE id = ?');
        $stmt->execute([$member_id]);
        $existing = $stmt->fetch();
        if (!$existing) $errors[] = 'That team member could not be found.';
    }

    $photo_upload = $_FILES['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE];
    if (!$existing && ($photo_upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Choose a profile photo for this team member.';
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(SITE_URL . '/admin/team.php');
    }

    $new_photo_path = null;
    try {
        if (($photo_upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $new_photo_path = save_team_photo($photo_upload, $upload_dir, $upload_prefix);
        }

        if ($action === 'update') {
            $photo_path = $new_photo_path ?? $existing['photo_path'];
            $stmt = $pdo->prepare('UPDATE team_members SET name=?, role=?, bio=?, photo_path=?, display_order=?, is_active=? WHERE id=?');
            $stmt->execute([$name, $role, $bio ?: null, $photo_path, $display_order, $is_active, $member_id]);
            if ($new_photo_path) remove_team_photo($existing['photo_path']);
            flash_set('success', 'Team member updated.');
        } elseif ($action === 'create') {
            $stmt = $pdo->prepare('INSERT INTO team_members (name, role, bio, photo_path, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $role, $bio ?: null, $new_photo_path, $display_order, $is_active]);
            flash_set('success', 'Team member added.');
        }
    } catch (RuntimeException $exception) {
        if ($new_photo_path) remove_team_photo($new_photo_path);
        flash_set('error', $exception->getMessage());
    } catch (Throwable $exception) {
        if ($new_photo_path) remove_team_photo($new_photo_path);
        error_log('Team member save failed: ' . $exception->getMessage());
        flash_set('error', 'The team member could not be saved. Please try again.');
    }

    redirect(SITE_URL . '/admin/team.php');
}

$members = $pdo->query('SELECT * FROM team_members ORDER BY display_order ASC, name ASC')->fetchAll();
$page_title = 'Team';
$admin_page = 'team';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash_get('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>

<div class="team-admin-layout">
  <section class="panel team-editor">
    <div class="panel-head"><h2 id="teamFormTitle">Add Team Member</h2></div>
    <form method="POST" enctype="multipart/form-data" id="teamMemberForm">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" id="teamAction" value="create">
      <input type="hidden" name="member_id" id="teamMemberId" value="">
      <div class="form-grid">
        <div class="form-field">
          <label for="teamName">Full Name</label>
          <input id="teamName" name="name" type="text" maxlength="120" required>
        </div>
        <div class="form-field">
          <label for="teamRole">Role or Position</label>
          <input id="teamRole" name="role" type="text" maxlength="120" required>
        </div>
        <div class="form-field full">
          <label for="teamBio">Short Biography</label>
          <textarea id="teamBio" name="bio" maxlength="1000" rows="4" placeholder="A short introduction to this team member"></textarea>
        </div>
        <div class="form-field">
          <label for="teamPhoto">Profile Photo <span class="field-hint">JPG, PNG or WebP, up to 5 MB</span></label>
          <input id="teamPhoto" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
          <img id="teamPhotoPreview" class="team-photo-preview" alt="Selected profile preview" hidden>
        </div>
        <div class="form-field">
          <label for="teamOrder">Display Order</label>
          <input id="teamOrder" name="display_order" type="number" min="0" value="0">
        </div>
        <div class="form-field checkbox-row">
          <input id="teamActive" name="is_active" type="checkbox" value="1" class="checkbox-input" checked>
          <label for="teamActive">Show on public Team page</label>
        </div>
        <div class="form-field full team-form-actions">
          <button type="submit" class="btn btn-primary" id="teamSaveButton">Add Team Member</button>
          <button type="button" class="btn btn-outline" id="teamCancelEdit" hidden>Cancel Edit</button>
        </div>
      </div>
    </form>
  </section>

  <section class="panel team-list-panel">
    <div class="panel-head"><h2>Team Members</h2><span><?= count($members) ?> total</span></div>
    <?php if ($members): ?>
      <div class="team-admin-list">
        <?php foreach ($members as $member): ?>
          <article class="team-admin-row">
            <img src="<?= e(SITE_URL . '/' . $member['photo_path']) ?>" alt="" class="team-admin-thumb">
            <div class="team-admin-info">
              <h3><?= e($member['name']) ?></h3>
              <p><?= e($member['role']) ?></p>
              <span class="status-pill <?= db_bool($member['is_active']) ? 'status-completed' : 'status-cancelled' ?>"><?= db_bool($member['is_active']) ? 'Visible' : 'Hidden' ?></span>
            </div>
            <div class="team-admin-actions">
              <button type="button" class="action-btn" data-team-member="<?= e(json_encode($member, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP)) ?>">Edit</button>
              <form method="POST" onsubmit="return confirm('Remove this team member and photo?');">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="member_id" value="<?= (int) $member['id'] ?>">
                <button type="submit" class="action-btn danger">Remove</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="team-admin-empty">No team members added yet. Add a profile and photo to introduce your team.</p>
    <?php endif; ?>
  </section>
</div>

<script>
(function () {
  var form = document.getElementById('teamMemberForm');
  var photoInput = document.getElementById('teamPhoto');
  var preview = document.getElementById('teamPhotoPreview');
  var cancelButton = document.getElementById('teamCancelEdit');

  function resetForm() {
    form.reset();
    document.getElementById('teamAction').value = 'create';
    document.getElementById('teamMemberId').value = '';
    document.getElementById('teamFormTitle').textContent = 'Add Team Member';
    document.getElementById('teamSaveButton').textContent = 'Add Team Member';
    document.getElementById('teamActive').checked = true;
    preview.hidden = true;
    preview.removeAttribute('src');
    cancelButton.hidden = true;
  }

  document.querySelectorAll('[data-team-member]').forEach(function (button) {
    button.addEventListener('click', function () {
      var member = JSON.parse(button.getAttribute('data-team-member'));
      document.getElementById('teamAction').value = 'update';
      document.getElementById('teamMemberId').value = member.id;
      document.getElementById('teamName').value = member.name;
      document.getElementById('teamRole').value = member.role;
      document.getElementById('teamBio').value = member.bio || '';
      document.getElementById('teamOrder').value = member.display_order;
      document.getElementById('teamActive').checked = member.is_active === true || member.is_active === 't' || member.is_active === '1' || member.is_active === 1;
      document.getElementById('teamFormTitle').textContent = 'Edit Team Member';
      document.getElementById('teamSaveButton').textContent = 'Save Changes';
      preview.src = '<?= e(SITE_URL) ?>/' + member.photo_path;
      preview.hidden = false;
      cancelButton.hidden = false;
      photoInput.value = '';
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  photoInput.addEventListener('change', function () {
    if (photoInput.files && photoInput.files[0]) {
      preview.src = URL.createObjectURL(photoInput.files[0]);
      preview.hidden = false;
    }
  });
  cancelButton.addEventListener('click', resetForm);
}());
</script>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
