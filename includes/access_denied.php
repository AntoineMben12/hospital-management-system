<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied — MediCore HMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f4f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a1a2e;
        }
        .denied-box {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 56px 48px;
            text-align: center;
            max-width: 460px;
            width: 94vw;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,.12);
        }
        .denied-icon {
            font-size: 52px;
            margin-bottom: 20px;
            display: block;
        }
        .denied-code {
            font-family: 'Playfair Display', serif;
            font-size: 64px;
            font-weight: 600;
            color: #dc2626;
            line-height: 1;
            margin-bottom: 8px;
        }
        .denied-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .denied-msg {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .denied-role {
            display: inline-block;
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 28px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 22px;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background .18s;
            margin: 4px;
        }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-ghost { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
        .btn-ghost:hover { background: #e5e7eb; }
    </style>
</head>
<body>

<div class="denied-box">
    <span class="denied-icon">🔒</span>
    <div class="denied-code">403</div>
    <h1 class="denied-title">Access Denied</h1>
    <?php
    $role = $_SESSION['user_role'] ?? 'unknown';
    $name = $_SESSION['user_name'] ?? 'User';
    ?>
    <span class="denied-role">Signed in as: <?= htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8') ?></span>
    <p class="denied-msg">
        Sorry, <strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong>.
        Your <strong><?= htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8') ?></strong> account
        does not have permission to view this page.
        Please contact your system administrator if you believe this is a mistake.
    </p>
    <div>
        <?php
        $dash = '/hospital-system/auth/login.php';
        if (!empty($_SESSION['user_id'])) {
            $dash = match ($role) {
                'admin'        => '/hospital-system/index.php',
                'receptionist' => '/hospital-system/receptionist/dashboard.php',
                'doctor'       => '/hospital-system/doctor/dashboard.php',
                default        => '/hospital-system/auth/login.php',
            };
        }
        ?>
        <a href="<?= $dash ?>" class="btn btn-primary">← Back to Dashboard</a>
        <a href="/hospital-system/auth/logout.php" class="btn btn-ghost">Sign Out</a>
    </div>
</div>

</body>
</html>
