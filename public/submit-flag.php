<?php
require_once __DIR__ . '/../src/bootstrap.php'; require_login();
$expectedKey=trim((string)($_POST['flag_lab_key']??''));
if($_SERVER['REQUEST_METHOD']!=='POST'){if($expectedKey === 'design') { header('Location: /cart.php'); } elseif($expectedKey === 'design') { header('Location: /cart.php'); } elseif($expectedKey !== '' && str_starts_with($expectedKey, 'x_')) { header('Location: /extended-lab.php?id='.rawurlencode($expectedKey).'#submit-flag'); } else { header('Location: /dashboard.php'); } exit;}
csrf_check(); $value=trim((string)($_POST['flag']??''));
$st=db()->prepare('SELECT flag_key,points FROM flags WHERE flag_value=?');$st->execute([$value]);$flag=$st->fetch();
if($flag && $expectedKey!=='' && !hash_equals((string)$flag['flag_key'],$expectedKey)) { $flag=false; }
if(!$flag) flash('Incorrect flag. Review the challenge and try again.');
else {$st=db()->prepare('INSERT IGNORE INTO submissions(user_id,flag_key) VALUES(?,?)');$st->execute([$_SESSION['user_id'],$flag['flag_key']]);flash($st->rowCount()?'Correct flag! You earned '.(int)$flag['points'].' points.':'You have already submitted this flag.');}
if($expectedKey === 'design') { header('Location: /cart.php'); } elseif($expectedKey !== '' && str_starts_with($expectedKey, 'x_')) { header('Location: /extended-lab.php?id='.rawurlencode($expectedKey).'#submit-flag'); } else { header('Location: /dashboard.php'); } exit;
