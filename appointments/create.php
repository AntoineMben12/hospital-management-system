<?php
// ============================================================
// appointments/create.php — Book new appointment
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdminOrReceptionist();
require_once __DIR__ . '/../includes/header.php';

$db = getDB();

$errors = [];
$data   = [
    'patient_id'       => $_GET['patient_id'] ?? '',
    'doctor_id'        => '',
    'appointment_date' => '',
    'appointment_time' => '',
    'status'           => 'Scheduled',
    'notes'            => '',
];

// Load patients and doctors for dropdowns
$patients = $db->query("SELECT id, name FROM patients ORDER BY name ASC")->fetchAll();
$doctors  = $db->query("SELECT id, name, specialty FROM doctors ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['patient_id']       = (int)($_POST['patient_id'] ?? 0);
    $data['doctor_id']        = (int)($_POST['doctor_id']  ?? 0);
    $data['appointment_date'] = trim($_POST['appointment_date'] ?? '');
    $data['appointment_time'] = trim($_POST['appointment_time'] ?? '');
    $data['status']           = trim($_POST['status'] ?? 'Scheduled');
    $data['notes']            = trim($_POST['notes'] ?? '');

    if (!$data['patient_id'])       $errors[] = 'Please select a patient.';
    if (!$data['doctor_id'])        $errors[] = 'Please select a doctor.';
    if ($data['appointment_date'] === '') $errors[] = 'Date is required.';
    if ($data['appointment_time'] === '') $errors[] = 'Time is required.';

    // Double booking check
    if (empty($errors)) {
        $chk = $db->prepare(
            "SELECT id FROM appointments
             WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?"
        );
        $chk->execute([$data['doctor_id'], $data['appointment_date'], $data['appointment_time']]);
        if ($chk->fetch()) {
            $errors[] = 'This doctor already has an appointment at the selected date and time. Please choose a different slot.';
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare(
            "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, notes)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['patient_id'], $data['doctor_id'],
            $data['appointment_date'], $data['appointment_time'],
            $data['status'], $data['notes']
        ]);
        setFlash('success', 'Appointment booked successfully.');
        redirect('/hospital-system/appointments/index.php');
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Book Appointment</h1>
        <p class="page-sub"><a href="/hospital-system/appointments/index.php" class="breadcrumb-link">Appointments</a> / New</p>
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
                <label class="form-label">Patient <span class="req">*</span></label>
                <select name="patient_id" class="form-control" required>
                    <option value="">— Select Patient —</option>
                    <?php foreach ($patients as $p): ?>
                    <option value="<?= $p['id'] ?>"
                        <?= (string)$data['patient_id'] === (string)$p['id'] ? 'selected' : '' ?>>
                        <?= sanitize($p['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Doctor <span class="req">*</span></label>
                <select name="doctor_id" class="form-control" required>
                    <option value="">— Select Doctor —</option>
                    <?php foreach ($doctors as $d): ?>
                    <option value="<?= $d['id'] ?>"
                        <?= (string)$data['doctor_id'] === (string)$d['id'] ? 'selected' : '' ?>>
                        <?= sanitize($d['name']) ?> (<?= sanitize($d['specialty']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Date <span class="req">*</span></label>
                <input type="date" name="appointment_date" class="form-control"
                       value="<?= sanitize($data['appointment_date']) ?>"
                       min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Time <span class="req">*</span></label>
                <input type="time" name="appointment_time" class="form-control"
                       value="<?= sanitize($data['appointment_time']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <?php foreach (['Scheduled','Completed','Cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $data['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"
                      placeholder="Reason for visit, special instructions…"><?= sanitize($data['notes']) ?></textarea>
        </div>

        <div class="form-actions">
            <a href="/hospital-system/appointments/index.php" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Book Appointment</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
