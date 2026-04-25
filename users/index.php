<?php
// ============================================================
// users/index.php — User Management  (Admin only)
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

$db    = getDB();
$users = $db->query(
    "SELECT u.*, d.name AS doctor_name
     FROM users u
     LEFT JOIN doctors d ON d.user_id = u.id
     ORDER BY u.role ASC, u.full_name ASC"
)->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-sub"><?= count($users) ?> system user<?= count($users) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="/hospital-system/users/create.php" class="btn btn-primary">+ Add User</a>
</div>

<div class="card">
    <div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Linked Doctor</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $i => $u): ?>
        <tr>
            <td class="text-muted"><?= $i + 1 ?></td>
            <td class="fw-500"><?= sanitize($u['full_name']) ?></td>
            <td><code><?= sanitize($u['username']) ?></code></td>
            <td><?= getRoleBadge($u['role']) ?></td>
            <td class="text-muted">
                <?= $u['doctor_name'] ? sanitize($u['doctor_name']) : '—' ?>
            </td>
            <td class="text-muted"><?= formatDate($u['created_at']) ?></td>
            <td>
                <div class="action-btns">
                    <a href="/hospital-system/users/edit.php?id=<?= $u['id'] ?>" class="btn btn-xs btn-ghost">✎ Edit</a>
                    <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                    <a href="/hospital-system/users/delete.php?id=<?= $u['id'] ?>"
                       class="btn btn-xs btn-danger-ghost confirm-delete"
                       data-name="<?= sanitize($u['full_name']) ?>">✕</a>
                    <?php else: ?>
                    <span class="btn btn-xs btn-ghost" style="opacity:.4;cursor:default;" title="Cannot delete yourself">✕</span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Role permission reference -->
<div class="card">
    <div class="card-header"><h2 class="card-title">Role Permissions Reference</h2></div>
    <div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Permission</th>
                <th>Admin</th>
                <th>Receptionist</th>
                <th>Doctor</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $perms = [
            ['Admin Dashboard & Reports',       true,  false, false],
            ['User Management',                  true,  false, false],
            ['Manage Doctors',                   true,  false, false],
            ['Manage Patients (full CRUD)',       true,  true,  false],
            ['View Patients',                    true,  true,  true],
            ['Manage Appointments',              true,  true,  false],
            ['View Appointments',                true,  true,  true],
            ['Add / Edit Medical Records',       true,  false, true],
            ['View Medical Records',             true,  false, true],
        ];
        foreach ($perms as [$label, $admin, $recep, $doc]): ?>
        <tr>
            <td class="fw-500"><?= $label ?></td>
            <td><?= $admin ? '<span class="badge badge-green">✓ Yes</span>' : '<span class="badge badge-gray">✕ No</span>' ?></td>
            <td><?= $recep ? '<span class="badge badge-green">✓ Yes</span>' : '<span class="badge badge-gray">✕ No</span>' ?></td>
            <td><?= $doc   ? '<span class="badge badge-green">✓ Yes</span>' : '<span class="badge badge-gray">✕ No</span>' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
