<?php
require_once __DIR__ . '/../src/bootstrap.php'; require_login();
$modules=[
'access'=>['A01:2021 · Broken Access Control','Customer order services.','/lab.php?id=access'],
'crypto'=>['A02:2021 · Cryptographic Failures','Profile and account information.','/lab.php?id=crypto'],
'injection'=>['A03:2021 · Injection','Product catalogue and search.','/lab.php?id=injection'],
'design'=>['A04:2021 · Insecure Design','Store basket and checkout.','/lab.php?id=design'],
'misconfig'=>['A05:2021 · Security Misconfiguration','Customer service resources.','/lab.php?id=misconfig'],
'outdated'=>['A06:2021 · Vulnerable and Outdated Components','Platform information.','/lab.php?id=outdated'],
'auth'=>['A07:2021 · Identification and Authentication Failures','Account recovery.','/lab.php?id=auth'],
'integrity'=>['A08:2021 · Software and Data Integrity Failures','Course administration portal.','/lab.php?id=integrity'],
'logging'=>['A09:2021 · Security Logging and Monitoring Failures','Customer support.','/lab.php?id=logging'],
'ssrf'=>['A10:2021 · Server-Side Request Forgery (SSRF)','Link preview service.','/lab.php?id=ssrf']
];
$id=(string)($_GET['id']??''); if(!isset($modules[$id])){http_response_code(404);exit('Challenge not found');}
[$category,$description,$labUrl]=$modules[$id];
$st=db()->prepare('SELECT points FROM flags WHERE flag_key=?');$st->execute([$id]);$flag=$st->fetch();
$st=db()->prepare('SELECT id FROM submissions WHERE user_id=? AND flag_key=?');$st->execute([$_SESSION['user_id'],$id]);$done=(bool)$st->fetch();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($category)?> | ICSS</title><link rel="stylesheet" href="/assets/style.css"></head><body><header class="top"><a class="brand" href="/dashboard.php"><span class="mark">IC</span><div><b>ICSS SECURITY LAB</b><small>CHALLENGE</small></div></a><nav><a href="/dashboard.php">Dashboard</a><a href="/logout.php">Sign out</a></nav></header><main class="wrap page narrow"><p class="eyebrow"><?=h($category)?></p><h1><?=h(substr($category, strpos($category,'·')+2))?></h1><div class="notice"><b>Authorized lab:</b> Use only the provided local target. This scenario is fictional and uses no real customer records.</div><section class="challenge-detail"><h2>Scenario</h2><p><?=h($description)?></p><h2>Application</h2><p>Use the application as you normally would. Document any unexpected behavior you can demonstrate, along with its impact and a recommended fix. Testing is limited to this fictional lab.</p><a class="button primary" href="<?=h($labUrl)?>">Open vulnerable lab ↗</a><h2>Submit your flag</h2><p>Discover the flag through testing; it is not displayed on this page.</p><form method="post" action="/submit-flag.php" class="flag-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="challenge" value="<?=h($id)?>"><label for="flag">Flag</label><input id="flag" name="flag" placeholder="ICSS{...}" required autocomplete="off"><button class="button primary" type="submit">Submit flag</button></form><p><span class="pill"><?=$done?'COMPLETED':(int)$flag['points'].' POINTS'?></span></p><a class="button ghost" href="/dashboard.php">← Back to dashboard</a></section></main><footer>ICSS WAPT CTF · Educational use only</footer></body></html>
