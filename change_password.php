<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$user = require_verified();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!password_verify($oldPassword, $user['password'])) {
        $errors[] = 'Old password is incorrect.';
    }

    if (!valid_password($newPassword)) {
        $errors[] = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirmation do not match.';
    }

    if (!$errors) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        db()->prepare('UPDATE users SET password = ? WHERE id = ?')
            ->execute([$hash, (int)$user['id']]);

        flash('success', 'Password changed successfully.');
        redirect('profile.php');
    }
}

$pageTitle = 'Change Password';
require __DIR__ . '/header.php';
?>
<div class="card">
    <h1>Change Password</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" data-validate novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>Old Password</label>
        <input type="password" name="old_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" data-min-password="<?= PASSWORD_MIN_LENGTH ?>" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" data-confirm-password required>

        <button type="submit">Change Password</button>
    </form>

    <p><a href="profile.php">Back to Profile</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>
