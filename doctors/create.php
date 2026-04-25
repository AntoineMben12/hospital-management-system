<?php
// ============================================================
// doctors/create.php — Add new doctor
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

$errors = [];
$data   = ['name'=>'','specialty'=>'','phone'=>'','email'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name']      = trim($_POST['name']      ?? '');
    $data['specialty'] = trim($_POST['specialty'] ?? '');
    $data['phone']     = trim($_POST['phone']     ?? '');
    $data['email']     = trim($_POST['email']     ?? '');

    if ($data['name']      === '') $errors[] = 'Name is required.';
    if ($data['specialty'] === '') $errors[] = 'Specialty is required.';
    if ($data['phone']     === '') $errors[] = 'Phone is required.';
    if ($data['email']     === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $errors[] = 'A valid email address is required.';

    if (empty($errors)) {
        $db = getDB();
        // Check email uniqueness
        $check = $db->prepare("SELECT id FROM doctors WHERE email = ?");
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            $errors[] = 'A doctor with this email already exists.';
        } else {
            $stmt = $db->prepare(
                "INSERT INTO doctors (name, specialty, phone, email) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$data['name'], $data['specialty'], $data['phone'], $data['email']]);
            setFlash('success', 'Dr. ' . $data['name'] . ' added successfully.');
            redirect('/hospital-system/doctors/index.php');
        }
    }
}

$specialties = [
    'Cardiology','Dermatology','Emergency Medicine','Endocrinology',
    'Gastroenterology','General Practice','General Surgery','Gynecology',
    'Hematology','Nephrology','Neurology','Oncology','Ophthalmology',
    'Orthopedics','Pediatrics','Psychiatry','Pulmonology','Radiology',
    'Rheumatology','Urology'
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Add Doctor</h1>
        <p class="page-sub"><a href="/hospital-system/doctors/index.php" class="breadcrumb-link">Doctors</a> / New</p>
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
                       value="<?= sanitize($data['name']) ?>" placeholder="Dr. First Last" required>
            </div>
            <div class="form-group">
                <label class="form-label">Specialty <span class="req">*</span></label>
                <input list="specialties" name="specialty" class="form-control"
                       value="<?= sanitize($data['specialty']) ?>" placeholder="e.g. Cardiology" required>
                <datalist id="specialties">
                    <?php foreach ($specialties as $s): ?>
                    <option value="<?= $s ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Phone <span class="req">*</span></label>
                <input type="tel" name="phone" class="form-control"
                       value="<?= sanitize($data['phone']) ?>" placeholder="+1-555-0101" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email <span class="req">*</span></label>
                <input type="email" name="email" class="form-control"
                       value="<?= sanitize($data['email']) ?>" placeholder="doctor@hospital.com" required>
            </div>
        </div>

        <div class="form-actions">
            <a href="/hospital-system/doctors/index.php" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Doctor</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
