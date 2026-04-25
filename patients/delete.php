<?php
// ============================================================
// patients/delete.php — Delete patient
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdminOrReceptionist();
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT name FROM patients WHERE id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    setFlash('danger', 'Patient not found.');
    redirect('/hospital-system/patients/index.php');
}

$db->prepare("DELETE FROM patients WHERE id = ?")->execute([$id]);
setFlash('success', 'Patient "' . $patient['name'] . '" deleted.');
redirect('/hospital-system/patients/index.php');
