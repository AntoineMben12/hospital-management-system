<?php
// ============================================================
// appointments/index.php — List appointments
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdminOrReceptionist();
require_once __DIR__ . '/../includes/header.php';

$db     = getDB();
$status = $_GET['status'] ?? '';
$date   = $_GET['date']   ?? '';

$where  = [];
$params = [];

if ($status !== '') { $where[] = 'a.status = ?'; $params[] = $status; }
if ($date   !== '') { $where[] = 'a.appointment_date = ?'; $params[] = $date; }

$sql = "SELECT a.*, p.name AS patient_name, d.name AS doctor_name, d.specialty
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors  d ON a.doctor_id  = d.id";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY a.appointment_date DESC, a.appointment_time DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Appointments</h1>
        <p class="page-sub"><?= count($appointments) ?> appointment<?= count($appointments) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="/hospital-system/appointments/create.php" class="btn btn-primary">+ Book Appointment</a>
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
            <a href="/hospital-system/appointments/index.php" class="btn btn-ghost">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($appointments)): ?>
        <div class="empty-state">No appointments found.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Specialty</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($appointments as $i => $a): ?>
        <tr>
            <td class="text-muted"><?= $a['id'] ?></td>
            <td class="fw-500"><?= sanitize($a['patient_name']) ?></td>
            <td><?= sanitize($a['doctor_name']) ?></td>
            <td class="text-muted"><?= sanitize($a['specialty']) ?></td>
            <td><?= formatDate($a['appointment_date']) ?></td>
            <td><?= formatTime($a['appointment_time']) ?></td>
            <td><?= getStatusBadge($a['status']) ?></td>
            <td>
                <div class="action-btns">
                    <a href="/hospital-system/appointments/edit.php?id=<?= $a['id'] ?>" class="btn btn-xs btn-ghost" title="Edit">✎</a>
                    <a href="/hospital-system/appointments/delete.php?id=<?= $a['id'] ?>"
                       class="btn btn-xs btn-danger-ghost confirm-delete"
                       data-name="appointment #<?= $a['id'] ?>">✕</a>
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
