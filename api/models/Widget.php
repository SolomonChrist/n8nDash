<?php
namespace Models;
use PDO;
class Widget extends BaseModel {
  public function forDashboard(int $dashId){
    $st = $this->pdo->prepare('SELECT * FROM widgets WHERE dashboard_id=? ORDER BY position_y, position_x'); $st->execute([$dashId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }
  public function get(int $id){
    $st = $this->pdo->prepare('SELECT * FROM widgets WHERE id=?'); $st->execute([$id]); return $st->fetch(PDO::FETCH_ASSOC);
  }
  public function saveOutput(int $id, array $data){
    $st = $this->pdo->prepare('UPDATE widgets SET last_output_json=? WHERE id=?');
    $st->execute([json_encode($data), $id]);
  }
}
