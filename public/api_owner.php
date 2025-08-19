<?php
require __DIR__ . '/../db.php'; require __DIR__ . '/../auth.php';
header('Content-Type: application/json; charset=utf-8'); if(!is_authenticated()){ http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }
$action=$_POST['action']??$_GET['action']??'list'; $token=$_POST['csrf_token']??$_GET['csrf_token']??'';
if(!hash_equals($_SESSION['csrf_token']??'', $token)){ http_response_code(400); echo json_encode(['error'=>'Bad CSRF']); exit; }
function split_parts($amt){ $p=['sf'=>.02,'it'=>.09,'sb'=>.05,'op'=>.06,'char'=>.04,'owner'=>.10,'invest'=>.64]; foreach($p as $k=>$v){ $p[$k]=round($amt*$v,2);} return $p; }
try{
  if($action==='create'){ $amount=(float)($_POST['amount']??0); $comment=trim($_POST['comment']??''); if($amount<=0) throw new Exception('Невірна сума');
    $st=db()->prepare('INSERT INTO owner_incomes (amount,comment) VALUES (?,?)'); $st->execute([$amount,$comment]);
    $id=(int)db()->lastInsertId(); $row=db()->query('SELECT * FROM owner_incomes WHERE id='.$id)->fetch();
    echo json_encode(['ok'=>true,'row'=>$row,'split'=>split_parts($row['amount'])]); exit; }
  if($action==='update'){ $id=(int)($_POST['id']??0); $amount=(float)($_POST['amount']??0); $comment=trim($_POST['comment']??'');
    if($id<=0||$amount<=0) throw new Exception('Невірні дані');
    $st=db()->prepare('UPDATE owner_incomes SET amount=?,comment=? WHERE id=?'); $st->execute([$amount,$comment,$id]);
    $row=db()->prepare('SELECT * FROM owner_incomes WHERE id=?'); $row->execute([$id]); $row=$row->fetch();
    echo json_encode(['ok'=>true,'row'=>$row,'split'=>split_parts($row['amount'])]); exit; }
  if($action==='delete'){ $id=(int)($_POST['id']??0); if($id<=0) throw new Exception('Невірний id');
    $st=db()->prepare('DELETE FROM owner_incomes WHERE id=?'); $st->execute([$id]); echo json_encode(['ok'=>true]); exit; }
  $rows=db()->query('SELECT * FROM owner_incomes ORDER BY id DESC')->fetchAll(); $grand=0; $items=[];
  foreach($rows as $r){ $grand+=(float)$r['amount']; $items[]=['row'=>$r,'split'=>split_parts($r['amount'])]; }
  $tot=split_parts($grand); echo json_encode(['ok'=>true,'items'=>$items,'totals'=>['total'=>$grand]+$tot]);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
