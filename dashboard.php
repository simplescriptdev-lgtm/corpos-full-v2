<?php
require __DIR__ . '/auth.php';
if(!is_authenticated()){ header('Location: /corpos-full-v2/index.php'); exit; }
$u=current_user(); if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(16));
?><!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Кабінет — CorpOS</title>
<link rel="stylesheet" href="/corpos-full-v2/public/styles.css"><script>window.__csrf="<?= htmlspecialchars($_SESSION['csrf_token']) ?>";</script><script defer src="/corpos-full-v2/public/app.js"></script></head>
<body><div class="layout"><aside class="sidebar"><div class="brand">CorpOS</div><div class="userbox"><div class="uname"><?= htmlspecialchars($u['name']) ?></div><div class="urole"><?= htmlspecialchars($u['role']) ?></div></div>
<nav class="menu"><div class="group"><div class="ghead">Корпорація</div>
<a href="#corp/money_flow" class="item" data-view>Рух капіталу</a><a href="#corp/owner_capital" class="item" data-view>Капітал власника</a><a href="#corp/operational_capital" class="item" data-view>Операційний капітал</a>Операційний капітал</a></div>
<div class="group"><div class="ghead">Інше</div><a class="item">Благодійний фонд</a><a class="item">Страховий фонд</a><a class="item">Фонд облігацій</a><a class="item">Біржовий фонд</a><a class="item">Бізнес</a></div></nav>
<div class="sidebar-actions"><form method="post" action="/corpos-full-v2/logout.php"><button class="btn">Вийти</button></form></div></aside>
<main class="content"><div id="content-root" class="content-root"><div class="card placeholder"><h2>Вітаю у кабінеті</h2><p>Оберіть розділ зліва.</p></div></div></main></div></body></html>
