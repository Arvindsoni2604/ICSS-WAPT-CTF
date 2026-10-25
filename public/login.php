<?php
require_once __DIR__ . '/../src/bootstrap.php';
if (logged_in()) { header('Location: /dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $identity = trim((string)($_POST['identity'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $stmt = db()->prepare('SELECT id, username, password_hash, role FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) LIMIT 1');
    $stmt->execute([$identity, $identity]); $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']=(int)$user['id']; $_SESSION['username']=$user['username']; $_SESSION['role']=$user['role'];
        header('Location: /dashboard.php'); exit;
    }
    $error = 'Invalid username/email or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in | ICSS</title><link rel="stylesheet" href="/assets/style.css"></head><body><header class="top"><a class="brand" href="/"><span class="mark">IC</span><div><b>ICSS MARKET</b><small>STUDENT ACCESS</small></div></a><a href="/signup.php">Create account</a></header><main class="auth-wrap"><section class="auth-card"><p class="eyebrow">STUDENT ACCESS</p><h1>Welcome back.</h1><p class="muted">Sign in to access your dashboard and challenges.</p><?php if($error): ?><div class="alert"><?=h($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><label>Username or email address</label><input name="identity" required autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Enter your username or email"><label>Password</label><input type="password" name="password" required autocomplete="current-password"><button class="button primary full" type="submit">Sign in ↗</button></form><p class="hint">New here? <a class="text-link" href="/signup.php">Create a student account</a>.</p></section></main><footer>ICSS WAPT CTF · Authorized training only</footer></body></html>
