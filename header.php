<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
$flash = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="<?= e(BASE_URL) ?>/index.php"><?= e(APP_NAME) ?></a>
    <div>
        <?php if (current_user()): ?>
            <a href="<?= e(BASE_URL) ?>/profile.php">Profile</a>
            <a href="<?= e(BASE_URL) ?>/logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= e(BASE_URL) ?>/login.php">Login</a>
            <a href="<?= e(BASE_URL) ?>/register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>
<main class="container">
<?php if ($flash): ?>
    <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
