<?php
declare(strict_types=1);

const APP_NAME = 'PHP Authentication System';
const BASE_URL = 'http://localhost/php-auth-system';

const DB_HOST = '127.0.0.1';
const DB_NAME = 'php_auth_system';
const DB_USER = 'root';
const DB_PASS = '';

const MAIL_HOST = 'smtp.gmail.com';
const MAIL_PORT = 587;
const MAIL_USERNAME = 'hafizahmadhassan919@gmail.com';
const MAIL_PASSWORD = 'ovkcgzrivxqzqnhh';
const MAIL_FROM = 'hafizahmadhassan919@gmail.com';
const MAIL_FROM_NAME = APP_NAME;

const PASSWORD_MIN_LENGTH = 8;
const TOKEN_LIFETIME_SECONDS = 3600;
const MAX_PROFILE_PHOTO_BYTES = 2 * 1024 * 1024;

date_default_timezone_set('Asia/Karachi');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
