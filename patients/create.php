<?php
// ============================================================
// patients/create.php — Add new patient
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdminOrReceptionist();
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$data   = ['name'=>'','age'=>'','gender'=>'','phone'=>'','address'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name']    = trim($_POST['name']    ?? '');
    $data['age']     = trim($_POST['age']     ?? '');
    $data['gender']  = trim($_POST['gender']  ?? '');
    $data['phone']   = trim($_POST['phone']   ?? '');
    $data['address'] = trim($_POST['address'] ?? '');

    if ($data['name']   === '') $errors[] = 'Name is required.';
    if ($data['age']    === '' || !is_numeric($data['age']) || $data['age'] < 0 || $data['age'] > 150)
        $errors[] = 'A valid age (0–150) is required.';
    if (!in_array($data['gender'], ['Male','Female','Other'])) $errors[] = 'Please select a gender.';
    if ($data['phone']  === '') $errors[] = 'Phone number is required.';

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare(
            "INSERT INTO patients (name, age, gender, phone, address) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$data['name'], (int)$data['age'], $data['gender'], $data['phone'], $data['address']]);
        setFlash('success', 'Patient "' . $data['name'] . '" added successfully.');
        redirect('/hospital-system/patients/index.php');
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Add Patient</h1>
        <p class="page-sub"><a href="/hospital-system/patients/index.php" class="breadcrumb-link">Patients</a> / New</p>
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

    <form method="POST" action="" novalidate id="patientForm">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="name">Full Name <span class="req">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?= sanitize($data['name']) ?>" placeholder="e.g. Alice Johnson" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="age">Age <span class="req">*</span></label>
                <input type="number" id="age" name="age" class="form-control"
                       value="<?= sanitize($data['age']) ?>" min="0" max="150" placeholder="e.g. 34" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="gender">Gender <span class="req">*</span></label>
                <select id="gender" name="gender" class="form-control" required>
                    <option value="">— Select —</option>
                    <?php foreach (['Male','Female','Other'] as $g): ?>
                    <option value="<?= $g ?>" <?= $data['gender'] === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Phone <span class="req">*</span></label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="<?= sanitize($data['phone']) ?>" placeholder="e.g. +1-555-0101" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="address">Address</label>
            <textarea id="address" name="address" class="form-control" rows="3"
                      placeholder="Street, City, State…"><?= sanitize($data['address']) ?></textarea>
        </div>

        <div class="form-actions">
            <a href="/hospital-system/patients/index.php" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Patient</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
