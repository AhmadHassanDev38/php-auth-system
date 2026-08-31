<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$token = trim($_GET['token'] ?? '');
$message = '';
$type = 'error';

if ($token === '') {
    $message = 'Invalid verification link.';
} else {
    $hash = hash('sha256', $token);

    $stmt = db()->prepare(
        'SELECT evt.id AS token_id, evt.user_id, evt.expires_at, u.email_verified_at
         FROM email_verification_tokens evt
         JOIN users u ON u.id = evt.user_id
         WHERE evt.token = ? LIMIT 1'
    );
    $stmt->execute([$hash]);
    $row = $stmt->fetch();

    if (!$row) {
        $message = 'This verification link is invalid or has already been used.';
    } elseif (strtotime($row['expires_at']) < time()) {
        $message = 'This verification link has expired. Please use the resend button below.';
        $expired = true;
    } else {
        db()->beginTransaction();

        $update = db()->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = ? AND email_verified_at IS NULL');
        $update->execute([(int)$row['user_id']]);

        db()->prepare('DELETE FROM email_verification_tokens WHERE id = ?')->execute([(int)$row['token_id']]);
        db()->commit();

        $message = 'Your email has been verified successfully.';
        $type = 'success';
    }
}

$pageTitle = 'Email Verification';
require __DIR__ . '/header.php';
?>
<div class="card">
    <h1>Email Verification</h1>
    <div class="alert <?= e($type) ?>"><?= e($message) ?></div>

    <?php if (!empty($expired)): ?>
        <?php if (current_user()): ?>
            <form method="post" action="resend_verification.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button type="submit">Resend Verification Email</button>
            </form>
        <?php else: ?>
            <p>Log in to resend the verification email.</p>
            <a class="button" href="login.php">Go to Login</a>
        <?php endif; ?>
    <?php elseif ($type === 'success'): ?>
        <?php if (current_user()): ?>
            <a class="button" href="profile.php">Go to Profile</a>
        <?php else: ?>
            <a class="button" href="login.php">Go to Login</a>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/footer.php'; ?>
