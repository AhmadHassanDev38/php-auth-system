<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

if (current_user()) {
    if (!empty(current_user()['email_verified_at'])) redirect('profile.php');
    redirect('verify_required.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || !valid_email($email)) $errors[] = 'Please enter a valid email.';
    if ($password === '') $errors[] = 'Password is required.';

    if (!$errors) {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid email or password.';
        } else {
            login_user((int)$user['id']);

            if (empty($user['email_verified_at'])) {
                flash('error', 'Your email is not verified. Please verify it before continuing.');
                redirect('verify_required.php');
            }

            redirect('profile.php');
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/header.php';
?>
<div class="card">
    <h1>Login</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" data-validate novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>Email</label>
        <input type="email" name="email" value="<?= e($email) ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <div class="links">
        <a href="forgot_password.php">Forgot Password?</a>
        <a href="register.php">Create Account</a>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
