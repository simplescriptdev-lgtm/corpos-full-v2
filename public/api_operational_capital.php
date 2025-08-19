<?php
require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
header('Content-Type: application/json; charset=utf-8');

if(!is_authenticated()){
  http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit;
}
$action=$_POST['action']??$_GET['action']??'summary';
$token=$_POST['csrf_token']??$_GET['csrf_token']??'';
if(!hash_equals($_SESSION['csrf_token']??'', $token)){
  http_response_code(400); echo json_encode(['error'=>'Bad CSRF']); exit;
}

function ensure_oper_withdrawals_table(){
  $sql = 'CREATE TABLE IF NOT EXISTS operational_capital_withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(12,2) NOT NULL,
    comment TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
  db()->exec($sql);
}

try{
  if($action==='create_withdrawal'){
    ensure_oper_withdrawals_table();
    $amount=(float)($_POST['amount']??0);
    $comment=trim($_POST['comment']??'');
    if($amount<=0) throw new Exception('Невірна сума');
    $st=db()->prepare('INSERT INTO operational_capital_withdrawals (amount,comment) VALUES (?,?)');
    $st->execute([$amount,$comment]);
    echo json_encode(['ok'=>true]); exit;
  }
  if($action==='delete_withdrawal'){
    ensure_oper_withdrawals_table();
    $id=(int)($_POST['id']??0);
    if($id<=0) throw new Exception('Невірний id');
    $st=db()->prepare('DELETE FROM operational_capital_withdrawals WHERE id=?');
    $st->execute([$id]);
    echo json_encode(['ok'=>true]); exit;
  }

  // Summary
  ensure_oper_withdrawals_table();
  // total owner incomes
  $sumOwner=(float)db()->query('SELECT COALESCE(SUM(amount),0) s FROM owner_incomes')->fetch()['s'];
  $operational = round($sumOwner*0.06, 2); // 6% «Операційна діяльність» з розділу Рух капіталу
  $fromBanks=0.0; $fromShmat=0.0; $fromStatutory=0.0; $fromProfit=0.0; $fromProjects=0.0;
  // На даному етапі узгоджено тільки джерело «Операційна діяльність» -> загальне надходження (operational)
  $withdrawn=(float)db()->query('SELECT COALESCE(SUM(amount),0) s FROM operational_capital_withdrawals')->fetch()['s'];
  $balance = $operational - $withdrawn;

  $hist=db()->query('SELECT * FROM operational_capital_withdrawals ORDER BY id DESC')->fetchAll();

  echo json_encode(['ok'=>true, 'sources'=>[
      ['name'=>'Надходження капіталу від інвестицій банків','amount'=>$fromBanks],
      ['name'=>'Надходження капіталу від інвестицій SHMAT BANK','amount'=>$fromShmat],
      ['name'=>'Надходження капіталу від інвестицій статутного капіталу','amount'=>$fromStatutory],
      ['name'=>'Надходження капіталу від прибутку корпорації','amount'=>$fromProfit],
      ['name'=>'Надходження капіталу від проектів корпорації','amount'=>$fromProjects],
    ],
    'summary'=>['total_in'=>$operational,'withdrawn'=>$withdrawn,'balance'=>$balance],
    'history'=>$hist
  ]);

}catch(Throwable $e){
  http_response_code(500); echo json_encode(['error'=>$e->getMessage()]);
}
