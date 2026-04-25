<?php
// ============================================================
// doctors/edit.php — Edit doctor
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    setFlash('danger', 'Doctor not found.');
    redirect('/hospital-system/doctors/index.php');
}

$errors = [];
$data   = $doctor;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name']      = trim($_POST['name']      ?? '');
    $data['specialty'] = trim($_POST['specialty'] ?? '');
    $data['phone']     = trim($_POST['phone']     ?? '');
    $data['email']     = trim($_POST['email']     ?? '');

    if ($data['name']      === '') $errors[] = 'Name is required.';
    if ($data['specialty'] === '') $errors[] = 'Specialty is required.';
    if ($data['phone']     === '') $errors[] = 'Phone is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';

    if (empty($errors)) {
        $check = $db->prepare("SELECT id FROM doctors WHERE email = ? AND id != ?");
        $check->execute([$data['email'], $id]);
        if ($check->fetch()) {
            $errors[] = 'Another doctor already uses this email.';
        } else {
            $db->prepare(
                "UPDATE doctors SET name=?, specialty=?, phone=?, email=? WHERE id=?"
            )->execute([$data['name'], $data['specialty'], $data['phone'], $data['email'], $id]);
            setFlash('success', 'Doctor updated successfully.');
            redirect('/hospital-system/doctors/index.php');
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Doctor</h1>
        <p class="page-sub"><a href="/hospital-system/doctors/index.php" class="breadcrumb-link">Doctors</a> / Edit</p>
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
                <input type="text" name="name" class="form-control" value="<?= sanitize($data['name']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Specialty <span class="req">*</span></label>
                <input type="text" name="specialty" class="form-control" value="<?= sanitize($data['specialty']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Phone <span class="req">*</span></label>
                <input type="tel" name="phone" class="form-control" value="<?= sanitize($data['phone']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email <span class="req">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= sanitize($data['email']) ?>" required>
            </div>
        </div>
        <div class="form-actions">
            <a href="/hospital-system/doctors/index.php" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Doctor</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
