<?php
// ============================================================
// auth/logout.php — Destroys session and redirects to login
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
session_unset();
session_destroy();
header('Location: /hospital-system/auth/login.php');
exit;
