# PHP + MySQL User Authentication System

## Features

- Registration with client-side and server-side validation
- Duplicate email protection
- Secure password hashing with `password_hash()` / `password_verify()`
- Automatic login after registration
- Email verification with one-time tokens and 1-hour expiry
- Unverified-account protection
- Resend verification email
- Login/logout/session handling
- Forgot password
- One-time password reset tokens with 1-hour expiry
- Profile display
- Profile name and photo update
- JPG/JPEG/PNG profile photo validation
- 2 MB upload limit
- Change password with old-password verification
- CSRF protection
- PDO prepared statements
- Generic forgot-password response to reduce account enumeration

> The assignment mentions MD5/SHA1. Do **not** use either for passwords in a real application. This project uses PHP's modern `password_hash()` API, which is the correct secure approach.

## Requirements

- PHP 8.1+
- MySQL 8+ or MariaDB
- Apache/XAMPP/WAMP/LAMP
- Composer
- An SMTP account, such as Gmail with a Google App Password

## 1. Put the project in XAMPP

Copy the `php-auth-system` folder into:

`C:/xampp/htdocs/`

For WAMP, put it in your `www` folder.

## 2. Create the database

Open phpMyAdmin and import `database.sql`.

Or run:

```sql
SOURCE database.sql;
```

The SQL creates the `php_auth_system` database and all required tables.

## 3. Install PHPMailer

Open Command Prompt/Terminal inside the project:

```bash
composer install
```

This creates the `vendor/` directory.

## 4. Configure config.php

Edit these values:

```php
const BASE_URL = 'http://localhost/php-auth-system';

const DB_HOST = '127.0.0.1';
const DB_NAME = 'php_auth_system';
const DB_USER = 'root';
const DB_PASS = '';

const MAIL_HOST = 'smtp.gmail.com';
const MAIL_PORT = 587;
const MAIL_USERNAME = 'your-email@gmail.com';
const MAIL_PASSWORD = 'your-16-character-app-password';
const MAIL_FROM = 'your-email@gmail.com';
const MAIL_FROM_NAME = APP_NAME;
```

For XAMPP MySQL, the default root password is often blank, but use your actual password if you configured one.

## 5. Gmail SMTP setup

Do not put your normal Gmail password in the project.

Use a Google App Password:

1. Turn on 2-Step Verification for the Google account.
2. Create an App Password.
3. Put the generated 16-character App Password into `MAIL_PASSWORD`.
4. Use the same Gmail address for `MAIL_USERNAME` and `MAIL_FROM`.

For production, store SMTP credentials in environment variables instead of source code.

## 6. Start Apache and MySQL

In XAMPP Control Panel:

- Start Apache
- Start MySQL

Then open:

`http://localhost/php-auth-system/`

## 7. Test the complete flow

### Registration

1. Open Register.
2. Enter name, email, and password.
3. Submit.
4. Confirm that the user is automatically logged in.
5. Confirm that the verification email arrives.
6. Try opening Profile before verification.
7. Confirm access is blocked.

### Email verification

1. Open the verification email.
2. Click the unique link.
3. Confirm the account becomes verified.
4. If logged in, confirm redirect to Profile.
5. Log out.
6. Register another test account.
7. Log out before verification.
8. Verify from email.
9. Confirm verification does NOT log the user in automatically.

### Expired verification link

The token lifetime is 3600 seconds (1 hour). To test expiry quickly during development, temporarily change:

```php
const TOKEN_LIFETIME_SECONDS = 3600;
```

to:

```php
const TOKEN_LIFETIME_SECONDS = 10;
```

Register/resend, wait 10+ seconds, then click the link. Restore it to 3600 afterward.

### Login

Test:
- Correct email/password -> Profile
- Wrong password -> error
- Unverified account -> verification screen + resend option

### Forgot password

1. Log out.
2. Open Forgot Password.
3. Enter an existing email.
4. Open the reset email.
5. Use the reset link.
6. Enter new password and confirmation.
7. Confirm redirect to Login with success message.
8. Confirm the old password no longer works.

### Expired reset link

Temporarily set token lifetime to 10 seconds, request a reset, wait, then click the link. Confirm an expiry message appears.

### Profile

After verification:
- Check name
- Check email
- Check profile photo
- Check verification status
- Edit name
- Upload JPG/JPEG/PNG
- Test a file larger than 2 MB
- Test a non-image file

### Change password

Test:
- Wrong old password
- New password shorter than 8 characters
- Confirmation mismatch
- Successful password change

### Logout

Click Logout and confirm:
- Session is destroyed
- User goes to Login
- Profile cannot be opened afterward

## Security notes

1. Passwords use `password_hash()` rather than MD5/SHA1.
2. Database queries use PDO prepared statements.
3. CSRF tokens protect POST forms.
4. Authentication uses session ID regeneration after login.
5. Verification/reset tokens are cryptographically random.
6. Only SHA-256 hashes of email/password-reset tokens are stored in the database.
7. Verification and reset links expire after 1 hour.
8. Reset/verification tokens are deleted after successful use.
9. Uploaded filenames are generated randomly; the original filename is never trusted.
10. Uploaded images are checked by MIME type and image parsing.
11. Profile images are limited to 2 MB and JPG/JPEG/PNG.
12. Forgot-password responses do not reveal whether an email exists.

## Screenshots for submission

Take screenshots of these screens/flows:

1. Register screen
2. Registration validation error
3. Verification-required screen
4. Verification email
5. Email verification success
6. Login screen
7. Login incorrect-password error
8. Forgot Password screen
9. Reset Password screen
10. Reset-password success on Login
11. Profile screen
12. Edit Profile screen
13. Profile with uploaded photo
14. Change Password screen
15. Logout/Login redirect
16. Expired verification link
17. Expired password reset link

## Suggested submission structure

```text
php-auth-system/
├── assets/
│   └── style.css
├── uploads/
│   └── .gitkeep
├── vendor/                 # generated by composer install
├── change_password.php
├── composer.json
├── config.php
├── database.sql
├── db.php
├── edit_profile.php
├── forgot_password.php
├── footer.php
├── functions.php
├── header.php
├── index.php
├── login.php
├── logout.php
├── profile.php
├── register.php
├── resend_verification.php
├── reset_password.php
├── verify.php
├── verify_required.php
└── README.md
```
