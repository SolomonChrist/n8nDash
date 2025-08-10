<?php
namespace Models;
use PDO;
class Dashboard extends BaseModel {
  public function allForUser(int $userId){
    $st = $this->pdo->prepare('SELECT d.* FROM dashboards d JOIN dashboard_users du ON du.dashboard_id=d.id WHERE du.user_id=? ORDER BY d.created_at DESC');
    $st->execute([$userId]); return $st->fetchAll(PDO::FETCH_ASSOC);
  }
  public function findBySlug(string $slug){
    $st = $this->pdo->prepare('SELECT * FROM dashboards WHERE slug=?'); $st->execute([$slug]);
    return $st->fetch(PDO::FETCH_ASSOC);
  }
}
