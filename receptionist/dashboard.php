<?php
// ============================================================
// receptionist/dashboard.php — Receptionist Dashboard
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireReceptionist();   // RBAC: receptionist only
require_once __DIR__ . '/../includes/header.php';

$db = getDB();

$totalPatients     = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalAppointments = $db->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

$scheduledToday = $db->query(
    "SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status='Scheduled'"
)->fetchColumn();

$upcomingAppts = $db->query(
    "SELECT a.*, p.name AS patient_name, d.name AS doctor_name
     FROM appointments a
     JOIN patients p ON a.patient_id = p.id
     JOIN doctors  d ON a.doctor_id  = d.id
     WHERE a.appointment_date >= CURDATE() AND a.status = 'Scheduled'
     ORDER BY a.appointment_date ASC, a.appointment_time ASC
     LIMIT 10"
)->fetchAll();

$recentPatients = $db->query(
    "SELECT * FROM patients ORDER BY created_at DESC LIMIT 6"
)->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Receptionist Dashboard</h1>
        <p class="page-sub">Welcome, <?= sanitize($_SESSION['user_name']) ?>. Manage patients &amp; appointments.</p>
    </div>
    <div class="header-actions">
        <a href="/hospital-system/patients/create.php"     class="btn btn-secondary">+ Patient</a>
        <a href="/hospital-system/appointments/create.php" class="btn btn-primary">+ Appointment</a>
    </div>
</div>

<!-- Stats (receptionist-relevant only) -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card stat-blue">
        <div class="stat-icon">♡</div>
        <div class="stat-body"><div class="stat-num"><?= $totalPatients ?></div><div class="stat-label">Patients</div></div>
        <a href="/hospital-system/patients/index.php" class="stat-link">View all →</a>
    </div>
    <div class="stat-card stat-amber">
        <div class="stat-icon">◷</div>
        <div class="stat-body"><div class="stat-num"><?= $totalAppointments ?></div><div class="stat-label">Total Appointments</div></div>
        <a href="/hospital-system/appointments/index.php" class="stat-link">View all →</a>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">✓</div>
        <div class="stat-body"><div class="stat-num"><?= $scheduledToday ?></div><div class="stat-label">Scheduled Today</div></div>
        <a href="/hospital-system/appointments/index.php" class="stat-link">View →</a>
    </div>
</div>

<div class="dash-grid">
    <!-- Upcoming Appointments -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Upcoming Appointments</h2>
            <a href="/hospital-system/appointments/create.php" class="btn btn-sm btn-primary">+ Book</a>
        </div>
        <?php if (empty($upcomingAppts)): ?>
            <div class="empty-state">No upcoming appointments.</div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($upcomingAppts as $a): ?>
            <tr>
                <td><?= formatDate($a['appointment_date']) ?></td>
                <td><?= formatTime($a['appointment_time']) ?></td>
                <td class="fw-500"><?= sanitize($a['patient_name']) ?></td>
                <td><?= sanitize($a['doctor_name']) ?></td>
                <td>
                    <a href="/hospital-system/appointments/edit.php?id=<?= $a['id'] ?>" class="btn btn-xs btn-ghost">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Patients -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Patients</h2>
            <a href="/hospital-system/patients/create.php" class="btn btn-sm btn-primary">+ Add</a>
        </div>
        <?php if (empty($recentPatients)): ?>
            <div class="empty-state">No patients yet.</div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Name</th><th>Age</th><th>Phone</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($recentPatients as $p): ?>
            <tr>
                <td><a href="/hospital-system/patients/view.php?id=<?= $p['id'] ?>" class="table-link fw-500"><?= sanitize($p['name']) ?></a></td>
                <td><?= $p['age'] ?></td>
                <td><?= sanitize($p['phone']) ?></td>
                <td>
                    <a href="/hospital-system/appointments/create.php?patient_id=<?= $p['id'] ?>" class="btn btn-xs btn-primary">Book</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
