<?php
require_once __DIR__ . '/../src/bootstrap.php'; require_login();
if (!is_admin()) { http_response_code(403); exit('Administrator access only.'); }
$canManage = is_super_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$canManage) { http_response_code(403); exit('Read-only administrator role. Only the super admin can change data.'); }
$selectedTopup = filter_input(INPUT_GET, 'topup_id', FILTER_VALIDATE_INT) ?: 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = (string)($_POST['action'] ?? 'topup');
    $targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if ($action === 'delete_user') {
        if (!$targetId) {
            flash('Select a valid account to delete.');
            header('Location: /admin.php'); exit;
        }
        if ($targetId === (int)($_SESSION['user_id'] ?? 0)) {
            flash('You cannot delete the account you are currently using.');
            header('Location: /admin.php'); exit;
        }
        $pdo = db();
        $targetStmt = $pdo->prepare('SELECT id, username, role FROM users WHERE id = ? LIMIT 1');
        $targetStmt->execute([$targetId]);
        $target = $targetStmt->fetch();
        if (!$target) {
            flash('That account no longer exists.');
            header('Location: /admin.php'); exit;
        }
        if ($target['role'] === 'super_admin') {
            flash('The super administrator account cannot be deleted from this screen.');
            header('Location: /admin.php'); exit;
        }
        if ($target['role'] === 'admin') {
            $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('admin','super_admin')")->fetchColumn();
            if ($adminCount <= 1) {
                flash('The last administrator account cannot be deleted.');
                header('Location: /admin.php'); exit;
            }
        }
        try {
            $pdo->beginTransaction();
            // Remove order line items before orders to satisfy foreign-key constraints.
            $deleteItems = $pdo->prepare('DELETE oi FROM order_items oi INNER JOIN orders o ON o.id = oi.order_id WHERE o.user_id = ?');
            $deleteItems->execute([$targetId]);
            $deleteOrders = $pdo->prepare('DELETE FROM orders WHERE user_id = ?');
            $deleteOrders->execute([$targetId]);
            // Submissions are configured to cascade with the user row.
            $deleteUser = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $deleteUser->execute([$targetId]);
            $pdo->commit();
            flash('Deleted account ' . $target['username'] . ' and its associated orders and challenge submissions.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Admin user deletion failed: ' . $e->getMessage());
            flash('The account could not be deleted because related records still exist. Check application logs.');
        }
        header('Location: /admin.php'); exit;
    }

    $amountRaw = trim((string)($_POST['amount'] ?? ''));
    if (!$targetId || !is_numeric($amountRaw) || (float)$amountRaw <= 0 || (float)$amountRaw > 1000000) {
        flash('Enter a valid student and a top-up amount greater than ₹0 and no more than ₹1,000,000.');
    } else {
        $target = db()->prepare("SELECT id, username, role FROM users WHERE id = ? LIMIT 1");
        $target->execute([$targetId]); $targetUser = $target->fetch();
        if (!$targetUser || $targetUser['role'] !== 'student') {
            flash('Wallet top-up is available for student accounts only.');
        } else {
            $update = db()->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ? AND role = 'student'");
            $update->execute([number_format((float)$amountRaw, 2, '.', ''), $targetId]);
            flash('Added ₹' . number_format((float)$amountRaw, 2) . ' to ' . $targetUser['username'] . "'s wallet.");
        }
    }
    header('Location: /admin.php'); exit;
}
$users=db()->query('SELECT u.id,u.username,u.email,u.role,u.wallet_balance,u.created_at,COALESCE(SUM(f.points),0) AS score,COUNT(s.id) AS submitted_count FROM users u LEFT JOIN submissions s ON s.user_id=u.id LEFT JOIN flags f ON f.flag_key=s.flag_key GROUP BY u.id ORDER BY u.created_at DESC')->fetchAll();
$submissionDetails=db()->query('SELECT u.username,f.title,f.points,s.submitted_at FROM submissions s JOIN users u ON u.id=s.user_id JOIN flags f ON f.flag_key=s.flag_key ORDER BY s.submitted_at DESC')->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Instructor | ICSS</title><link rel="stylesheet" href="/assets/style.css"></head><body><header class="top"><a class="brand" href="/"><span class="mark">IC</span><div><b>ICSS INSTRUCTOR</b><small>STUDENT MANAGEMENT</small></div></a><a href="/dashboard.php">Dashboard</a></header><main class="wrap page"><?php show_flash(); ?><p class="eyebrow">PRIVATE INSTRUCTOR VIEW</p><h1>Student registrations</h1><?php if ($canManage): ?><p class="admin-actions"><a class="button primary" href="/admin-products.php">Manage shop products</a></p><?php endif; ?><p class="muted"><?= $canManage ? 'Super admin mode: account and shop changes are enabled.' : 'Read-only administrator mode: you can inspect user records but cannot change data.' ?></p><p class="muted">Select any username to view that account’s profile, wallet, challenge submissions, and order history. Passwords and password hashes are never displayed.</p><table class="scores"><thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Wallet</th><th>Points</th><th>Flags</th><th>Registered</th><th>Admin actions</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td><?=(int)$u['id']?></td><td><a href="/admin-user.php?id=<?=(int)$u['id']?>"><?=h($u['username'])?></a></td><td><?=h($u['email'])?></td><td><?=h($u['role'])?></td><td><?= $u['role']==='super_admin' ? 'Unlimited' : '₹'.number_format((float)$u['wallet_balance'],2) ?></td><td><?=(int)$u['score']?></td><td><?=(int)$u['submitted_count']?></td><td><?=h($u['created_at'])?></td><td class="admin-actions"><a class="button primary" href="/admin-user.php?id=<?=(int)$u['id']?>">Access user data</a><?php if ($canManage && $u['role']==='student'): ?> <a class="button" href="/admin.php?topup_id=<?=(int)$u['id']?>#topup">Add money</a><?php endif; ?><?php if ($canManage && (int)$u['id'] !== (int)($_SESSION['user_id'] ?? 0) && $u['role'] !== 'super_admin'): ?><form method="post" style="display:inline" onsubmit="return confirm('Permanently delete <?=h(addslashes($u['username']))?>? Their orders and challenge submissions will also be deleted. This cannot be undone.');"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?=(int)$u['id']?>"><button class="button" type="submit" style="border-color:#c43d4b;color:#a51f2d">Delete user</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table><?php if ($canManage): ?><h2 id="topup">Add student wallet balance</h2><p class="muted">Choose a student account and add classroom wallet credits. Super admin has unlimited purchasing.</p><form method="post" class="wallet-topup-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><label for="user_id">Student account</label><select id="user_id" name="user_id" required><option value="">Select student</option><?php foreach($users as $u): if ($u['role'] !== 'student') continue; ?><option value="<?=(int)$u['id']?>" <?= $selectedTopup === (int)$u['id'] ? 'selected' : '' ?>><?=h($u['username'])?> — <?=h($u['email'])?> (₹<?=number_format((float)$u['wallet_balance'],2)?>)</option><?php endforeach; ?></select><label for="amount">Amount (₹)</label><input id="amount" name="amount" type="number" min="0.01" max="1000000" step="0.01" required><button class="button primary" type="submit">Add wallet balance</button></form><?php endif; ?><h2>Recent flag submissions</h2><table class="scores"><thead><tr><th>Student</th><th>Lab</th><th>Points</th><th>Submitted</th></tr></thead><tbody><?php foreach($submissionDetails as $d): ?><tr><td><?=h($d['username'])?></td><td><?=h($d['title'])?></td><td><?=(int)$d['points']?></td><td><?=h($d['submitted_at'])?></td></tr><?php endforeach; ?></tbody></table></main><footer>ICSS · Personal data restricted to instructors</footer></body></html>
