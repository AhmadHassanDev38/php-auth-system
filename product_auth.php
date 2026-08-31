<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

// Products are part of the authenticated/verified area.
require_verified();

function product_csrf_token(): string
{
    return csrf_token();
}

function verify_product_csrf(): void
{
    verify_csrf();
}

function product_e(?string $value): string
{
    return e($value);
}
