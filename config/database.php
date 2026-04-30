<?php
// ============================================================
// config/database.php — Database connection via PDO
// Supports environment variables for AWS/Cloud deployment
// ============================================================

/**
 * Get environment variable with optional default.
 * Works with Docker, AWS Elastic Beanstalk, .env files, etc.
 */
function getEnvVar(string $key, ?string $default = null): string {
    // Try environment variable first
    $value = getenv($key);
    if ($value === false) {
        $value = $default;
    }
    
    // If still null and we have a .env file, try loading from it
    if ($value === null && file_exists(__DIR__ . '/../.env')) {
        static $envLoaded = false;
        if (!$envLoaded) {
            $envLoaded = true;
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') === false) continue;
                list($k, $v) = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v);
                // Remove quotes if present
                if (preg_match('/^["\'](.*)["\']\s*$/', $v, $matches)) {
                    $v = $matches[1];
                }
                putenv("$k=$v");
                $_ENV[$key] = $v;
            }
        }
        $value = getenv($key) ?: $default;
    }
    
    return $value ?? '';
}

// Database configuration - uses env vars with local defaults
define('DB_HOST', getEnvVar('DB_HOST', 'localhost'));
define('DB_NAME', getEnvVar('DB_NAME', 'hospital'));
define('DB_USER', getEnvVar('DB_USER', 'root'));
define('DB_PASS', getEnvVar('DB_PASS', ''));
define('DB_CHARSET', getEnvVar('DB_CHARSET', 'utf8mb4'));

// Session configuration for cloud/load balancer deployments
if (getEnvVar('SESSION_COOKIE_DOMAIN', '') !== '') {
    ini_set('session.cookie_domain', getEnvVar('SESSION_COOKIE_DOMAIN'));
}
if (getEnvVar('SESSION_COOKIE_SECURE', 'false') === 'true') {
    ini_set('session.cookie_secure', '1');
}
if (getEnvVar('SESSION_COOKIE_HTTPONLY', 'false') === 'true') {
    ini_set('session.cookie_httponly', '1');
}

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
            // Show appropriate error based on environment
            if (getEnvVar('APP_DEBUG', 'true') === 'true') {
                die('<div style="font-family:monospace;padding:20px;background:#fee;border:1px solid #c00;color:#c00;">
                    <strong>Database Connection Failed:</strong><br>' . htmlspecialchars($e->getMessage()) .
                    '<br><br>Please check your config/database.php settings and ensure MySQL is running.<br>
                    <br><em>DB_HOST: ' . htmlspecialchars(DB_HOST) . '</em></div>');
            } else {
                // Production: generic error
                die('<div style="font-family:monospace;padding:20px;background:#f5f5f5;border:1px solid #ccc;color:#333;">
                    <strong>Application Error:</strong><br>Unable to connect to database. Please try again later.</div>');
            }
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
