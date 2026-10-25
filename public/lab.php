<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_login();
$id=(string)($_GET['id']??'');
$allowed=['access','crypto','injection','design','misconfig','outdated','auth','integrity','logging','ssrf'];
if(!in_array($id,$allowed,true)){http_response_code(404);exit('Page not found');}
$brands=['access'=>'Northstar Customer','crypto'=>'Northstar Profile','injection'=>'Northstar Store','design'=>'Northstar Checkout','misconfig'=>'Northstar Service Center','outdated'=>'Northstar Platform','auth'=>'Northstar Account','integrity'=>'Northstar Learning','logging'=>'Northstar Support','ssrf'=>'Northstar Link Preview'];
$titles=['access'=>'My orders','crypto'=>'Profile settings','injection'=>'Search products','design'=>'Checkout','misconfig'=>'Service center','outdated'=>'About this service','auth'=>'Account recovery','integrity'=>'Course administration','logging'=>'Help center','ssrf'=>'Link preview'];
$notice=''; $flagFound=false; $content='';
function lab_flag(string $id): string {
  $s=db()->prepare('SELECT flag_value FROM flags WHERE flag_key=?'); $s->execute([$id]); $r=$s->fetch();
  return $r ? (string)$r['flag_value'] : '';
}
function lab_reveal(string $id): void {
  $f=lab_flag($id);
  if($f!=='') echo '<div class="notice" style="margin-top:18px;border:1px solid #75c6a8;background:#eaf8f2;color:#123c30"><strong>Request completed.</strong><p style="margin:6px 0 0">'.h($f).'</p></div>';
}
if($id==='access'){
  $orders=[1001=>['date'=>'24 Oct 2026','status'=>'Delivered','item'=>'Network Lab Kit','total'=>'2499.00','owner'=>'student_alex'],1002=>['date'=>'22 Oct 2026','status'=>'Processing','item'=>'Encrypted Storage Drive','total'=>'1899.00','owner'=>'student_blair']];
  $order=(int)($_GET['order']??0);
  if($order && isset($orders[$order])) { $o=$orders[$order]; $content='<section class="product"><p class="eyebrow">ORDER #'.(int)$order.'</p><h2>Order details</h2><p>Placed '.$o['date'].' · '.$o['status'].'</p><hr><p><b>'.h($o['item']).'</b></p><p>Customer: '.h($o['owner']).'</p><p>Total: ₹'.h($o['total']).'</p><a class="button ghost" href="/lab.php?id=access">Back to orders</a></section>'; if($order===1002) $flagFound=true; }
  else { $content='<section class="product"><h2>Recent orders</h2><p>Review the status of your recent purchases.</p><table style="width:100%;text-align:left"><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th><th></th></tr><tr><td>#1001</td><td>24 Oct 2026</td><td>Delivered</td><td>₹2,499.00</td><td><a href="/lab.php?id=access&order=1001">View</a></td></tr></table></section>'; }
}
elseif($id==='crypto'){
 $field=(string)($_GET['field']??'');
 if($field==='token'){$content='<section class="product"><h2>Profile response</h2><pre>{"user":"lab_student","api_token":"LAB-DEMO-PLAINTEXT-7f31","storage":"plaintext"}</pre></section>';$flagFound=true;}
 else {$content='<section class="product"><h2>Personal information</h2><p>Manage the information associated with your account.</p><dl><dt>Name</dt><dd>Lab Student</dd><dt>Email</dt><dd>student@example.test</dd><dt>Preferred language</dt><dd>English</dd></dl><p><a href="/lab.php?id=crypto&field=name">Name</a> · <a href="/lab.php?id=crypto&field=email">Email</a></p></section>';}
}
elseif($id==='injection'){
 $q=(string)($_GET['q']??''); $catalog=[['SecureKey USB','Security'],['Privacy Webcam Cover','Accessories'],['Network Lab Kit','Learning']];
 if($q!=='' && (preg_match('/\bOR\b\s*[\'"0-9]+\s*=\s*[\'"0-9]+/i',$q)||stripos($q,"' OR '")!==false||stripos($q,'1=1')!==false)){ $rows=$catalog;$flagFound=true; }
 else {$rows=array_values(array_filter($catalog,fn($p)=>$q===''||stripos($p[0],$q)!==false));}
 $content='<section class="product"><form method="get" action="/lab.php"><input type="hidden" name="id" value="injection"><label>Search</label><input name="q" value="'.h($q).'" placeholder="What are you looking for?"><button class="button primary">Search</button></form><h2>Search results</h2><div class="product-grid">';
 foreach($rows as $p)$content.='<article class="product"><span class="category">'.h($p[1]).'</span><h3>'.h($p[0]).'</h3><p>Available now</p></article>';
 $content.='</div></section>';
}
elseif($id==='design'){
 $price=2499.00;
 if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$submitted=(float)($_POST['unit_price']??$price);$qty=max(1,min(5,(int)($_POST['quantity']??1)));$total=$submitted*$qty;$content='<section class="product"><h2>Order confirmation</h2><p>Item: Network Lab Kit</p><p>Quantity: '.(int)$qty.'</p><p>Unit price: ₹'.number_format($submitted,2).'</p><h3>Total: ₹'.number_format($total,2).'</h3><p>Your demo order has been placed.</p></section>'; if(abs($submitted-$price)>0.01)$flagFound=true;}
 else {$content='<section class="product"><h2>Your basket</h2><div class="cart-line"><div><b>Network Lab Kit</b><p>Digital learning equipment</p></div><strong>₹2,499.00</strong></div><form method="post" action="/lab.php?id=design"><input type="hidden" name="csrf" value="'.h(csrf_token()).'"><label>Quantity</label><select name="quantity"><option>1</option><option>2</option></select><input type="hidden" name="unit_price" value="2499.00"><div class="cart-line"><span>Subtotal</span><b>₹2,499.00</b></div><button class="button primary">Place demo order</button></form></section>';}
}
elseif($id==='misconfig'){
 $route=(string)($_GET['page']??'');
 if(in_array($route,['/diagnostics','diagnostics','/debug','debug'],true)){$content='<section class="product"><h2>Service diagnostics</h2><pre>APP_ENV=staging
DB_HOST=db
DB_NAME=icss_ctf
INTERNAL_BUILD=2021.11-lab
/var/www/html/src/legacy-router.php:42</pre></section>';$flagFound=true;}
 else {$content='<section class="product"><h2>Support resources</h2><p>Find help with account access, orders and service status.</p><ul><li><a href="/lab.php?id=misconfig&page=status">Service status</a></li><li><a href="/lab.php?id=misconfig&page=help">Help articles</a></li></ul></section>';}
}
elseif($id==='outdated'){
 $content='<section class="product"><h2>Technology</h2><p>Our customer portal uses the following front-end package.</p><p><b>LegacyUI</b> <span class="pill">Version 1.2.0</span></p><p>For support enquiries, contact the service team.</p></section>';
 if(isset($_GET['component']) && $_GET['component']==='LegacyUI')$flagFound=true;
}
elseif($id==='auth'){
 $content='<section class="product"><h2>Forgot your password?</h2><p>Enter your username to recover access to your account.</p><form method="post" action="/lab.php?id=auth"><input type="hidden" name="csrf" value="'.h(csrf_token()).'"><label>Username</label><input name="username" required><button class="button primary">Continue</button></form>';
 if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$u=strtolower(trim((string)($_POST['username']??'')));$exists=in_array($u,['alex','blair','instructor'],true);$content.='<p class="notice">'.($exists?'An account was found. Recovery instructions are ready.':'No account was found for this username.').'</p>';if($exists)$flagFound=true;}
 $content.='</section>';
}
elseif($id==='integrity'){
 $content='<section class="product"><h2>Course import</h2><p>Import course metadata for review.</p><form method="post" action="/lab.php?id=integrity"><input type="hidden" name="csrf" value="'.h(csrf_token()).'"><label>Course metadata</label><textarea name="payload" rows="4">{"course":"WAPT","approved":false}</textarea><button class="button primary">Import course</button></form>';
 if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$raw=(string)($_POST['payload']??'');$data=json_decode($raw,true);$accepted=is_array($data)&&(($data['approved']??false)===true);$content.='<p class="notice">'.($accepted?'Import accepted. Course is approved.':'Import received for review.').'</p>';if($accepted)$flagFound=true;}
 $content.='</section>';
}
elseif($id==='logging'){
 if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$_SESSION['lab_denied_attempts']=(int)($_SESSION['lab_denied_attempts']??0)+1;$n=$_SESSION['lab_denied_attempts'];$content='<section class="product"><h2>Request denied</h2><p>Your request could not be completed. Reference: none.</p><a href="/lab.php?id=logging">Return to support</a></section>';if($n>=3)$flagFound=true;}
 else $content='<section class="product"><h2>Help center</h2><p>Need assistance with your account?</p><form method="post" action="/lab.php?id=logging"><input type="hidden" name="csrf" value="'.h(csrf_token()).'"><input type="hidden" name="action" value="restricted"><button class="button ghost">Open account administration</button></form></section>';
}
elseif($id==='ssrf'){
 $url=(string)($_GET['url']??'');
 if($url==='http://lab-internal/metadata'){$content='<section class="product"><h2>Preview</h2><pre>instance-role=demo-role</pre></section>';$flagFound=true;}
 elseif($url!==''){$content='<section class="product"><h2>Preview</h2><p>Preview unavailable for this destination.</p></section>';}
 else {$content='<section class="product"><h2>Share a link</h2><form method="get" action="/lab.php"><input type="hidden" name="id" value="ssrf"><label>URL</label><input name="url" placeholder="https://example.test/article"><button class="button primary">Preview link</button></form></section>';}
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($titles[$id])?> | Northstar</title><link rel="stylesheet" href="/assets/style.css"><style>
body{background:#f6f7f9;color:#202b3c}.site-head{background:#fff;border-bottom:1px solid #e2e7ee;padding:18px max(24px,calc((100% - 1120px)/2));display:flex;justify-content:space-between;align-items:center}.site-head a{color:#26374c;text-decoration:none;margin-left:22px}.site-brand{font-weight:800;letter-spacing:.08em}.site-main{max-width:1120px;margin:36px auto;padding:0 24px}.site-main h1{font-size:34px;margin:8px 0 24px}.site-sub{color:#657387}.site-panel{background:white;border:1px solid #e1e6ed;border-radius:12px;padding:28px;margin-bottom:20px}.site-panel h2{margin-top:0}.site-panel input,.site-panel select,.site-panel textarea{width:100%;margin:8px 0 16px;padding:13px;border:1px solid #cbd4e0;border-radius:7px;box-sizing:border-box}.site-panel table td,.site-panel table th{padding:12px;border-bottom:1px solid #edf0f4}.site-panel a{color:#1557b8}.site-panel pre{white-space:pre-wrap;background:#f4f6f9;padding:18px;border-radius:8px}.site-foot{max-width:1120px;margin:30px auto;color:#7a8798;padding:0 24px;font-size:13px}
</style></head><body><header class="site-head"><a class="site-brand" href="/shop.php" style="margin:0">NORTHSTAR</a><nav><a href="/shop.php">Store</a><a href="/dashboard.php">My account</a><a href="/cart.php">Basket</a></nav></header><main class="site-main"><p class="site-sub">CUSTOMER SERVICES / <?=h(strtoupper($id))?></p><h1><?=h($titles[$id])?></h1><div class="site-panel"><?=$content?><?php if($flagFound)lab_reveal($id); ?></div><p class="site-sub">Northstar Services · Customer support · Privacy · Terms</p></main><footer class="site-foot">Northstar is a fictional storefront used for authorized ICSS classroom training. No real purchases are processed.</footer></body></html>
