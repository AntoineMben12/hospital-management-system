<?php
// ============================================================
// doctors/index.php — List all doctors
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

$db     = getDB();
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $db->prepare(
        "SELECT * FROM doctors WHERE name LIKE ? OR specialty LIKE ? OR email LIKE ?
         ORDER BY name ASC"
    );
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $db->query("SELECT * FROM doctors ORDER BY name ASC");
}
$doctors = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Doctors</h1>
        <p class="page-sub"><?= count($doctors) ?> doctor<?= count($doctors) !== 1 ? 's' : '' ?> on staff</p>
    </div>
    <a href="/hospital-system/doctors/create.php" class="btn btn-primary">+ Add Doctor</a>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="search-form">
            <input type="text" name="search" class="form-control search-input"
                   placeholder="Search by name, specialty, email…"
                   value="<?= sanitize($search) ?>">
            <button type="submit" class="btn btn-secondary">Search</button>
            <?php if ($search): ?>
            <a href="/hospital-system/doctors/index.php" class="btn btn-ghost">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($doctors)): ?>
        <div class="empty-state">No doctors found.</div>
    <?php else: ?>
    <div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Specialty</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($doctors as $i => $d): ?>
            <tr>
                <td class="text-muted"><?= $i + 1 ?></td>
                <td class="fw-500"><?= sanitize($d['name']) ?></td>
                <td>
                    <span class="badge badge-green"><?= sanitize($d['specialty']) ?></span>
                </td>
                <td><?= sanitize($d['phone']) ?></td>
                <td><?= sanitize($d['email']) ?></td>
                <td>
                    <div class="action-btns">
                        <a href="/hospital-system/doctors/edit.php?id=<?= $d['id'] ?>" class="btn btn-xs btn-ghost" title="Edit">✎</a>
                        <a href="/hospital-system/doctors/delete.php?id=<?= $d['id'] ?>"
                           class="btn btn-xs btn-danger-ghost confirm-delete"
                           data-name="<?= sanitize($d['name']) ?>">✕</a>
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
