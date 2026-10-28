<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();
$pdo = get_db_connection();

$categories = ['Community', 'School Ministries', 'Faith'];
$upload_dir = __DIR__ . '/../uploads/gallery';
$upload_prefix = 'uploads/gallery/';

function remove_gallery_photo(?string $image_path): void
{
    if (!$image_path || !preg_match('/^uploads\/gallery\/[a-f0-9]{32}\.(jpg|png|webp)$/', $image_path)) {
        return;
    }

    $full_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $image_path);
    if (is_file($full_path)) {
        unlink($full_path);
    }
}

function save_gallery_photo(array $upload, string $upload_dir, string $upload_prefix): string
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
        throw new RuntimeException('The gallery upload folder is not writable.');
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
        redirect(SITE_URL . '/admin/gallery.php');
    }

    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $photo_id = (int) ($_POST['photo_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT image_path FROM gallery_photos WHERE id = ?');
        $stmt->execute([$photo_id]);
        $photo = $stmt->fetch();
        $stmt = $pdo->prepare('DELETE FROM gallery_photos WHERE id = ?');
        $stmt->execute([$photo_id]);
        if ($photo) {
            remove_gallery_photo($photo['image_path']);
        }
        flash_set('success', 'Gallery photo removed.');
        redirect(SITE_URL . '/admin/gallery.php');
    }

    if ($action !== 'upload') {
        flash_set('error', 'Choose a valid gallery action.');
        redirect(SITE_URL . '/admin/gallery.php');
    }

    $caption = trim($_POST['caption'] ?? '');
    $category = $_POST['category'] ?? '';
    $errors = [];
    if ($caption === '' || mb_strlen($caption) > 160) $errors[] = 'Caption is required and must be 160 characters or fewer.';
    if (!in_array($category, $categories, true)) $errors[] = 'Choose a valid gallery category.';
    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(SITE_URL . '/admin/gallery.php');
    }

    $image_path = null;
    try {
        $image_path = save_gallery_photo($_FILES['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE], $upload_dir, $upload_prefix);
        $stmt = $pdo->prepare('INSERT INTO gallery_photos (caption, category, image_path) VALUES (?, ?, ?)');
        $stmt->execute([$caption, $category, $image_path]);
        flash_set('success', 'Gallery photo uploaded.');
    } catch (RuntimeException $exception) {
        if ($image_path) remove_gallery_photo($image_path);
        flash_set('error', $exception->getMessage());
    } catch (Throwable $exception) {
        if ($image_path) remove_gallery_photo($image_path);
        error_log('Gallery photo save failed: ' . $exception->getMessage());
        flash_set('error', 'The gallery photo could not be saved. Please try again.');
    }

    redirect(SITE_URL . '/admin/gallery.php');
}

$photos = $pdo->query('SELECT * FROM gallery_photos ORDER BY created_at DESC, id DESC')->fetchAll();
$page_title = 'Gallery';
$admin_page = 'gallery';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($msg = flash_get('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash_get('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>

<section class="panel">
  <div class="panel-head"><h2>Upload Gallery Photo</h2></div>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="upload">
    <div class="form-grid">
      <div class="form-field">
        <label for="galleryCaption">Caption</label>
        <input id="galleryCaption" name="caption" type="text" maxlength="160" required>
      </div>
      <div class="form-field">
        <label for="galleryCategory">Category</label>
        <select id="galleryCategory" name="category" required>
          <?php foreach ($categories as $category): ?>
            <option value="<?= e($category) ?>"><?= e($category) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-field full">
        <label for="galleryPhoto">Photo <span class="field-hint">JPG, PNG or WebP, up to 5 MB</span></label>
        <input id="galleryPhoto" name="photo" type="file" accept="image/jpeg,image/png,image/webp" required>
      </div>
      <div class="form-field full">
        <button type="submit" class="btn btn-primary">Upload Photo</button>
      </div>
    </div>
  </form>
</section>

<section class="panel">
  <div class="panel-head"><h2>Uploaded Photos</h2><span><?= count($photos) ?> total</span></div>
  <div class="table-scroll">
    <table class="data-table">
      <thead><tr><th>Photo</th><th>Caption</th><th>Category</th><th>Uploaded</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($photos as $photo): ?>
          <tr>
            <td><img class="gallery-admin-thumb" src="<?= e(SITE_URL . '/' . $photo['image_path']) ?>" alt=""></td>
            <td><?= e($photo['caption']) ?></td>
            <td><?= e($photo['category']) ?></td>
            <td><?= e(date('j M Y', strtotime($photo['created_at']))) ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('Remove this photo from the gallery?');" class="inline-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="photo_id" value="<?= (int) $photo['id'] ?>">
                <button type="submit" class="action-btn danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$photos): ?><tr><td colspan="5">No uploaded photos yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>