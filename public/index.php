<?php
declare(strict_types=1);
session_start();

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
if (strpos($path, '/api/') === 0) {
  require __DIR__ . '/../api/Router.php'; exit;
}

function view($name, $vars=[]){
  extract($vars);
  include __DIR__.'/views/layout.php';
}

$page = 'dashboards-list';
$slug = null;
if ($path === '/' || $path === '/dashboards') { $page = 'dashboards-list'; }
elseif ($path === '/login') { $page = 'login'; }
elseif (preg_match('#^/dashboard/([a-z0-9-]+)$#',$path,$m)) { $page = 'dashboard'; $slug = $m[1]; }
elseif (preg_match('#^/editor/([a-z0-9-]+)$#',$path,$m)) { $page = 'editor'; $slug = $m[1]; }
elseif (preg_match('#^/onboarding/([a-z0-9-]+)$#',$path,$m)) { $page = 'onboarding'; $slug = $m[1]; }

view($page,['slug'=>$slug,'page'=>$page]);
