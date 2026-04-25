<?php
// ============================================================
// includes/auth_check.php
// Central Role-Based Access Control (RBAC) guard.
//
// USAGE — at the top of every protected page:
//
//   require_once __DIR__ . '/../includes/auth_check.php';
//   requireRole(['admin']);                    // admin only
//   requireRole(['admin','receptionist']);     // either role
//   requireRole(['doctor']);                   // doctor only
//
// OR use the named helpers:
//   requireAdmin();
//   requireReceptionist();
//   requireDoctor();
//   requireAdminOrReceptionist();
// ============================================================

if (session_status() === PHP_SESSION_NONE) session_start();

// ── Constants ───────────────────────────────────────────────
define('ROLE_ADMIN',         'admin');
define('ROLE_RECEPTIONIST',  'receptionist');
define('ROLE_DOCTOR',        'doctor');

// ── Core guard ──────────────────────────────────────────────
/**
 * Ensure user is logged in AND has one of the allowed roles.
 * On failure: redirect to login (not logged in) or access-denied page.
 *
 * @param string[] $allowedRoles  e.g. ['admin','receptionist']
 */
function requireRole(array $allowedRoles): void
{
    // 1. Must be logged in
    if (empty($_SESSION['user_id'])) {
        // Save the attempted URL so we can redirect back after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /hospital-system/auth/login.php');
        exit;
    }

    // 2. Role must be in the allowed list
    $role = $_SESSION['user_role'] ?? '';
    if (!in_array($role, $allowedRoles, true)) {
        // Log the violation attempt (server-side only)
        error_log(sprintf(
            '[RBAC] Access denied: user_id=%d role=%s tried %s',
            $_SESSION['user_id'],
            $role,
            $_SERVER['REQUEST_URI']
        ));

        // Show access-denied page — do NOT redirect to login (user IS logged in)
        http_response_code(403);
        include __DIR__ . '/access_denied.php';
        exit;
    }
}

// ── Named helpers ────────────────────────────────────────────
function requireAdmin(): void
{
    requireRole([ROLE_ADMIN]);
}

function requireReceptionist(): void
{
    requireRole([ROLE_RECEPTIONIST]);
}

function requireDoctor(): void
{
    requireRole([ROLE_DOCTOR]);
}

function requireAdminOrReceptionist(): void
{
    requireRole([ROLE_ADMIN, ROLE_RECEPTIONIST]);
}

function requireAdminOrDoctor(): void
{
    requireRole([ROLE_ADMIN, ROLE_DOCTOR]);
}

function requireAnyRole(): void
{
    requireRole([ROLE_ADMIN, ROLE_RECEPTIONIST, ROLE_DOCTOR]);
}

// ── Convenience checkers (return bool, don't redirect) ───────
function isAdmin(): bool        { return ($_SESSION['user_role'] ?? '') === ROLE_ADMIN; }
function isReceptionist(): bool { return ($_SESSION['user_role'] ?? '') === ROLE_RECEPTIONIST; }
function isDoctor(): bool       { return ($_SESSION['user_role'] ?? '') === ROLE_DOCTOR; }

function hasRole(string $role): bool
{
    return ($_SESSION['user_role'] ?? '') === $role;
}

function hasAnyRole(array $roles): bool
{
    return in_array($_SESSION['user_role'] ?? '', $roles, true);
}

/**
 * Return the dashboard URL for the current user's role.
 */
function dashboardUrl(): string
{
    return match ($_SESSION['user_role'] ?? '') {
        ROLE_ADMIN        => '/hospital-system/index.php',
        ROLE_RECEPTIONIST => '/hospital-system/receptionist/dashboard.php',
        ROLE_DOCTOR       => '/hospital-system/doctor/dashboard.php',
        default           => '/hospital-system/auth/login.php',
    };
}

/**
 * If user is already logged in, redirect to their dashboard.
 * Call this at the top of login.php.
 */
function redirectIfLoggedIn(): void
{
    if (!empty($_SESSION['user_id'])) {
        header('Location: ' . dashboardUrl());
        exit;
    }
}

/**
 * Look up the doctor row linked to the currently logged-in doctor user.
 * Returns the doctor row array, or redirects to access-denied if not found.
 */
function getCurrentDoctorRow(): array
{
    requireRole([ROLE_DOCTOR]);
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM doctors WHERE user_id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $doctor = $stmt->fetch();
    if (!$doctor) {
        http_response_code(403);
        include __DIR__ . '/access_denied.php';
        exit;
    }
    return $doctor;
}
