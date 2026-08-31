<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$user = require_login();

$pageTitle = 'Verify Email';
require __DIR__ . '/header.php';
?>
<div class="card">
    <h1>Email Verification Required</h1>
    <p>Your account is logged in, but your email <strong><?= e($user['email']) ?></strong> has not been verified.</p>
    <p>Check your inbox and click the verification link. The link expires after 1 hour.</p>

    <form method="post" action="resend_verification.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <button type="submit">Resend Verification Email</button>
    </form>

    <p><a href="logout.php">Logout</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>
