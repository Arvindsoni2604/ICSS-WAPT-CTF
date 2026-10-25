<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_login();
if (!is_admin()) { http_response_code(403); exit('Admin access only.'); }
$canManage = is_super_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$canManage) { http_response_code(403); exit('Read-only administrator role. Only the super admin can change user data.'); }
$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$userId) { http_response_code(400); exit('Invalid user ID.'); }
$q = db()->prepare("SELECT id, username, email, role, wallet_balance, created_at FROM users WHERE id = ? LIMIT 1");
$q->execute([$userId]);
$user = $q->fetch();
if (!$user) { http_response_code(404); exit('User not found.'); }

// Administrator-only account management: edit profile/role, set wallet balance, or reset password.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'change_user_id') {
        $newId = filter_var($_POST['new_user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$newId) {
            flash('Enter a valid positive numeric user ID.');
        } elseif ((int)$user['id'] === (int)($_SESSION['user_id'] ?? 0)) {
            flash('For safety, the currently logged-in super admin ID cannot be changed.');
        } elseif ($user['role'] === 'super_admin') {
            flash('The designated super admin account ID cannot be changed here.');
        } else {
            try {
                $pdo = db();
                $exists = $pdo->prepare('SELECT id FROM users WHERE id = ? LIMIT 1');
                $exists->execute([(int)$newId]);
                if ($exists->fetch()) {
                    flash('That user ID is already in use.');
                } else {
                    $pdo->beginTransaction();
                    // Preserve related records while changing the primary key. Foreign-key checks are
                    // disabled only for this connection and restored immediately afterward.
                    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
                    $upd = $pdo->prepare('UPDATE users SET id = ? WHERE id = ?');
                    $upd->execute([(int)$newId, (int)$userId]);
                    $updOrders = $pdo->prepare('UPDATE orders SET user_id = ? WHERE user_id = ?');
                    $updOrders->execute([(int)$newId, (int)$userId]);
                    $updSubs = $pdo->prepare('UPDATE submissions SET user_id = ? WHERE user_id = ?');
                    $updSubs->execute([(int)$newId, (int)$userId]);
                    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
                    $pdo->commit();
                    $userId = (int)$newId;
                    flash('User ID changed. Related orders and challenge submissions were preserved.');
                }
            } catch (Throwable $e) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                try { if (isset($pdo)) $pdo->exec('SET FOREIGN_KEY_CHECKS=1'); } catch (Throwable $ignored) {}
                error_log('Super admin user ID change failed: ' . $e->getMessage());
                flash('Could not change the user ID. Check the application logs.');
            }
        }
    } elseif ($action === 'update_profile') {
        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $role = (string)($_POST['role'] ?? 'student');
        if (!preg_match('/^[A-Za-z0-9_.-]{3,80}$/', $username) || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['student','admin','super_admin'], true)) {
            flash('Enter a valid username, email address, and role.');
        } else {
            if ((int)$user['id'] === (int)($_SESSION['user_id'] ?? 0) && $role !== 'super_admin') {
                flash('You cannot remove your own administrator role.');
            } else {
                try {
                    $upd = db()->prepare('UPDATE users SET username=?, email=?, role=? WHERE id=?');
                    $upd->execute([$username, $email, $role, $userId]);
                    if ((int)$userId === (int)($_SESSION['user_id'] ?? 0)) { $_SESSION['username'] = $username; $_SESSION['role'] = $role; }
                    flash('Account profile updated successfully.');
                } catch (PDOException $e) {
                    flash('Username or email is already in use. No changes were made.');
                }
            }
        }
    } elseif ($action === 'reset_password') {
        $newPassword = (string)($_POST['new_password'] ?? '');
        if (strlen($newPassword) < 12) {
            flash('New password must be at least 12 characters.');
        } else {
            $upd = db()->prepare('UPDATE users SET password_hash=? WHERE id=?');
            $upd->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
            flash('Password reset successfully. Share the new password securely with the account owner.');
        }
    } elseif ($action === 'set_wallet') {
        $balanceRaw = trim((string)($_POST['wallet_balance'] ?? ''));
        if (in_array($user['role'], ['admin','super_admin'], true)) {
            flash('Administrator accounts have unlimited wallet balance.');
        } elseif (!is_numeric($balanceRaw) || (float)$balanceRaw < 0 || (float)$balanceRaw > 1000000) {
            flash('Enter a wallet balance from ₹0 to ₹1,000,000.');
        } else {
            $upd = db()->prepare("UPDATE users SET wallet_balance=? WHERE id=? AND role='student'");
            $upd->execute([number_format((float)$balanceRaw, 2, '.', ''), $userId]);
            flash('Wallet balance updated.');
        }
    } else {
        flash('Unknown account action.');
    }
    header('Location: /admin-user.php?id=' . (int)$userId); exit;
}
// Refresh after any prior action so the page shows current database values.
$q = db()->prepare("SELECT id, username, email, role, wallet_balance, created_at FROM users WHERE id = ? LIMIT 1");
$q->execute([$userId]); $user = $q->fetch();
$q = db()->prepare("SELECT f.title, f.points, s.submitted_at FROM submissions s JOIN flags f ON f.flag_key=s.flag_key WHERE s.user_id=? ORDER BY s.submitted_at DESC");
$q->execute([$userId]); $submissions = $q->fetchAll();
$q = db()->prepare("SELECT id, total, status, created_at FROM orders WHERE user_id=? ORDER BY created_at DESC");
$q->execute([$userId]); $orders = $q->fetchAll();
$q = db()->prepare("SELECT COALESCE(SUM(f.points),0) FROM submissions s JOIN flags f ON f.flag_key=s.flag_key WHERE s.user_id=?");
$q->execute([$userId]); $score = (int)$q->fetchColumn();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Account details | ICSS Admin</title><link rel="stylesheet" href="/assets/style.css"></head><body><header class="top"><a class="brand" href="/admin.php"><span class="mark">IC</span><div><b>ICSS ADMIN</b><small>ACCOUNT DETAILS</small></div></a><a href="/admin.php">Back to admin</a></header><main class="wrap page admin-management-page admin-user-page"><?php show_flash(); ?><p class="eyebrow">ADMINISTRATOR ONLY</p><h1><?=h($user['username'])?></h1><p class="muted">Account details and activity. Passwords and password hashes are intentionally never shown.</p><section class="stats-grid"><article class="stat"><span>Account ID</span><strong><?=(int)$user['id']?></strong></article><article class="stat"><span>Role</span><strong><?=h($user['role'])?></strong></article><article class="stat"><span>Total points</span><strong><?=$score?></strong></article><article class="stat"><span>Wallet</span><strong><?=$user['role']==='super_admin'?'Unlimited':'₹'.number_format((float)$user['wallet_balance'],2)?></strong></article></section><h2>Account actions</h2><div class="admin-actions"><a class="button" href="/admin.php">All users</a><?php if ($canManage && $user['role'] === 'student'): ?><a class="button primary" href="/admin.php?topup_id=<?=(int)$user['id']?>#topup">Add money to wallet</a><?php endif; ?></div><p class="muted">Use the wallet action to add classroom credits. Account passwords and password hashes are never displayed or editable here.</p><?php if ($canManage): ?><h2>Edit account</h2><form method="post" class="wallet-topup-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="update_profile"><label for="username">Username</label><input id="username" name="username" maxlength="80" required value="<?=h($user['username'])?>"><label for="email">Email</label><input id="email" name="email" type="email" maxlength="190" required value="<?=h($user['email'])?>"><label for="role">Role</label><select id="role" name="role" required><option value="student" <?=$user['role']==='student'?'selected':''?>>Student</option><option value="admin" <?=$user['role']==='admin'?'selected':''?>>Admin (read-only)</option><option value="super_admin" <?=$user['role']==='super_admin'?'selected':''?>>Super admin (full access)</option></select><button class="button primary" type="submit">Save account changes</button></form>
<h2>Change numeric user ID</h2><p class="muted">Super-admin only. Related orders and challenge submissions are preserved. The active super-admin account ID is protected.</p><form method="post" class="wallet-topup-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="change_user_id"><label for="new_user_id">New numeric user ID</label><input id="new_user_id" name="new_user_id" type="number" min="1" step="1" required value="<?= (int)$user['id'] ?>"><button class="button" type="submit" onclick="return confirm('Change this user ID and preserve related records?');">Change user ID</button></form>
<h2>Reset password</h2><form method="post" class="wallet-topup-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="reset_password"><label for="new_password">New password (minimum 12 characters)</label><input id="new_password" name="new_password" type="password" minlength="12" autocomplete="new-password" required><button class="button" type="submit">Set new password</button></form>
<?php if ($user['role'] === 'student'): ?><h2>Set wallet balance</h2><form method="post" class="wallet-topup-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="set_wallet"><label for="wallet_balance">New wallet balance (₹)</label><input id="wallet_balance" name="wallet_balance" type="number" min="0" max="1000000" step="0.01" value="<?=h((string)$user['wallet_balance'])?>" required><button class="button" type="submit">Update wallet balance</button></form><?php endif; ?><?php else: ?><p class="muted">Read-only administrator access: profile changes, password resets, wallet changes, and role changes are disabled.</p><?php endif; ?>
<h2>Profile</h2><table class="scores"><tbody><tr><th>Username</th><td><?=h($user['username'])?></td></tr><tr><th>Email</th><td><?=h($user['email'])?></td></tr><tr><th>Registered</th><td><?=h($user['created_at'])?></td></tr><tr><th>Challenges solved</th><td><?=count($submissions)?></td></tr></tbody></table><h2>Flag submissions</h2><table class="scores"><thead><tr><th>Challenge</th><th>Points</th><th>Submitted</th></tr></thead><tbody><?php if (!$submissions): ?><tr><td colspan="3">No submissions yet.</td></tr><?php else: foreach ($submissions as $item): ?><tr><td><?=h($item['title'])?></td><td><?=(int)$item['points']?></td><td><?=h($item['submitted_at'])?></td></tr><?php endforeach; endif; ?></tbody></table><h2>Order history</h2><table class="scores"><thead><tr><th>Order ID</th><th>Total</th><th>Status</th><th>Created</th></tr></thead><tbody><?php if (!$orders): ?><tr><td colspan="4">No orders yet.</td></tr><?php else: foreach ($orders as $order): ?><tr><td>#<?=(int)$order['id']?></td><td>₹<?=number_format((float)$order['total'],2)?></td><td><?=h($order['status'])?></td><td><?=h($order['created_at'])?></td></tr><?php endforeach; endif; ?></tbody></table></main><footer>ICSS · Restricted administrator view</footer></body></html>
