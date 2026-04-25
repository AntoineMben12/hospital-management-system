<?php
// ============================================================
// appointments/delete.php
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdminOrReceptionist();
require_once __DIR__ . '/../includes/header.php';
$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT id FROM appointments WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    setFlash('danger', 'Appointment not found.');
    redirect('/hospital-system/appointments/index.php');
}
$db->prepare("DELETE FROM appointments WHERE id = ?")->execute([$id]);
setFlash('success', 'Appointment deleted.');
redirect('/hospital-system/appointments/index.php');
