<?php
// ============================================================
// reports/index.php — Reports & Analytics Dashboard
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

$db = getDB();

// ── Core counts ────────────────────────────────────────────
$totalPatients     = (int)$db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalDoctors      = (int)$db->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$totalAppointments = (int)$db->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$totalRecords      = (int)$db->query("SELECT COUNT(*) FROM medical_records")->fetchColumn();

$scheduled  = (int)$db->query("SELECT COUNT(*) FROM appointments WHERE status='Scheduled'")->fetchColumn();
$completed  = (int)$db->query("SELECT COUNT(*) FROM appointments WHERE status='Completed'")->fetchColumn();
$cancelled  = (int)$db->query("SELECT COUNT(*) FROM appointments WHERE status='Cancelled'")->fetchColumn();

$todayAppts = (int)$db->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn();

// ── Gender breakdown ──────────────────────────────────────
$genders = $db->query(
    "SELECT gender, COUNT(*) AS cnt FROM patients GROUP BY gender"
)->fetchAll(PDO::FETCH_KEY_PAIR);

// ── Top 5 doctors by appointment count ───────────────────
$topDoctors = $db->query(
    "SELECT d.name, d.specialty, COUNT(a.id) AS total
     FROM doctors d
     LEFT JOIN appointments a ON a.doctor_id = d.id
     GROUP BY d.id
     ORDER BY total DESC
     LIMIT 5"
)->fetchAll();

// ── Appointments last 7 days ──────────────────────────────
$last7 = $db->query(
    "SELECT DATE_FORMAT(appointment_date,'%a') AS day_label,
            appointment_date,
            COUNT(*) AS cnt
     FROM appointments
     WHERE appointment_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
     GROUP BY appointment_date
     ORDER BY appointment_date ASC"
)->fetchAll();

// Build full 7-day array (fill missing days with 0)
$last7Map = [];
foreach ($last7 as $row) {
    $last7Map[$row['appointment_date']] = (int)$row['cnt'];
}
$chartDays   = [];
$chartCounts = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartDays[]   = date('D', strtotime($d));
    $chartCounts[] = $last7Map[$d] ?? 0;
}

// ── Recent appointments ───────────────────────────────────
$recentAppts = $db->query(
    "SELECT a.*, p.name AS patient_name, d.name AS doctor_name
     FROM appointments a
     JOIN patients p ON a.patient_id = p.id
     JOIN doctors  d ON a.doctor_id  = d.id
     ORDER BY a.created_at DESC
     LIMIT 10"
)->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Reports & Analytics</h1>
        <p class="page-sub">System-wide statistics as of <?= date('F j, Y') ?></p>
    </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid stats-grid-4">
    <div class="stat-card stat-blue">
        <div class="stat-icon">♡</div>
        <div class="stat-body">
            <div class="stat-num"><?= $totalPatients ?></div>
            <div class="stat-label">Total Patients</div>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">✚</div>
        <div class="stat-body">
            <div class="stat-num"><?= $totalDoctors ?></div>
            <div class="stat-label">Total Doctors</div>
        </div>
    </div>
    <div class="stat-card stat-amber">
        <div class="stat-icon">◷</div>
        <div class="stat-body">
            <div class="stat-num"><?= $totalAppointments ?></div>
            <div class="stat-label">Total Appointments</div>
        </div>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-icon">☰</div>
        <div class="stat-body">
            <div class="stat-num"><?= $totalRecords ?></div>
            <div class="stat-label">Medical Records</div>
        </div>
    </div>
</div>

<!-- Appointment Status & Gender -->
<div class="dash-grid">

    <!-- Appointment Status Breakdown -->
    <div class="card">
        <div class="card-header"><h2 class="card-title">Appointment Status</h2></div>
        <div class="report-status-grid">
            <div class="status-block status-blue">
                <div class="status-num"><?= $scheduled ?></div>
                <div class="status-lbl">Scheduled</div>
            </div>
            <div class="status-block status-green">
                <div class="status-num"><?= $completed ?></div>
                <div class="status-lbl">Completed</div>
            </div>
            <div class="status-block status-red">
                <div class="status-num"><?= $cancelled ?></div>
                <div class="status-lbl">Cancelled</div>
            </div>
            <div class="status-block status-amber">
                <div class="status-num"><?= $todayAppts ?></div>
                <div class="status-lbl">Today</div>
            </div>
        </div>

        <?php if ($totalAppointments > 0): ?>
        <div class="progress-section">
            <div class="progress-label">
                <span>Completion rate</span>
                <span><?= round($completed / $totalAppointments * 100) ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill progress-green"
                     style="width:<?= round($completed / $totalAppointments * 100) ?>%"></div>
            </div>
            <div class="progress-label">
                <span>Cancellation rate</span>
                <span><?= round($cancelled / $totalAppointments * 100) ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill progress-red"
                     style="width:<?= round($cancelled / $totalAppointments * 100) ?>%"></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Patient Gender Breakdown -->
    <div class="card">
        <div class="card-header"><h2 class="card-title">Patient Demographics</h2></div>
        <?php if ($totalPatients > 0):
            $male   = (int)($genders['Male']   ?? 0);
            $female = (int)($genders['Female'] ?? 0);
            $other  = (int)($genders['Other']  ?? 0);
        ?>
        <div class="gender-bars">
            <?php
            $genderData = [
                ['label'=>'Male',   'count'=>$male,   'cls'=>'bar-blue'],
                ['label'=>'Female', 'count'=>$female, 'cls'=>'bar-pink'],
                ['label'=>'Other',  'count'=>$other,  'cls'=>'bar-purple'],
            ];
            foreach ($genderData as $g): $pct = $totalPatients > 0 ? round($g['count']/$totalPatients*100) : 0; ?>
            <div class="gender-row">
                <span class="gender-label"><?= $g['label'] ?></span>
                <div class="gender-bar-track">
                    <div class="gender-bar-fill <?= $g['cls'] ?>" style="width:<?= $pct ?>%"></div>
                </div>
                <span class="gender-count"><?= $g['count'] ?> <small>(<?= $pct ?>%)</small></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">No patient data.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Appointments – Last 7 Days (CSS bar chart) -->
<div class="card">
    <div class="card-header"><h2 class="card-title">Appointments — Last 7 Days</h2></div>
    <?php $maxCount = max(array_merge($chartCounts, [1])); ?>
    <div class="bar-chart">
        <?php foreach ($chartCounts as $i => $cnt): ?>
        <div class="bar-col">
            <div class="bar-value"><?= $cnt ?: '' ?></div>
            <div class="bar-bar">
                <div class="bar-fill" style="height:<?= round($cnt / $maxCount * 100) ?>%"></div>
            </div>
            <div class="bar-day"><?= $chartDays[$i] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Top Doctors -->
<div class="dash-grid">
    <div class="card">
        <div class="card-header"><h2 class="card-title">Top Doctors by Appointments</h2></div>
        <?php if (empty($topDoctors)): ?>
        <div class="empty-state">No data.</div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Rank</th><th>Doctor</th><th>Specialty</th><th>Appointments</th></tr></thead>
            <tbody>
            <?php foreach ($topDoctors as $i => $doc): ?>
            <tr>
                <td class="text-muted"><?= $i + 1 ?></td>
                <td class="fw-500"><?= sanitize($doc['name']) ?></td>
                <td><span class="badge badge-green"><?= sanitize($doc['specialty']) ?></span></td>
                <td><?= $doc['total'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Appointments table -->
    <div class="card">
        <div class="card-header"><h2 class="card-title">Recent Appointments</h2></div>
        <?php if (empty($recentAppts)): ?>
        <div class="empty-state">No appointments yet.</div>
        <?php else: ?>
        <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recentAppts as $a): ?>
            <tr>
                <td><?= sanitize($a['patient_name']) ?></td>
                <td class="text-muted"><?= sanitize($a['doctor_name']) ?></td>
                <td><?= formatDate($a['appointment_date']) ?></td>
                <td><?= getStatusBadge($a['status']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
