<?php
// ============================================================
// doctor/appointments.php — Doctor's own appointments
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireDoctor();
require_once __DIR__ . '/../includes/header.php';

$db       = getDB();
$doctorId = $_SESSION['doctor_id'] ?? 0;

$status = $_GET['status'] ?? '';
$date   = $_GET['date']   ?? '';

$where  = ['a.doctor_id = ?'];
$params = [$doctorId];

if ($status !== '') { $where[] = 'a.status = ?'; $params[] = $status; }
if ($date   !== '') { $where[] = 'a.appointment_date = ?'; $params[] = $date; }

$sql = "SELECT a.*, p.name AS patient_name, p.age, p.gender, p.phone
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">My Appointments</h1>
        <p class="page-sub"><?= count($appointments) ?> appointment<?= count($appointments) !== 1 ? 's' : '' ?> found</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="filter-form">
            <select name="status" class="form-control" style="width:auto">
                <option value="">All Statuses</option>
                <?php foreach (['Scheduled','Completed','Cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date" class="form-control" style="width:auto" value="<?= sanitize($date) ?>">
            <button type="submit" class="btn btn-secondary">Filter</button>
            <?php if ($status || $date): ?>
            <a href="/hospital-system/doctor/appointments.php" class="btn btn-ghost">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($appointments)): ?>
        <div class="empty-state">No appointments found for the selected filter.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Patient</th>
                <th>Age</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
        <tr>
            <td><?= formatDate($a['appointment_date']) ?></td>
            <td class="fw-500"><?= formatTime($a['appointment_time']) ?></td>
            <td>
                <a href="/hospital-system/patients/view.php?id=<?= $a['patient_id'] ?>" class="table-link fw-500">
                    <?= sanitize($a['patient_name']) ?>
                </a>
            </td>
            <td><?= $a['age'] ?></td>
            <td><?= sanitize($a['phone']) ?></td>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
