<?php
// ============================================================
// doctors/delete.php
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';
$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT name FROM doctors WHERE id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();
if (!$doctor) { setFlash('danger', 'Doctor not found.'); redirect('/hospital-system/doctors/index.php'); }
$db->prepare("DELETE FROM doctors WHERE id = ?")->execute([$id]);
setFlash('success', 'Doctor "' . $doctor['name'] . '" removed.');
redirect('/hospital-system/doctors/index.php');
