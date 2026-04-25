<?php
// ============================================================
// users/create.php — Add new system user  (Admin only)
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

$db = getDB();

// Doctors without a linked user account (for assigning doctor role)
$unlinkedDoctors = $db->query(
    "SELECT id, name, specialty FROM doctors WHERE user_id IS NULL ORDER BY name ASC"
)->fetchAll();

$errors = [];
$data   = ['full_name'=>'', 'username'=>'', 'role'=>'receptionist', 'doctor_id'=>''];

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
    if ($password === '')          $errors[] = 'Password is required.';
    if (strlen($password) < 6)    $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $password2)  $errors[] = 'Passwords do not match.';
    if ($data['role'] === 'doctor' && $data['doctor_id'] === '')
        $errors[] = 'Please select which doctor profile to link to this account.';

    if (empty($errors)) {
        // Check username uniqueness
        $chk = $db->prepare("SELECT id FROM users WHERE username = ?");
        $chk->execute([$data['username']]);
        if ($chk->fetch()) {
            $errors[] = 'Username "' . sanitize($data['username']) . '" is already taken.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare(
            "INSERT INTO users (username, password, full_name, role) VALUES (?,?,?,?)"
        )->execute([$data['username'], $hash, $data['full_name'], $data['role']]);

        $newUserId = $db->lastInsertId();

        // Link doctor profile if role is doctor
        if ($data['role'] === 'doctor' && $data['doctor_id'] !== '') {
            $db->prepare("UPDATE doctors SET user_id = ? WHERE id = ?")
               ->execute([$newUserId, (int)$data['doctor_id']]);
        }

        setFlash('success', 'User "' . $data['full_name'] . '" created successfully.');
        redirect('/hospital-system/users/index.php');
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Add User</h1>
        <p class="page-sub"><a href="/hospital-system/users/index.php" class="breadcrumb-link">Users</a> / New</p>
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
                       value="<?= sanitize($data['full_name']) ?>" placeholder="e.g. Dr. John Smith" required>
            </div>
            <div class="form-group">
                <label class="form-label">Username <span class="req">*</span></label>
                <input type="text" name="username" class="form-control"
                       value="<?= sanitize($data['username']) ?>" placeholder="e.g. dr.smith" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Password <span class="req">*</span></label>
                <input type="password" name="password" class="form-control"
                       placeholder="Minimum 6 characters" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password <span class="req">*</span></label>
                <input type="password" name="password2" class="form-control"
                       placeholder="Repeat password" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Role <span class="req">*</span></label>
                <select name="role" class="form-control" id="roleSelect" required>
                    <?php foreach (['admin','receptionist','doctor'] as $r): ?>
                    <option value="<?= $r ?>" <?= $data['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="doctorLinkGroup" style="<?= $data['role'] !== 'doctor' ? 'display:none' : '' ?>">
                <label class="form-label">Link to Doctor Profile <span class="req">*</span></label>
                <?php if (empty($unlinkedDoctors)): ?>
                    <p class="text-muted" style="font-size:13px;padding-top:10px;">
                        All doctors already have linked accounts, or no doctors exist.
                        <a href="/hospital-system/doctors/create.php">Add a doctor first</a>.
                    </p>
                <?php else: ?>
                <select name="doctor_id" class="form-control">
                    <option value="">— Select Doctor Profile —</option>
                    <?php foreach ($unlinkedDoctors as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= (string)$data['doctor_id'] === (string)$d['id'] ? 'selected' : '' ?>>
                        <?= sanitize($d['name']) ?> (<?= sanitize($d['specialty']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
        </div>

        <!-- Role description hints -->
        <div class="role-hint" id="hint-admin" style="<?= $data['role'] !== 'admin' ? 'display:none' : '' ?>">
            <div class="alert alert-info-soft">
                <strong>Admin:</strong> Full system access — can manage users, doctors, view all reports.
            </div>
        </div>
        <div class="role-hint" id="hint-receptionist" style="<?= $data['role'] !== 'receptionist' ? 'display:none' : '' ?>">
            <div class="alert alert-info-soft">
                <strong>Receptionist:</strong> Can manage patients and appointments only. No access to reports or doctor management.
            </div>
        </div>
        <div class="role-hint" id="hint-doctor" style="<?= $data['role'] !== 'doctor' ? 'display:none' : '' ?>">
            <div class="alert alert-info-soft">
                <strong>Doctor:</strong> Can view their assigned patients, appointments, and manage medical records. No admin access.
            </div>
        </div>

        <div class="form-actions">
            <a href="/hospital-system/users/index.php" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Create User</button>
        </div>
    </form>
</div>

<script>
// Show/hide doctor link field and role hints based on role selection
document.getElementById('roleSelect').addEventListener('change', function () {
    const role = this.value;
    document.getElementById('doctorLinkGroup').style.display = (role === 'doctor') ? '' : 'none';
    document.querySelectorAll('.role-hint').forEach(h => h.style.display = 'none');
    const hint = document.getElementById('hint-' + role);
    if (hint) hint.style.display = '';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
