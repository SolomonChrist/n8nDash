<?php
namespace Controllers;
use Utils\\Response;
use Services\\N8NClient;
use Models\\Job;
use Models\\JobEvent;
use Models\\Widget;
class WidgetController {
  private static function requireAuth(){
    session_start();
    if (!($_SESSION['uid'] ?? null)) { Response::json(['ok'=>false,'error'=>'auth'], 401); }
    return (int)$_SESSION['uid'];
  }
  public static function run($id){
    $uid = self::requireAuth();
    $widget = (new Widget())->get((int)$id);
    if (!$widget) Response::json(['ok'=>false,'error'=>'widget_not_found'],404);
    $cfg = json_decode($widget['config_json'] ?? "{}", true);
    $url = $cfg['webhook_url'] ?? null;
    if (!$url) Response::json(['ok'=>false,'error'=>'no_webhook_url'],400);
    $jobId = (new Job())->create((int)$id, $uid);
    $secret = (require __DIR__.'/../../config/config.php')['security']['status_webhook_secret'];
    $payload = [
      'job_id' => $jobId,
      'params' => $_POST ?: (json_decode(file_get_contents('php://input'), true) ?: []),
      'callback' => [
        'status' => (self::baseUrl().'/api/jobs/update'),
        'complete' => (self::baseUrl().'/api/jobs/complete'),
      ],
    ];
    $raw = json_encode($payload);
    $sig = hash_hmac('sha256',$raw,$secret);
    $resp = N8NClient::triggerWebhook($url, $payload, ["X-N8N-Signature: $sig"]);
    Response::json(['ok'=>true,'job_id'=>$jobId,'n8n'=>$resp]);
  }
  private static function baseUrl(): string {
    $cfg = require __DIR__.'/../../config/config.php';
    if (!empty($cfg['app']['base_url'])) return rtrim($cfg['app']['base_url'],'/');
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $proto.'://'.$host;
  }
}
