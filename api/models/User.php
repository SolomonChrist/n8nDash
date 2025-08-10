<?php
namespace Models;
use PDO;
class User extends BaseModel {
  public function findByUsername(string $u){
    $st = $this->pdo->prepare('SELECT * FROM users WHERE username = ? AND is_active=1');
    $st->execute([$u]); return $st->fetch(PDO::FETCH_ASSOC);
  }
  public function verify(string $u,string $p){
    $row = $this->findByUsername($u);
    if ($row && password_verify($p,$row['password_hash'])) return $row;
    return null;
  }
}
