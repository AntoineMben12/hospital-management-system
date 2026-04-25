<?php
// ============================================================
// auth/login.php — Login with role-based redirect
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            // doctor role also stores doctor_id for quick queries
            if ($user['role'] === 'doctor') {
                $dstmt = $db->prepare("SELECT id FROM doctors WHERE user_id = ? LIMIT 1");
                $dstmt->execute([$user['id']]);
                $drow = $dstmt->fetch();
                $_SESSION['doctor_id'] = $drow ? $drow['id'] : null;
            }
            $destination = dashboardUrl();
            if (!empty($_SESSION['redirect_after_login'])) {
                $destination = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
            }
            redirect($destination);
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCore HMS — Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/hospital-system/style.css">
</head>
<body class="login-body">
<div class="login-layout">
    <div class="login-panel">
        <div class="login-brand">
            <div class="login-brand-icon">✦</div>
            <h1 class="login-brand-name">MediCore</h1>
            <p class="login-brand-sub">Hospital Management System</p>
        </div>
        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom:1.5rem;"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="" class="login-form" novalidate>
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       value="<?= sanitize($_POST['username'] ?? '') ?>"
                       placeholder="Enter your username" autocomplete="username" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Enter your password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Sign In →</button>
        </form>
        <div class="login-credentials-hint">
            <p class="hint-title">Demo Credentials</p>
            <div class="hint-grid">
                <div class="hint-row"><span class="badge badge-purple">Admin</span><code>admin</code> / <code>password</code></div>
                <div class="hint-row"><span class="badge badge-amber">Reception</span><code>reception1</code> / <code>password</code></div>
                <div class="hint-row"><span class="badge badge-blue">Doctor</span><code>dr.carter</code> / <code>password</code></div>
            </div>
        </div>
    </div>
    <div class="login-visual">
        <div class="login-visual-inner">
            <div class="login-stat"><span class="login-stat-num">3</span><span class="login-stat-label">Role Types</span></div>
            <div class="login-stat"><span class="login-stat-num">∞</span><span class="login-stat-label">Patient Records</span></div>
            <div class="login-stat"><span class="login-stat-num">24/7</span><span class="login-stat-label">System Access</span></div>
        </div>
        <p class="login-visual-quote">"Quality care begins with quality data."</p>
    </div>
</div>
<script src="/hospital-system/script.js"></script>
</body>
</html>
