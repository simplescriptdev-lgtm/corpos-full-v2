<?php
require __DIR__ . '/../db.php'; require __DIR__ . '/../auth.php';
header('Content-Type: application/json; charset=utf-8'); if(!is_authenticated()){ http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }
$action=$_POST['action']??$_GET['action']??'summary'; $token=$_POST['csrf_token']??$_GET['csrf_token']??'';
if(!hash_equals($_SESSION['csrf_token']??'', $token)){ http_response_code(400); echo json_encode(['error'=>'Bad CSRF']); exit; }
try{
  if($action==='create_withdrawal'){ $amount=(float)($_POST['amount']??0); $comment=trim($_POST['comment']??''); if($amount<=0) throw new Exception('Невірна сума');
    $st=db()->prepare('INSERT INTO owner_capital_withdrawals (amount,comment) VALUES (?,?)'); $st->execute([$amount,$comment]); echo json_encode(['ok'=>true]); exit; }
  if($action==='update_withdrawal'){ $id=(int)($_POST['id']??0); $amount=(float)($_POST['amount']??0); $comment=trim($_POST['comment']??'');
    if($id<=0||$amount<=0) throw new Exception('Невірні дані');
    $st=db()->prepare('UPDATE owner_capital_withdrawals SET amount=?,comment=? WHERE id=?'); $st->execute([$amount,$comment,$id]); echo json_encode(['ok'=>true]); exit; }
  if($action==='delete_withdrawal'){ $id=(int)($_POST['id']??0); if($id<=0) throw new Exception('Невірний id');
    $st=db()->prepare('DELETE FROM owner_capital_withdrawals WHERE id=?'); $st->execute([$id]); echo json_encode(['ok'=>true]); exit; }
  $sumOwner=(float)db()->query('SELECT COALESCE(SUM(amount),0) s FROM owner_incomes')->fetch()['s']; $statutory=round($sumOwner*0.10,2);
  $fromBanks=0.0; $fromShmat=0.0; $fromProfit=0.0; $fromProjects=0.0;
  $totalIn=$statutory+$fromBanks+$fromShmat+$fromProfit+$fromProjects;
  $withdrawn=(float)db()->query('SELECT COALESCE(SUM(amount),0) s FROM owner_capital_withdrawals')->fetch()['s'];
  $balance=$totalIn-$withdrawn;
  $hist=db()->query('SELECT * FROM owner_capital_withdrawals ORDER BY id DESC')->fetchAll();
  echo json_encode(['ok'=>true,'sources'=>[
    ['name'=>'Надходження капіталу від інвестицій банків','amount'=>$fromBanks],
    ['name'=>'Надходження капіталу від інвестицій SHMAT BANK','amount'=>$fromShmat],
    ['name'=>'Надходження капіталу від інвестицій статутного капіталу','amount'=>$statutory],
    ['name'=>'Надходження капіталу від прибутку корпорації','amount'=>$fromProfit],
    ['name'=>'Надходження капіталу від проектів корпорації','amount'=>$fromProjects]
  ],'summary'=>['total_in'=>$totalIn,'withdrawn'=>$withdrawn,'balance'=>$balance],'history'=>$hist]);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
