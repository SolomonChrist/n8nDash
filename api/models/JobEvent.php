<?php
namespace Models;
use PDO;
class JobEvent extends BaseModel {
  public function add(int $jobId, ?string $step, ?int $progress, ?string $message, ?array $payload=null){
    $st = $this->pdo->prepare('INSERT INTO job_events(job_id,step,progress,message,payload_json) VALUES(?,?,?,?,?)');
    $st->execute([$jobId,$step,$progress,$message,$payload?json_encode($payload):null]);
  }
  public function forJob(int $jobId){
    $st = $this->pdo->prepare('SELECT * FROM job_events WHERE job_id=? ORDER BY id ASC'); $st->execute([$jobId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }
}
