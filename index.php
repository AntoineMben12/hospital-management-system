<?php
// ============================================================
// index.php — Admin Dashboard  (Admin only)
// ============================================================
require_once __DIR__ . '/includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Stats
$totalPatients     = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalDoctors      = $db->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$totalAppointments = $db->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$totalRecords      = $db->query("SELECT COUNT(*) FROM medical_records")->fetchColumn();

$scheduledToday = $db->query(
    "SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status='Scheduled'"
)->fetchColumn();

// Today's appointments with names
$todayAppts = $db->query(
    "SELECT a.*, p.name AS patient_name, d.name AS doctor_name
     FROM appointments a
     JOIN patients p ON a.patient_id = p.id
     JOIN doctors d ON a.doctor_id = d.id
     WHERE a.appointment_date = CURDATE()
     ORDER BY a.appointment_time ASC
     LIMIT 8"
)->fetchAll();

// Recent patients
$recentPatients = $db->query(
    "SELECT * FROM patients ORDER BY created_at DESC LIMIT 5"
)->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-sub">Welcome back, <?= sanitize($_SESSION['user_name']) ?>. Here's today's overview.</p>
</div>

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card stat-blue">
        <div class="stat-icon">♡</div>
        <div class="stat-body">
            <div class="stat-num"><?= $totalPatients ?></div>
            <div class="stat-label">Total Patients</div>
        </div>
        <a href="/hospital-system/patients/index.php" class="stat-link">View all →</a>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">✚</div>
        <div class="stat-body">
            <div class="stat-num"><?= $totalDoctors ?></div>
            <div class="stat-label">Total Doctors</div>
        </div>
        <a href="/hospital-system/doctors/index.php" class="stat-link">View all →</a>
    </div>
    <div class="stat-card stat-amber">
        <div class="stat-icon">◷</div>
        <div class="stat-body">
            <div class="stat-num"><?= $totalAppointments ?></div>
            <div class="stat-label">Appointments</div>
        </div>
        <a href="/hospital-system/appointments/index.php" class="stat-link">View all →</a>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-icon">☰</div>
        <div class="stat-body">
            <div class="stat-num"><?= $totalRecords ?></div>
            <div class="stat-label">Medical Records</div>
        </div>
        <a href="/hospital-system/records/index.php" class="stat-link">View all →</a>
    </div>
</div>

<!-- Two-column layout -->
<div class="dash-grid">
    <!-- Today's Appointments -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Today's Appointments
                <span class="badge badge-blue"><?= $scheduledToday ?> scheduled</span>
            </h2>
            <a href="/hospital-system/appointments/create.php" class="btn btn-sm btn-primary">+ New</a>
        </div>
        <?php if (empty($todayAppts)): ?>
            <div class="empty-state">No appointments scheduled for today.</div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($todayAppts as $appt): ?>
                <tr>
                    <td><?= formatTime($appt['appointment_time']) ?></td>
                    <td><?= sanitize($appt['patient_name']) ?></td>
                    <td><?= sanitize($appt['doctor_name']) ?></td>
                    <td><?= getStatusBadge($appt['status']) ?></td>
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
            <div class="empty-state">No patients found.</div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentPatients as $p): ?>
                <tr>
                    <td>
                        <a href="/hospital-system/patients/view.php?id=<?= $p['id'] ?>" class="table-link">
                            <?= sanitize($p['name']) ?>
                        </a>
                    </td>
                    <td><?= $p['age'] ?></td>
                    <td><?= sanitize($p['gender']) ?></td>
                    <td><?= sanitize($p['phone']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
