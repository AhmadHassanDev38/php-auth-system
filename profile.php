<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$user = require_verified();

$pageTitle = 'Profile';
require __DIR__ . '/header.php';

$photoUrl = $user['profile_photo']
    ? BASE_URL . '/uploads/' . rawurlencode($user['profile_photo'])
    : null;
?>
<div class="card">
    <h1>Profile</h1>

    <div class="profile">
        <?php if ($photoUrl): ?>
            <img class="avatar" src="<?= e($photoUrl) ?>" alt="Profile photo">
        <?php else: ?>
            <div class="avatar placeholder">No Photo</div>
        <?php endif; ?>

        <p><strong>Name:</strong> <?= e($user['name']) ?></p>
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
        <p><strong>Email Verification:</strong> <span class="badge success">Verified</span></p>
    </div>

    <div class="actions">
        <a class="button" href="edit_profile.php">Edit Profile</a>
        <a class="button" href="products.php">Products</a>
        <a class="button secondary" href="change_password.php">Change Password</a>
        <a class="button danger" href="logout.php">Logout</a>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
