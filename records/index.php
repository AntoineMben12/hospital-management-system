<?php
// ============================================================
// records/index.php — Medical Records list
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdminOrDoctor();
require_once __DIR__ . '/../includes/header.php';

$db     = getDB();
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $db->prepare(
        "SELECT r.*, p.name AS patient_name, d.name AS doctor_name
         FROM medical_records r
         JOIN patients p ON r.patient_id = p.id
         LEFT JOIN doctors d ON r.doctor_id = d.id
         WHERE p.name LIKE ? OR r.diagnosis LIKE ?
         ORDER BY r.record_date DESC"
    );
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like]);
} else {
    $stmt = $db->query(
        "SELECT r.*, p.name AS patient_name, d.name AS doctor_name
         FROM medical_records r
         JOIN patients p ON r.patient_id = p.id
         LEFT JOIN doctors d ON r.doctor_id = d.id
         ORDER BY r.record_date DESC"
    );
}
$records = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Medical Records</h1>
        <p class="page-sub"><?= count($records) ?> record<?= count($records) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="/hospital-system/records/create.php" class="btn btn-primary">+ Add Record</a>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="search-form">
            <input type="text" name="search" class="form-control search-input"
                   placeholder="Search by patient name or diagnosis…"
                   value="<?= sanitize($search) ?>">
            <button type="submit" class="btn btn-secondary">Search</button>
            <?php if ($search): ?>
            <a href="/hospital-system/records/index.php" class="btn btn-ghost">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($records)): ?>
        <div class="empty-state">No medical records found.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Patient</th>
                <th>Diagnosis</th>
                <th>Treatment</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($records as $i => $r): ?>
        <tr>
            <td class="text-muted"><?= $i + 1 ?></td>
            <td>
                <a href="/hospital-system/patients/view.php?id=<?= $r['patient_id'] ?>" class="table-link fw-500">
                    <?= sanitize($r['patient_name']) ?>
                </a>
            </td>
            <td class="fw-500"><?= sanitize($r['diagnosis']) ?></td>
            <td class="text-truncate" style="max-width:220px;"><?= sanitize($r['treatment']) ?></td>
            <td class="text-muted"><?= sanitize($r['doctor_name'] ?? '—') ?></td>
            <td><?= formatDate($r['record_date']) ?></td>
            <td>
                <div class="action-btns">
                    <a href="/hospital-system/records/edit.php?id=<?= $r['id'] ?>" class="btn btn-xs btn-ghost" title="Edit">✎</a>
                    <a href="/hospital-system/records/delete.php?id=<?= $r['id'] ?>"
                       class="btn btn-xs btn-danger-ghost confirm-delete"
                       data-name="record #<?= $r['id'] ?>">✕</a>
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
