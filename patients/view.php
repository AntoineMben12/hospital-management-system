<?php
// ============================================================
// patients/view.php — View patient profile + records
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireRole(['admin','receptionist','doctor']);
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    setFlash('danger', 'Patient not found.');
    redirect('/hospital-system/patients/index.php');
}

// Patient's appointments
$appts = $db->prepare(
    "SELECT a.*, d.name AS doctor_name, d.specialty
     FROM appointments a
     JOIN doctors d ON a.doctor_id = d.id
     WHERE a.patient_id = ?
     ORDER BY a.appointment_date DESC, a.appointment_time DESC"
);
$appts->execute([$id]);
$appointments = $appts->fetchAll();

// Patient's medical records
$recs = $db->prepare(
    "SELECT r.*, d.name AS doctor_name
     FROM medical_records r
     LEFT JOIN doctors d ON r.doctor_id = d.id
     WHERE r.patient_id = ?
     ORDER BY r.record_date DESC"
);
$recs->execute([$id]);
$records = $recs->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= sanitize($patient['name']) ?></h1>
        <p class="page-sub"><a href="/hospital-system/patients/index.php" class="breadcrumb-link">Patients</a> / Profile</p>
    </div>
    <div class="header-actions">
        <a href="/hospital-system/patients/edit.php?id=<?= $id ?>" class="btn btn-secondary">Edit</a>
        <a href="/hospital-system/records/create.php?patient_id=<?= $id ?>" class="btn btn-primary">+ Add Record</a>
    </div>
</div>

<!-- Patient Info Card -->
<div class="card profile-card">
    <div class="profile-avatar"><?= strtoupper(substr($patient['name'], 0, 1)) ?></div>
    <div class="profile-details">
        <div class="profile-grid">
            <div class="profile-field">
                <span class="field-label">Full Name</span>
                <span class="field-value"><?= sanitize($patient['name']) ?></span>
            </div>
            <div class="profile-field">
                <span class="field-label">Age</span>
                <span class="field-value"><?= $patient['age'] ?> years</span>
            </div>
            <div class="profile-field">
                <span class="field-label">Gender</span>
                <span class="field-value"><?= sanitize($patient['gender']) ?></span>
            </div>
            <div class="profile-field">
                <span class="field-label">Phone</span>
                <span class="field-value"><?= sanitize($patient['phone']) ?></span>
            </div>
            <div class="profile-field profile-field-wide">
                <span class="field-label">Address</span>
                <span class="field-value"><?= sanitize($patient['address'] ?: '—') ?></span>
            </div>
            <div class="profile-field">
                <span class="field-label">Registered</span>
                <span class="field-value"><?= formatDate($patient['created_at']) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Appointments -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Appointments <span class="badge badge-blue"><?= count($appointments) ?></span></h2>
        <a href="/hospital-system/appointments/create.php?patient_id=<?= $id ?>" class="btn btn-sm btn-primary">+ Book</a>
    </div>
    <?php if (empty($appointments)): ?>
        <div class="empty-state">No appointments yet.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="table">
        <thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Specialty</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
        <tr>
            <td><?= formatDate($a['appointment_date']) ?></td>
            <td><?= formatTime($a['appointment_time']) ?></td>
            <td><?= sanitize($a['doctor_name']) ?></td>
            <td class="text-muted"><?= sanitize($a['specialty']) ?></td>
            <td><?= getStatusBadge($a['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- Medical Records -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Medical Records <span class="badge badge-blue"><?= count($records) ?></span></h2>
    </div>
    <?php if (empty($records)): ?>
        <div class="empty-state">No medical records on file.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="table">
        <thead><tr><th>Date</th><th>Diagnosis</th><th>Treatment</th><th>Doctor</th></tr></thead>
        <tbody>
        <?php foreach ($records as $r): ?>
        <tr>
            <td><?= formatDate($r['record_date']) ?></td>
            <td class="fw-500"><?= sanitize($r['diagnosis']) ?></td>
            <td><?= sanitize($r['treatment']) ?></td>
            <td class="text-muted"><?= sanitize($r['doctor_name'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
