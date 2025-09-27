<?php

declare(strict_types=1);

namespace App\Lib;

const ADMIN_USER = 'admin';
const ADMIN_PASSWORD = 'dulces2025';
const SESSION_KEY = 'dulces_admin_session';

function ensureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool
{
    ensureSession();
    return isset($_SESSION[SESSION_KEY]) && $_SESSION[SESSION_KEY] === true;
}

function requireAuth(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function attemptLogin(string $user, string $password): bool
{
    if ($user === ADMIN_USER && $password === ADMIN_PASSWORD) {
        ensureSession();
        $_SESSION[SESSION_KEY] = true;
        return true;
    }

    return false;
}

function logout(): void
{
    ensureSession();
    unset($_SESSION[SESSION_KEY]);
}
