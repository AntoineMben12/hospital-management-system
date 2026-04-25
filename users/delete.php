<?php
// ============================================================
// users/delete.php — Delete user  (Admin only)
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

// Cannot delete yourself
if ($id === (int)$_SESSION['user_id']) {
    setFlash('danger', 'You cannot delete your own account.');
    redirect('/hospital-system/users/index.php');
}

$stmt = $db->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect('/hospital-system/users/index.php');
}

// Unlink any doctor profile before deleting
$db->prepare("UPDATE doctors SET user_id = NULL WHERE user_id = ?")->execute([$id]);
$db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

setFlash('success', 'User "' . $user['full_name'] . '" deleted.');
redirect('/hospital-system/users/index.php');
