<?php
namespace Services;
class N8NClient {
  public static function triggerWebhook(string $url, array $payload, array $headers=[]): array {
    $ch = curl_init($url);
    curl_setopt_array($ch,[
      CURLOPT_RETURNTRANSFER=>true,
      CURLOPT_POST=>true,
      CURLOPT_HTTPHEADER=>array_merge(['Content-Type: application/json'],$headers),
      CURLOPT_POSTFIELDS=>json_encode($payload),
      CURLOPT_TIMEOUT=>30,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code'=>$code,'body'=>$res,'error'=>$err];
  }
}
