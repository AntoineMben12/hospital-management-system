<?php
// ============================================================
// users/edit.php — Edit user  (Admin only)
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect('/hospital-system/users/index.php');
}

// Currently linked doctor (if any)
$linkedDoctor = null;
if ($user['role'] === 'doctor') {
    $dstmt = $db->prepare("SELECT * FROM doctors WHERE user_id = ?");
    $dstmt->execute([$id]);
    $linkedDoctor = $dstmt->fetch();
}

// Doctors available for linking (unlinked OR currently linked to this user)
$availDoctors = $db->prepare(
    "SELECT id, name, specialty FROM doctors
     WHERE user_id IS NULL OR user_id = ?
     ORDER BY name ASC"
);
$availDoctors->execute([$id]);
$availDoctors = $availDoctors->fetchAll();

$errors = [];
$data = [
    'full_name' => $user['full_name'],
    'username'  => $user['username'],
    'role'      => $user['role'],
    'doctor_id' => $linkedDoctor ? $linkedDoctor['id'] : '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['full_name'] = trim($_POST['full_name'] ?? '');
    $data['username']  = trim($_POST['username']  ?? '');
    $data['role']      = trim($_POST['role']       ?? '');
    $data['doctor_id'] = trim($_POST['doctor_id']  ?? '');
    $password          = $_POST['password']         ?? '';
    $password2         = $_POST['password2']        ?? '';

    if ($data['full_name'] === '') $errors[] = 'Full name is required.';
    if ($data['username']  === '') $errors[] = 'Username is required.';
    if (!in_array($data['role'], ['admin','receptionist','doctor'])) $errors[] = 'Invalid role.';
    if ($password !== '' && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== '' && $password !== $password2) $errors[] = 'Passwords do not match.';

    // Prevent removing admin role from yourself
    if ((int)$id === (int)$_SESSION['user_id'] && $data['role'] !== 'admin') {
        $errors[] = 'You cannot change your own role.';
    }

    if (empty($errors)) {
        // Username uniqueness (exclude self)
        $chk = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $chk->execute([$data['username'], $id]);
        if ($chk->fetch()) $errors[] = 'Username "' . sanitize($data['username']) . '" is already taken.';
    }

    if (empty($errors)) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET full_name=?, username=?, role=?, password=? WHERE id=?")
               ->execute([$data['full_name'], $data['username'], $data['role'], $hash, $id]);
        } else {
            $db->prepare("UPDATE users SET full_name=?, username=?, role=? WHERE id=?")
               ->execute([$data['full_name'], $data['username'], $data['role'], $id]);
        }

        // Handle doctor link changes
        // First unlink any doctor that was linked to this user
        $db->prepare("UPDATE doctors SET user_id = NULL WHERE user_id = ?")->execute([$id]);

        // Re-link if role is doctor and a doctor_id was provided
        if ($data['role'] === 'doctor' && $data['doctor_id'] !== '') {
            $db->prepare("UPDATE doctors SET user_id = ? WHERE id = ?")
               ->execute([$id, (int)$data['doctor_id']]);
        }

        setFlash('success', 'User updated successfully.');
        redirect('/hospital-system/users/index.php');
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit User</h1>
        <p class="page-sub"><a href="/hospital-system/users/index.php" class="breadcrumb-link">Users</a> / Edit</p>
    </div>
</div>

<div class="card form-card">
    <?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="error-list">
            <?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Full Name <span class="req">*</span></label>
                <input type="text" name="full_name" class="form-control"
                       value="<?= sanitize($data['full_name']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Username <span class="req">*</span></label>
                <input type="text" name="username" class="form-control"
                       value="<?= sanitize($data['username']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                <input type="password" name="password" class="form-control" placeholder="New password (min 6 chars)">
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password2" class="form-control" placeholder="Repeat new password">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Role <span class="req">*</span></label>
                <select name="role" class="form-control" id="roleSelect"
                    <?= ((int)$id === (int)$_SESSION['user_id']) ? 'disabled' : '' ?> required>
                    <?php foreach (['admin','receptionist','doctor'] as $r): ?>
                    <option value="<?= $r ?>" <?= $data['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ((int)$id === (int)$_SESSION['user_id']): ?>
                <input type="hidden" name="role" value="<?= sanitize($data['role']) ?>">
                <p class="text-muted" style="font-size:12px;margin-top:4px;">You cannot change your own role.</p>
                <?php endif; ?>
            </div>

            <div class="form-group" id="doctorLinkGroup"
                 style="<?= $data['role'] !== 'doctor' ? 'display:none' : '' ?>">
                <label class="form-label">Linked Doctor Profile</label>
                <select name="doctor_id" class="form-control">
                    <option value="">— None —</option>
                    <?php foreach ($availDoctors as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= (string)$data['doctor_id'] === (string)$d['id'] ? 'selected' : '' ?>>
                        <?= sanitize($d['name']) ?> (<?= sanitize($d['specialty']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="/hospital-system/users/index.php" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<script>
document.getElementById('roleSelect').addEventListener('change', function () {
    document.getElementById('doctorLinkGroup').style.display = (this.value === 'doctor') ? '' : 'none';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
