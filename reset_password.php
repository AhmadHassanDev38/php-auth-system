<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$errors = [];
$validToken = false;

if ($token !== '') {
    $hash = hash('sha256', $token);
    $stmt = db()->prepare(
        'SELECT prt.id AS token_id, prt.user_id, prt.expires_at
         FROM password_reset_tokens prt
         WHERE prt.token = ? LIMIT 1'
    );
    $stmt->execute([$hash]);
    $reset = $stmt->fetch();

    if ($reset && strtotime($reset['expires_at']) >= time()) {
        $validToken = true;
    } elseif ($reset) {
        $errors[] = 'This password reset link has expired. Please request a new one.';
    } else {
        $errors[] = 'This password reset link is invalid or has already been used.';
    }
} else {
    $errors[] = 'A password reset token is required.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    verify_csrf();

    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!valid_password($newPassword)) {
        $errors[] = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        db()->beginTransaction();

        db()->prepare('UPDATE users SET password = ? WHERE id = ?')
            ->execute([$newHash, (int)$reset['user_id']]);

        db()->prepare('DELETE FROM password_reset_tokens WHERE user_id = ?')
            ->execute([(int)$reset['user_id']]);

        db()->commit();

        flash('success', 'Password reset successful. Please log in with your new password.');
        redirect('login.php');
    }
}

$pageTitle = 'Reset Password';
require __DIR__ . '/header.php';
?>
<div class="card">
    <h1>Reset Password</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <?php if ($validToken): ?>
        <form method="post" data-validate novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <label>New Password</label>
            <input type="password" name="new_password" data-min-password="<?= PASSWORD_MIN_LENGTH ?>" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" data-confirm-password required>

            <button type="submit">Reset Password</button>
        </form>
    <?php else: ?>
        <a class="button" href="forgot_password.php">Request New Reset Link</a>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/footer.php'; ?>
