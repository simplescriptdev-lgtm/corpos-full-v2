<?php
require __DIR__ . '/db.php'; require __DIR__ . '/auth.php';
try{ db()->query('SELECT 1'); }catch(Throwable $e){ header('Location: /corpos-full-v2/install.php'); exit; }
if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(16));
$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']??''); $password=$_POST['password']??''; $token=$_POST['csrf_token']??'';
  if(!hash_equals($_SESSION['csrf_token'],$token)) $error='Невірний CSRF токен';
  else { $u=find_user_by_email($email); if($u && password_verify($password,$u['password_hash'])){ login_user($u); header('Location: /corpos-full-v2/dashboard.php'); exit; } else $error='Невірний email або пароль'; }
}
?><!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>CorpOS — Вхід</title><link rel="stylesheet" href="/corpos-full-v2/public/styles.css"></head><body class="center">
<div class="card" style="min-width:340px"><h1>CorpOS</h1><p class="subtitle">Вхід для керівника</p>
<?php if(is_authenticated()): ?><div class="box success">Ви увійшли як <b><?= htmlspecialchars(current_user()['name']) ?></b></div><div class="actions"><a class="btn primary" href="/corpos-full-v2/dashboard.php">Перейти в кабінет</a><form method="post" action="/corpos-full-v2/logout.php"><button class="btn">Вийти</button></form></div>
<?php else: ?><?php if($error): ?><div class="box error"><?= htmlspecialchars($error) ?></div><?php endif; ?><form class="form" method="post">
<label>Email</label><input type="email" name="email" value="admin@corp.local" required>
<label>Пароль</label><input type="password" name="password" value="admin12345" required>
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<button class="btn primary">Увійти</button></form><?php endif; ?></div></body></html>
