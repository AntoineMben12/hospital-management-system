<?php
// ============================================================
// records/delete.php
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdminOrDoctor();
require_once __DIR__ . '/../includes/header.php';
$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT id FROM medical_records WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    setFlash('danger', 'Record not found.');
    redirect('/hospital-system/records/index.php');
}
$db->prepare("DELETE FROM medical_records WHERE id = ?")->execute([$id]);
setFlash('success', 'Medical record deleted.');
redirect('/hospital-system/records/index.php');
