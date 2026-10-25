<?php
require_once __DIR__ . '/../src/bootstrap.php';
if (logged_in()) { header('Location: /dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_]{3,40}$/', $username)) $error = 'Username must be 3–40 characters: letters, numbers, underscore.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter a valid email address.';
    elseif (strlen($password) < 10) $error = 'Password must be at least 10 characters.';
    else {
        try {
            $stmt = db()->prepare('INSERT INTO users (username,email,password_hash,role,wallet_balance) VALUES (?,?,?,?,100.00)');
            $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), 'student']);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)db()->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'student';
            flash('Welcome! Your account has been created with ₹100.00 in your demo wallet.');
            header('Location: /dashboard.php'); exit;
        } catch (PDOException $e) {
            $error = 'That username or email may already be registered.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create student account | ICSS</title><link rel="stylesheet" href="/assets/style.css"></head><body><header class="top"><a class="brand" href="/"><span class="mark">IC</span><div><b>ICSS MARKET</b><small>STUDENT REGISTRATION</small></div></a><a href="/login.php">Sign in</a></header><main class="auth-wrap"><section class="auth-card"><p class="eyebrow">JOIN THE LAB</p><h1>Create account</h1><p class="muted">Your account keeps your challenge score separate.</p><?php if($error): ?><div class="alert"><?=h($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><label>Username</label><input name="username" required minlength="3" maxlength="40" pattern="[A-Za-z0-9_]+" autocomplete="username"><label>Email address</label><input type="email" name="email" required maxlength="190" autocomplete="email"><label>Password</label><input type="password" name="password" required minlength="10" autocomplete="new-password"><button class="button primary full" type="submit">Create account ↗</button></form><p class="hint">Use a unique training password. Do not reuse your company or personal account password.</p><p>Already registered? <a class="text-link" href="/login.php">Sign in</a></p></section></main><footer>ICSS WAPT CTF · Fictional training environment</footer></body></html>
