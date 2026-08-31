<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('verify_required.php');
}

verify_csrf();

if (!empty($user['email_verified_at'])) {
    flash('success', 'Your email is already verified.');
    redirect('profile.php');
}

try {
    $token = save_verification_token((int)$user['id']);
    send_verification_email($user, $token);
    flash('success', 'A new verification email has been sent. The new link expires in 1 hour.');
} catch (Throwable $e) {
    flash('error', 'Unable to send the verification email right now. Check SMTP configuration.');
}

redirect('verify_required.php');
