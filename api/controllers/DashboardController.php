<?php
namespace Controllers;
use Utils\\Response;
use Models\\Dashboard;
use Models\\Widget;
class DashboardController {
  private static function requireAuth(){
    session_start();
    if (!($_SESSION['uid'] ?? null)) { Response::json(['ok'=>false,'error'=>'auth'], 401); }
    return (int)$_SESSION['uid'];
  }
  public static function list(){
    $uid = self::requireAuth();
    $rows = (new Dashboard())->allForUser($uid);
    Response::json(['ok'=>true,'dashboards'=>$rows]);
  }
  public static function detail($slug){
    self::requireAuth();
    $d = (new Dashboard())->findBySlug($slug);
    if (!$d) Response::json(['ok'=>false,'error'=>'not_found'],404);
    $widgets = (new Widget())->forDashboard((int)$d['id']);
    Response::json(['ok'=>true,'dashboard'=>$d,'widgets'=>$widgets]);
  }
}
