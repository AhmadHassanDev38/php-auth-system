<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$user = require_verified();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $newPhoto = null;

    if ($name === '') {
        $errors[] = 'Name cannot be empty.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Name cannot exceed 100 characters.';
    }

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_photo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Profile photo upload failed.';
        } elseif ($file['size'] > MAX_PROFILE_PHOTO_BYTES) {
            $errors[] = 'Profile photo must be 2 MB or smaller.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
            ];

            if (!isset($allowed[$mime])) {
                $errors[] = 'Only JPG, JPEG, and PNG images are allowed.';
            } elseif (@getimagesize($file['tmp_name']) === false) {
                $errors[] = 'Uploaded file is not a valid image.';
            } else {
                $newPhoto = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
            }
        }
    }

    if (!$errors) {
        if ($newPhoto) {
            $uploadDir = __DIR__ . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . '/' . $newPhoto)) {
                $errors[] = 'Could not save the profile photo.';
            } else {
                if (!empty($user['profile_photo'])) {
                    $old = $uploadDir . '/' . basename($user['profile_photo']);
                    if (is_file($old)) @unlink($old);
                }

                db()->prepare('UPDATE users SET name = ?, profile_photo = ? WHERE id = ?')
                    ->execute([$name, $newPhoto, (int)$user['id']]);
            }
        }

        if (!$errors) {
            if (!$newPhoto) {
                db()->prepare('UPDATE users SET name = ? WHERE id = ?')
                    ->execute([$name, (int)$user['id']]);
            }

            flash('success', 'Profile updated successfully.');
            redirect('profile.php');
        }
    }
}

$pageTitle = 'Edit Profile';
require __DIR__ . '/header.php';
?>
<div class="card">
    <h1>Edit Profile</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data" data-validate novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>Name</label>
        <input type="text" name="name" value="<?= e($_POST['name'] ?? $user['name']) ?>" required maxlength="100">

        <label>Profile Photo</label>
        <input type="file" name="profile_photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
        <small>JPG, JPEG, or PNG. Maximum 2 MB.</small>

        <button type="submit">Save Changes</button>
    </form>

    <p><a href="profile.php">Cancel</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>
