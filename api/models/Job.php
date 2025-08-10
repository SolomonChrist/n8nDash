<?php
namespace Models;
use PDO;
class Job extends BaseModel {
  public function create(int $widgetId, ?int $userId): int {
    $st = $this->pdo->prepare('INSERT INTO jobs(widget_id,user_id,status) VALUES(?,?,"running")');
    $st->execute([$widgetId,$userId]);
    return (int)$this->pdo->lastInsertId();
  }
  public function updateStatus(int $jobId, string $status, ?string $step=null, ?int $progress=null){
    $st = $this->pdo->prepare('UPDATE jobs SET status=?, current_step=?, progress=? WHERE id=?');
    $st->execute([$status,$step,$progress,$jobId]);
  }
  public function setResult(int $jobId, array $result){
    $st = $this->pdo->prepare('UPDATE jobs SET result_json=?, status="completed" WHERE id=?');
    $st->execute([json_encode($result),$jobId]);
  }
  public function get(int $id){
    $st = $this->pdo->prepare('SELECT * FROM jobs WHERE id=?'); $st->execute([$id]); return $st->fetch(PDO::FETCH_ASSOC);
  }
}
