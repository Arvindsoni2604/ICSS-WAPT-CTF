<?php
declare(strict_types=1);
// IMPORTANT: no whitespace or output before this opening PHP tag.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ]);
    session_start();
}
require_once __DIR__ . '/database.php';

// Safe runtime migration for installations created before wallet support.
// Existing MySQL volumes do not rerun database/init.sql automatically.
try {
    $pdo = db();
    $columns = $pdo->query("SHOW COLUMNS FROM users LIKE 'wallet_balance'")->fetchAll();
    if (!$columns) {
        $pdo->exec("ALTER TABLE users ADD COLUMN wallet_balance DECIMAL(12,2) NOT NULL DEFAULT 100.00");
    }
} catch (Throwable $e) {
    error_log('Wallet schema migration failed: ' . $e->getMessage());
    http_response_code(503);
    exit('The training service is temporarily unavailable. Please contact the instructor.');
}
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if (!hash_equals((string)($_SESSION['csrf'] ?? ''), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400); exit('Invalid request token. Go back and try again.');
    }
}
function logged_in(): bool { return isset($_SESSION['user_id']); }
function require_login(): void { if (!logged_in()) { header('Location: /login.php'); exit; } }
function flash(string $msg): void { $_SESSION['flash'] = $msg; }
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        echo '<div class="notice">'.h((string)$_SESSION['flash']).'</div>';
        unset($_SESSION['flash']);
    }
}
function is_admin(): bool { return in_array(($_SESSION['role'] ?? ''), ['admin', 'super_admin'], true); }
function is_super_admin(): bool { return ($_SESSION['role'] ?? '') === 'super_admin'; }

/** Provision the designated super administrator. Credentials are stored as password hashes. */
function ensure_classroom_admins(): void {
    try {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->execute(['Admin']);
        $existing = $stmt->fetch();
        $hash = password_hash('Anime@123', PASSWORD_DEFAULT);
        if (!$existing) {
            $insert = $pdo->prepare('INSERT INTO users (username, email, password_hash, role, wallet_balance) VALUES (?, ?, ?, ?, 100.00)');
            $insert->execute(['Admin', 'admin@icss.local', $hash, 'super_admin']);
        } else {
            // Preserve password changes made by the super admin. Only ensure the role.
            $upd = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
            $upd->execute(['super_admin', (int)$existing['id']]);
        }
        // Older administrator accounts remain normal, read-only admins.
        $pdo->exec("UPDATE users SET role = 'admin' WHERE role = 'administrator'");
        // Uniform challenge scoring requested for this classroom project.
        $pdo->exec('UPDATE flags SET points = 10 WHERE points <> 10');
    } catch (Throwable $e) {
        error_log('Super-admin provisioning failed: ' . $e->getMessage());
    }
}
ensure_classroom_admins();

function wallet_display(): string {
    if (is_super_admin()) return 'Unlimited';
    $stmt = db()->prepare('SELECT wallet_balance FROM users WHERE id = ?');
    $stmt->execute([(int)($_SESSION['user_id'] ?? 0)]);
    $balance = $stmt->fetchColumn();
    return '₹' . number_format((float)($balance === false ? 0 : $balance), 2);
}
