<?php
// ============================================================
// records/create.php — Add medical record
// ============================================================
require_once __DIR__ . '/../includes/auth_check.php';
requireAdminOrDoctor();
require_once __DIR__ . '/../includes/header.php';

$db = getDB();

$errors = [];
$data   = [
    'patient_id'  => $_GET['patient_id'] ?? '',
    'doctor_id'   => '',
    'diagnosis'   => '',
    'treatment'   => '',
    'record_date' => date('Y-m-d'),
    'notes'       => '',
];

$patients = $db->query("SELECT id, name FROM patients ORDER BY name ASC")->fetchAll();
$doctors  = $db->query("SELECT id, name, specialty FROM doctors ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['patient_id']  = (int)($_POST['patient_id']  ?? 0);
    $data['doctor_id']   = ($_POST['doctor_id'] !== '') ? (int)$_POST['doctor_id'] : null;
    $data['diagnosis']   = trim($_POST['diagnosis']   ?? '');
    $data['treatment']   = trim($_POST['treatment']   ?? '');
    $data['record_date'] = trim($_POST['record_date'] ?? '');
    $data['notes']       = trim($_POST['notes']       ?? '');

    if (!$data['patient_id'])          $errors[] = 'Please select a patient.';
    if ($data['diagnosis']   === '')   $errors[] = 'Diagnosis is required.';
    if ($data['treatment']   === '')   $errors[] = 'Treatment is required.';
    if ($data['record_date'] === '')   $errors[] = 'Date is required.';

    if (empty($errors)) {
        $db->prepare(
            "INSERT INTO medical_records (patient_id, doctor_id, diagnosis, treatment, record_date, notes)
             VALUES (?, ?, ?, ?, ?, ?)"
        )->execute([
            $data['patient_id'], $data['doctor_id'],
            $data['diagnosis'], $data['treatment'],
            $data['record_date'], $data['notes']
        ]);
        setFlash('success', 'Medical record added successfully.');
        redirect('/hospital-system/records/index.php');
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Add Medical Record</h1>
        <p class="page-sub"><a href="/hospital-system/records/index.php" class="breadcrumb-link">Records</a> / New</p>
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
                <label class="form-label">Attending Doctor</label>
                <select name="doctor_id" class="form-control">
                    <option value="">— None / Unknown —</option>
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
                <label class="form-label">Record Date <span class="req">*</span></label>
                <input type="date" name="record_date" class="form-control"
                       value="<?= sanitize($data['record_date']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Diagnosis <span class="req">*</span></label>
            <input type="text" name="diagnosis" class="form-control"
                   value="<?= sanitize($data['diagnosis']) ?>"
                   placeholder="e.g. Hypertension Stage 1" required>
        </div>

        <div class="form-group">
            <label class="form-label">Treatment <span class="req">*</span></label>
            <textarea name="treatment" class="form-control" rows="4"
                      placeholder="Describe prescribed medications, procedures, therapy…"
                      required><?= sanitize($data['treatment']) ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Additional Notes</label>
            <textarea name="notes" class="form-control" rows="3"
                      placeholder="Follow-up instructions, observations…"><?= sanitize($data['notes']) ?></textarea>
        </div>

        <div class="form-actions">
            <a href="/hospital-system/records/index.php" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Record</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
