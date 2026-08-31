<?php
require_once __DIR__ . '/functions.php';

if (current_user()) {
    if (!empty(current_user()['email_verified_at'])) {
        redirect('profile.php');
    }
    redirect('verify_required.php');
}

redirect('login.php');
