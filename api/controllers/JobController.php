<?php
namespace Controllers;
use Utils\\Response;
use Services\\Security;
use Models\\Job;
use Models\\JobEvent;
use Models\\Widget;

class JobController {
  public static function update(){
    $raw = file_get_contents('php://input');
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $sig = $headers['X-N8N-Signature'] ?? $headers['x-n8n-signature'] ?? '';
    $secret = (require __DIR__.'/../../config/config.php')['security']['status_webhook_secret'];
    if (!Security::hmacVerify($raw,$sig,$secret)) { Response::json(['ok'=>false,'error'=>'hmac'],401); }
    $body = json_decode($raw,true) ?: [];
    $jobId = (int)($body['job_id'] ?? 0);
    if (!$jobId) Response::json(['ok'=>false,'error'=>'job_id'],400);
    (new JobEvent())->add($jobId, $body['step'] ?? null, $body['progress'] ?? null, $body['message'] ?? null, $body);
    (new Job())->updateStatus($jobId,'running',$body['step'] ?? null, $body['progress'] ?? null);
    Response::json(['ok'=>true]);
  }

  public static function complete(){
    $raw = file_get_contents('php://input');
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $sig = $headers['X-N8N-Signature'] ?? $headers['x-n8n-signature'] ?? '';
    $secret = (require __DIR__.'/../../config/config.php')['security']['status_webhook_secret'];
    if (!Security::hmacVerify($raw,$sig,$secret)) { Response::json(['ok'=>false,'error'=>'hmac'],401); }
    $body = json_decode($raw,true) ?: [];
    $jobId = (int)($body['job_id'] ?? 0);
    if (!$jobId) Response::json(['ok'=>false,'error'=>'job_id'],400);
    (new JobEvent())->add($jobId, 'Completed', 100, 'Done', $body['result'] ?? null);
    (new Job())->setResult($jobId, $body['result'] ?? []);
    if (!empty($body['widget_id'])) {
      (new Widget())->saveOutput((int)$body['widget_id'], $body['result'] ?? []);
    }
    Response::json(['ok'=>true]);
  }

  public static function get($id){
    $job = (new Job())->get((int)$id);
    if (!$job) Response::json(['ok'=>false,'error'=>'not_found'],404);
    $events = (new JobEvent())->forJob((int)$id);
    Response::json(['ok'=>true,'job'=>$job,'events'=>$events]);
  }

  public static function stream($id){
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    $ticks = 0;
    while ($ticks < 30) { // ~60s at 2s/tick
      $events = (new JobEvent())->forJob((int)$id);
      echo 'event: tick' . "\n";
      echo 'data: ' . json_encode($events) . "\n\n";
      if (function_exists('ob_flush')) @ob_flush();
      @flush();
      if (connection_aborted()) break;
      sleep(2);
      $ticks += 1;
    }
    exit;
  }
}
