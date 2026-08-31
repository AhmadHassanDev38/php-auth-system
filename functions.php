<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function current_user(): ?array
{
    static $loaded = false;
    static $user = null;

    if ($loaded) {
        return $user;
    }

    $loaded = true;

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;

    if (!$user) {
        unset($_SESSION['user_id']);
    }

    return $user;
}

function login_user(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
    }

    session_destroy();
}

function require_login(): array
{
    $user = current_user();

    if (!$user) {
        flash('error', 'Please log in first.');
        redirect('login.php');
    }

    return $user;
}

function require_verified(): array
{
    $user = require_login();

    if (empty($user['email_verified_at'])) {
        flash('error', 'Please verify your email before using your account.');
        redirect('verify_required.php');
    }

    return $user;
}

function valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function valid_password(string $password): bool
{
    return mb_strlen($password) >= PASSWORD_MIN_LENGTH;
}

function create_token(): string
{
    return bin2hex(random_bytes(32));
}

function save_verification_token(int $userId): string
{
    $pdo = db();
    $pdo->prepare('DELETE FROM email_verification_tokens WHERE user_id = ?')->execute([$userId]);

    $raw = create_token();
    $hash = hash('sha256', $raw);
    $expires = date('Y-m-d H:i:s', time() + TOKEN_LIFETIME_SECONDS);

    $stmt = $pdo->prepare('INSERT INTO email_verification_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $hash, $expires]);

    return $raw;
}

function save_password_reset_token(int $userId): string
{
    $pdo = db();
    $pdo->prepare('DELETE FROM password_reset_tokens WHERE user_id = ?')->execute([$userId]);

    $raw = create_token();
    $hash = hash('sha256', $raw);
    $expires = date('Y-m-d H:i:s', time() + TOKEN_LIFETIME_SECONDS);

    $stmt = $pdo->prepare('INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $hash, $expires]);

    return $raw;
}

function send_verification_email(array $user, string $rawToken): void
{
    require_once __DIR__ . '/vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = MAIL_PORT;

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($user['email'], $user['name']);
    $mail->isHTML(true);
    $mail->Subject = 'Verify your email address';

    $link = BASE_URL . '/verify.php?token=' . urlencode($rawToken);

    $mail->Body = '<h2>Verify your email</h2>'
        . '<p>Hello ' . e($user['name']) . ',</p>'
        . '<p>Please click the link below to verify your account. This link expires in 1 hour.</p>'
        . '<p><a href="' . e($link) . '">Verify Email</a></p>'
        . '<p>If you did not create this account, ignore this email.</p>';

    $mail->AltBody = "Verify your email: {$link}\nThis link expires in 1 hour.";

    $mail->send();
}

function send_password_reset_email(array $user, string $rawToken): void
{
    require_once __DIR__ . '/vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = MAIL_PORT;

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($user['email'], $user['name']);
    $mail->isHTML(true);
    $mail->Subject = 'Reset your password';

    $link = BASE_URL . '/reset_password.php?token=' . urlencode($rawToken);

    $mail->Body = '<h2>Password reset</h2>'
        . '<p>Hello ' . e($user['name']) . ',</p>'
        . '<p>Click the link below to choose a new password. This link expires in 1 hour.</p>'
        . '<p><a href="' . e($link) . '">Reset Password</a></p>'
        . '<p>If you did not request a reset, ignore this email.</p>';

    $mail->AltBody = "Reset your password: {$link}\nThis link expires in 1 hour.";

    $mail->send();
}
