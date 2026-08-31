<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

if (current_user()) {
    redirect('profile.php');
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($name === '') $errors[] = 'Name is required.';
    if ($email === '') $errors[] = 'Email is required.';
    elseif (!valid_email($email)) $errors[] = 'Please enter a valid email.';
    if ($password === '') $errors[] = 'Password is required.';
    elseif (!valid_password($password)) $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';

    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);
        $userId = (int) db()->lastInsertId();

        $userStmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch();

        login_user($userId);

        try {
            $token = save_verification_token($userId);
            send_verification_email($user, $token);
            flash('success', 'Registration successful. You are logged in. Please verify your email within 1 hour.');
        } catch (Throwable $e) {
            flash('error', 'Account created and you are logged in, but the verification email could not be sent. Please resend it.');
        }

        redirect('verify_required.php');
    }
}

$pageTitle = 'Register';
require __DIR__ . '/header.php';
?>
<div class="card">
    <h1>Create Account</h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post" data-validate novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <label>Full Name</label>
        <input type="text" name="name" value="<?= e($name) ?>" required maxlength="100">

        <label>Email</label>
        <input type="email" name="email" value="<?= e($email) ?>" required>

        <label>Password</label>
        <input type="password" name="password" data-min-password="<?= PASSWORD_MIN_LENGTH ?>" required>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</div>
<?php require __DIR__ . '/footer.php'; ?>
