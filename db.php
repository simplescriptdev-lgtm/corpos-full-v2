<?php
$config = require __DIR__ . '/config.php';
function db(): PDO {
  static $pdo=null;
  if($pdo===null){
    $cfg=require __DIR__ . '/config.php';
    $dsn=sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',$cfg['db']['host'],$cfg['db']['port'],$cfg['db']['database'],$cfg['db']['charset']);
    $pdo=new PDO($dsn,$cfg['db']['username'],$cfg['db']['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
  }
  return $pdo;
}
function find_user_by_email(string $email){ $s=db()->prepare('SELECT * FROM users WHERE email=? LIMIT 1'); $s->execute([$email]); return $s->fetch(); }
