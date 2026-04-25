<?php
// ============================================================
// doctor/dashboard.php — Doctor Dashboard
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireDoctor();   // RBAC: doctors only
require_once __DIR__ . '/../includes/header.php';

$db       = getDB();
$doctorId = $_SESSION['doctor_id'] ?? null;

if (!$doctorId) {
    setFlash('danger', 'Your user account is not linked to a doctor profile. Contact admin.');
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// My doctor profile
$stmt = $db->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$doctorId]);
$doctor = $stmt->fetch();

// Today's appointments for this doctor
$todayAppts = $db->prepare(
    "SELECT a.*, p.name AS patient_name, p.age, p.gender
     FROM appointments a
     JOIN patients p ON a.patient_id = p.id
     WHERE a.doctor_id = ? AND a.appointment_date = CURDATE()
     ORDER BY a.appointment_time ASC"
);
$todayAppts->execute([$doctorId]);
$todayAppts = $todayAppts->fetchAll();

// Upcoming appointments (next 7 days, excluding today)
$upcomingAppts = $db->prepare(
    "SELECT a.*, p.name AS patient_name
     FROM appointments a
     JOIN patients p ON a.patient_id = p.id
     WHERE a.doctor_id = ?
       AND a.appointment_date > CURDATE()
       AND a.appointment_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
       AND a.status = 'Scheduled'
     ORDER BY a.appointment_date ASC, a.appointment_time ASC
     LIMIT 8"
);
$upcomingAppts->execute([$doctorId]);
$upcomingAppts = $upcomingAppts->fetchAll();

// My recent medical records
$recentRecords = $db->prepare(
    "SELECT r.*, p.name AS patient_name
     FROM medical_records r
     JOIN patients p ON r.patient_id = p.id
     WHERE r.doctor_id = ?
     ORDER BY r.record_date DESC, r.created_at DESC
     LIMIT 5"
);
$recentRecords->execute([$doctorId]);
$recentRecords = $recentRecords->fetchAll();

// Quick stats
$totalMyAppts   = $db->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ?");
$totalMyAppts->execute([$doctorId]);
$totalMyAppts = $totalMyAppts->fetchColumn();

$totalMyPatients = $db->prepare(
    "SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE doctor_id = ?"
);
$totalMyPatients->execute([$doctorId]);
$totalMyPatients = $totalMyPatients->fetchColumn();

$totalMyRecords = $db->prepare("SELECT COUNT(*) FROM medical_records WHERE doctor_id = ?");
$totalMyRecords->execute([$doctorId]);
$totalMyRecords = $totalMyRecords->fetchColumn();

$todayCount = count($todayAppts);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">My Dashboard</h1>
        <p class="page-sub">Welcome, <?= sanitize($doctor['name']) ?> &mdash; <?= sanitize($doctor['specialty']) ?></p>
    </div>
    <a href="/hospital-system/records/create.php" class="btn btn-primary">+ Add Medical Record</a>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card stat-amber">
        <div class="stat-icon">◷</div>
        <div class="stat-body"><div class="stat-num"><?= $todayCount ?></div><div class="stat-label">Today's Appointments</div></div>
        <a href="/hospital-system/doctor/appointments.php" class="stat-link">View →</a>
    </div>
    <div class="stat-card stat-blue">
        <div class="stat-icon">♡</div>
        <div class="stat-body"><div class="stat-num"><?= $totalMyPatients ?></div><div class="stat-label">My Patients</div></div>
        <a href="/hospital-system/doctor/patients.php" class="stat-link">View →</a>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">☰</div>
        <div class="stat-body"><div class="stat-num"><?= $totalMyRecords ?></div><div class="stat-label">Records Written</div></div>
        <a href="/hospital-system/records/index.php" class="stat-link">View →</a>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-icon">◎</div>
        <div class="stat-body"><div class="stat-num"><?= $totalMyAppts ?></div><div class="stat-label">Total Appointments</div></div>
    </div>
</div>

<div class="dash-grid">
    <!-- Today's schedule -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Today's Schedule
                <span class="badge badge-blue"><?= $todayCount ?></span>
            </h2>
            <a href="/hospital-system/doctor/appointments.php" class="btn btn-sm btn-ghost">Full list →</a>
        </div>
        <?php if (empty($todayAppts)): ?>
            <div class="empty-state">No appointments scheduled for today.</div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Time</th><th>Patient</th><th>Age</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($todayAppts as $a): ?>
            <tr>
                <td class="fw-500"><?= formatTime($a['appointment_time']) ?></td>
                <td><?= sanitize($a['patient_name']) ?></td>
                <td><?= $a['age'] ?></td>
                <td><?= getStatusBadge($a['status']) ?></td>
                <td>
                    <a href="/hospital-system/records/create.php?patient_id=<?= $a['patient_id'] ?>"
                       class="btn btn-xs btn-primary">+ Record</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Upcoming (Next 7 Days)</h2>
        </div>
        <?php if (empty($upcomingAppts)): ?>
            <div class="empty-state">No upcoming appointments in the next 7 days.</div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($upcomingAppts as $a): ?>
            <tr>
                <td><?= formatDate($a['appointment_date']) ?></td>
                <td><?= formatTime($a['appointment_time']) ?></td>
                <td class="fw-500"><?= sanitize($a['patient_name']) ?></td>
                <td><?= getStatusBadge($a['status']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent records I wrote -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">My Recent Medical Records</h2>
        <a href="/hospital-system/records/create.php" class="btn btn-sm btn-primary">+ New Record</a>
    </div>
    <?php if (empty($recentRecords)): ?>
        <div class="empty-state">No medical records written yet.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="table">
        <thead><tr><th>Date</th><th>Patient</th><th>Diagnosis</th><th>Treatment</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($recentRecords as $r): ?>
        <tr>
            <td><?= formatDate($r['record_date']) ?></td>
            <td class="fw-500"><?= sanitize($r['patient_name']) ?></td>
            <td><?= sanitize($r['diagnosis']) ?></td>
            <td class="text-muted text-truncate" style="max-width:200px"><?= sanitize($r['treatment']) ?></td>
            <td>
                <a href="/hospital-system/records/edit.php?id=<?= $r['id'] ?>" class="btn btn-xs btn-ghost">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
