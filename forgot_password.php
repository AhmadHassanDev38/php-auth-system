<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

if (current_user()) redirect('profile.php');

$errors = [];
$sent = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim($_POST['email'] ?? ''));

    if ($email === '' || !valid_email($email)) {
        $errors[] = 'Please enter a valid email.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            try {
                $token = save_password_reset_token((int)$user['id']);
                send_password_reset_email($user, $token);
            } catch (Throwable $e) {
                // Keep the same response to avoid exposing account existence.
            }
        }

        $sent = true;
    }
}

$pageTitle = 'Forgot Password';
require __DIR__ . '/header.php';
?>
<div class="card">
    <h1>Forgot Password</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <?php if ($sent): ?>
        <div class="alert success">If an account exists for that email, a password reset link has been sent. The link expires in 1 hour.</div>
    <?php endif; ?>

    <form method="post" data-validate novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>Email</label>
        <input type="email" name="email" value="<?= e($email) ?>" required>

        <button type="submit">Send Reset Link</button>
    </form>

    <p><a href="login.php">Back to Login</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>
