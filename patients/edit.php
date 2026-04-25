<?php
// ============================================================
// patients/edit.php — Edit existing patient
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdminOrReceptionist();
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    setFlash('danger', 'Patient not found.');
    redirect('/hospital-system/patients/index.php');
}

$errors = [];
$data   = $patient;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name']    = trim($_POST['name']    ?? '');
    $data['age']     = trim($_POST['age']     ?? '');
    $data['gender']  = trim($_POST['gender']  ?? '');
    $data['phone']   = trim($_POST['phone']   ?? '');
    $data['address'] = trim($_POST['address'] ?? '');

    if ($data['name']  === '') $errors[] = 'Name is required.';
    if ($data['age']   === '' || !is_numeric($data['age']) || $data['age'] < 0 || $data['age'] > 150)
        $errors[] = 'A valid age (0–150) is required.';
    if (!in_array($data['gender'], ['Male','Female','Other'])) $errors[] = 'Please select a gender.';
    if ($data['phone'] === '') $errors[] = 'Phone number is required.';

    if (empty($errors)) {
        $stmt = $db->prepare(
            "UPDATE patients SET name=?, age=?, gender=?, phone=?, address=? WHERE id=?"
        );
        $stmt->execute([$data['name'], (int)$data['age'], $data['gender'], $data['phone'], $data['address'], $id]);
        setFlash('success', 'Patient updated successfully.');
        redirect('/hospital-system/patients/index.php');
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Patient</h1>
        <p class="page-sub"><a href="/hospital-system/patients/index.php" class="breadcrumb-link">Patients</a> / Edit</p>
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

    <form method="POST" action="" novalidate>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Full Name <span class="req">*</span></label>
                <input type="text" name="name" class="form-control"
                       value="<?= sanitize($data['name']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Age <span class="req">*</span></label>
                <input type="number" name="age" class="form-control"
                       value="<?= sanitize($data['age']) ?>" min="0" max="150" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Gender <span class="req">*</span></label>
                <select name="gender" class="form-control" required>
                    <?php foreach (['Male','Female','Other'] as $g): ?>
                    <option value="<?= $g ?>" <?= $data['gender'] === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Phone <span class="req">*</span></label>
                <input type="tel" name="phone" class="form-control"
                       value="<?= sanitize($data['phone']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="3"><?= sanitize($data['address']) ?></textarea>
        </div>

        <div class="form-actions">
            <a href="/hospital-system/patients/index.php" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Patient</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
