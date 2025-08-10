<?php
declare(strict_types=1);
session_start();
require __DIR__.'/utils/Autoload.php';

use Utils\\Response;
use Controllers\\AuthController;
use Controllers\\DashboardController;
use Controllers\\WidgetController;
use Controllers\\JobController;

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

function match($method, $pattern, $callable){
  global $path;
  if ($_SERVER['REQUEST_METHOD'] !== $method) return;
  $regex = '#^'.$pattern.'$#';
  if (preg_match($regex, $path, $m)) {
    array_shift($m);
    call_user_func_array($callable, $m);
    exit;
  }
}

// Auth
match('POST','/api/login',[AuthController::class,'login']);
match('POST','/api/logout',[AuthController::class,'logout']);
match('GET','/api/me',[AuthController::class,'me']);

// Dashboards
match('GET','/api/dashboards',[DashboardController::class,'list']);
match('GET','/api/dashboards/([a-z0-9-]+)',[DashboardController::class,'detail']);

// Widgets
match('POST','/api/widgets/(\\d+)/run',[WidgetController::class,'run']);

// Jobs
match('POST','/api/jobs/update',[JobController::class,'update']);
match('POST','/api/jobs/complete',[JobController::class,'complete']);
match('GET','/api/jobs/(\\d+)',[JobController::class,'get']);
match('GET','/api/jobs/(\\d+)/stream',[JobController::class,'stream']);

// 404
Response::json(['ok'=>false,'error'=>'not_found','path'=>$path],404);
