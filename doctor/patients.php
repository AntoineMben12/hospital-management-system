<?php
// ============================================================
// doctor/patients.php — Patients assigned to this doctor
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireDoctor();
require_once __DIR__ . '/../includes/header.php';

$db       = getDB();
$doctorId = $_SESSION['doctor_id'] ?? 0;

// Patients who have at least one appointment with this doctor
$patients = $db->prepare(
    "SELECT DISTINCT p.*,
            COUNT(a.id)   AS total_appointments,
            MAX(a.appointment_date) AS last_seen
     FROM patients p
     JOIN appointments a ON a.patient_id = p.id
     WHERE a.doctor_id = ?
     GROUP BY p.id
     ORDER BY last_seen DESC"
);
$patients->execute([$doctorId]);
$patients = $patients->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">My Patients</h1>
        <p class="page-sub"><?= count($patients) ?> patient<?= count($patients) !== 1 ? 's' : '' ?> assigned</p>
    </div>
</div>

<div class="card">
    <?php if (empty($patients)): ?>
        <div class="empty-state">No patients assigned yet. Patients appear here once an appointment is booked with you.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Phone</th>
                <th>Appointments</th>
                <th>Last Seen</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($patients as $i => $p): ?>
        <tr>
            <td class="text-muted"><?= $i + 1 ?></td>
            <td>
                <a href="/hospital-system/patients/view.php?id=<?= $p['id'] ?>" class="table-link fw-500">
                    <?= sanitize($p['name']) ?>
                </a>
            </td>
            <td><?= $p['age'] ?></td>
            <td><?= sanitize($p['gender']) ?></td>
            <td><?= sanitize($p['phone']) ?></td>
            <td><span class="badge badge-blue"><?= $p['total_appointments'] ?></span></td>
            <td><?= formatDate($p['last_seen']) ?></td>
            <td>
                <div class="action-btns">
                    <a href="/hospital-system/patients/view.php?id=<?= $p['id'] ?>" class="btn btn-xs btn-ghost">View</a>
                    <a href="/hospital-system/records/create.php?patient_id=<?= $p['id'] ?>" class="btn btn-xs btn-primary">+ Record</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
