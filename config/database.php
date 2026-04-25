<?php
// ============================================================
// config/database.php — Database connection via PDO
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'hospital');
define('DB_USER', 'root');
define('DB_PASS', '');         // Change if your MySQL has a password
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:monospace;padding:20px;background:#fee;border:1px solid #c00;color:#c00;">
                <strong>Database Connection Failed:</strong><br>' . htmlspecialchars($e->getMessage()) .
                '<br><br>Please check your config/database.php settings and ensure MySQL is running.</div>');
        }
    }
    return $pdo;
}

// ============================================================
// Shared utility functions
// ============================================================

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect('/hospital-system/auth/login.php');
    }
}

function getRoleBadge(string $role): string {
    $map = [
        'admin'        => 'badge-purple',
        'receptionist' => 'badge-amber',
        'doctor'       => 'badge-blue',
    ];
    $cls = $map[$role] ?? 'badge-gray';
    return '<span class="badge ' . $cls . '">' . sanitize(ucfirst($role)) . '</span>';
}

function formatDate(string $date): string {
    return $date ? date('M d, Y', strtotime($date)) : '—';
}

function formatTime(string $time): string {
    return $time ? date('h:i A', strtotime($time)) : '—';
}

function getStatusBadge(string $status): string {
    $map = [
        'Scheduled'  => 'badge-blue',
        'Completed'  => 'badge-green',
        'Cancelled'  => 'badge-red',
    ];
    $cls = $map[$status] ?? 'badge-gray';
    return '<span class="badge ' . $cls . '">' . sanitize($status) . '</span>';
}
