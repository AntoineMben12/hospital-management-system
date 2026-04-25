<?php
// ============================================================
// patients/index.php — List all patients
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdminOrReceptionist();
require_once __DIR__ . '/../includes/header.php';

$db = getDB();

// Search
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $db->prepare(
        "SELECT * FROM patients
         WHERE name LIKE ? OR phone LIKE ? OR gender LIKE ?
         ORDER BY created_at DESC"
    );
    $like = '%' . $search . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $db->query("SELECT * FROM patients ORDER BY created_at DESC");
}
$patients = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Patients</h1>
        <p class="page-sub"><?= count($patients) ?> patient<?= count($patients) !== 1 ? 's' : '' ?> found</p>
    </div>
    <a href="/hospital-system/patients/create.php" class="btn btn-primary">+ Add Patient</a>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="search-form">
            <input type="text" name="search" class="form-control search-input"
                   placeholder="Search by name, phone, gender…"
                   value="<?= sanitize($search) ?>">
            <button type="submit" class="btn btn-secondary">Search</button>
            <?php if ($search): ?>
            <a href="/hospital-system/patients/index.php" class="btn btn-ghost">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($patients)): ?>
        <div class="empty-state">
            <?= $search ? 'No patients match your search.' : 'No patients yet. Add your first patient!' ?>
        </div>
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
                <th>Address</th>
                <th>Registered</th>
                <th>Actions</th>
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
                <td class="text-truncate" style="max-width:180px"><?= sanitize($p['address']) ?></td>
                <td class="text-muted"><?= formatDate($p['created_at']) ?></td>
                <td>
                    <div class="action-btns">
                        <a href="/hospital-system/patients/view.php?id=<?= $p['id'] ?>" class="btn btn-xs btn-ghost" title="View">👁</a>
                        <a href="/hospital-system/patients/edit.php?id=<?= $p['id'] ?>" class="btn btn-xs btn-ghost" title="Edit">✎</a>
                        <a href="/hospital-system/patients/delete.php?id=<?= $p['id'] ?>"
                           class="btn btn-xs btn-danger-ghost confirm-delete" title="Delete"
                           data-name="<?= sanitize($p['name']) ?>">✕</a>
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
