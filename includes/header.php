<?php
// ============================================================
// includes/header.php — Role-aware sidebar navigation
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Ensure user is logged in (role check is done per-page before including header)
requireLogin();

$flash      = getFlash();
$current    = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$userRole   = $_SESSION['user_role'] ?? '';

function navLink(string $href, string $icon, string $label, string $dir): string {
    global $currentDir, $current;
    $active = ($currentDir === $dir || ($dir === 'hospital-system' && $current === 'index.php')) ? 'active' : '';
    return '<a href="' . $href . '" class="nav-link ' . $active . '">'
         . '<span class="nav-icon">' . $icon . '</span>'
         . '<span class="nav-label">' . $label . '</span>'
         . '</a>';
}

// Role-to-home mapping
$homeUrl = dashboardUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCore HMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/hospital-system/style.css">
</head>
<body>
<div class="app-layout">

    <!-- ── Sidebar ──────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">✦</div>
            <div class="brand-text">
                <span class="brand-name">MediCore</span>
                <span class="brand-sub">
                    <?= match($userRole) {
                        'admin'        => 'Administrator',
                        'receptionist' => 'Reception',
                        'doctor'       => 'Doctor Portal',
                        default        => 'HMS v1.0'
                    } ?>
                </span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <?php /* ════════════ ADMIN NAV ════════════ */ if (isAdmin()): ?>

                <div class="nav-section-label">Overview</div>
                <?= navLink('/hospital-system/index.php', '◈', 'Admin Dashboard', 'hospital-system') ?>

                <div class="nav-section-label">Management</div>
                <?= navLink('/hospital-system/patients/index.php',     '♡', 'Patients',        'patients') ?>
                <?= navLink('/hospital-system/doctors/index.php',      '✚', 'Doctors',         'doctors') ?>
                <?= navLink('/hospital-system/appointments/index.php', '◷', 'Appointments',    'appointments') ?>
                <?= navLink('/hospital-system/records/index.php',      '☰', 'Medical Records', 'records') ?>

                <div class="nav-section-label">System</div>
                <?= navLink('/hospital-system/reports/index.php',      '◎', 'Reports',         'reports') ?>
                <?= navLink('/hospital-system/users/index.php',        '⊕', 'User Management', 'users') ?>

            <?php /* ════════════ RECEPTIONIST NAV ════════════ */ elseif (isReceptionist()): ?>

                <div class="nav-section-label">Overview</div>
                <?= navLink('/hospital-system/receptionist/dashboard.php', '◈', 'Dashboard', 'receptionist') ?>

                <div class="nav-section-label">Management</div>
                <?= navLink('/hospital-system/patients/index.php',     '♡', 'Patients',     'patients') ?>
                <?= navLink('/hospital-system/appointments/index.php', '◷', 'Appointments', 'appointments') ?>

            <?php /* ════════════ DOCTOR NAV ════════════ */ elseif (isDoctor()): ?>

                <div class="nav-section-label">Overview</div>
                <?= navLink('/hospital-system/doctor/dashboard.php', '◈', 'My Dashboard', 'doctor') ?>

                <div class="nav-section-label">My Work</div>
                <?= navLink('/hospital-system/doctor/appointments.php', '◷', 'My Appointments', 'doctor') ?>
                <?= navLink('/hospital-system/doctor/patients.php',     '♡', 'My Patients',     'doctor') ?>
                <?= navLink('/hospital-system/records/index.php',       '☰', 'Medical Records',  'records') ?>

            <?php endif; ?>

        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?></div>
                <div class="user-details">
                    <span class="user-name"><?= sanitize($_SESSION['user_name'] ?? '') ?></span>
                    <span class="user-role"><?= sanitize(ucfirst($userRole)) ?></span>
                </div>
            </div>
            <a href="/hospital-system/auth/logout.php" class="logout-btn" title="Logout">⎋</a>
        </div>
    </aside>

    <!-- ── Main wrapper ─────────────────────────────────── -->
    <div class="main-wrapper">
        <header class="top-bar">
            <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">☰</button>
            <div class="top-bar-right">
                <span class="role-pill role-<?= $userRole ?>"><?= ucfirst($userRole) ?></span>
                <span class="top-date"><?= date('l, F j Y') ?></span>
            </div>
        </header>

        <main class="main-content">
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>" id="flashAlert">
                <?= sanitize($flash['message']) ?>
                <button onclick="this.parentElement.remove()" class="alert-close">×</button>
            </div>
            <?php endif; ?>
