<?php
namespace Services;
class Security {
  public static function hmacVerify(string $rawBody, string $header, string $secret): bool {
    $expect = hash_hmac('sha256', $rawBody, $secret);
    return hash_equals($expect, $header);
  }
  public static function csrfToken(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
  }
  public static function requireCsrf(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
      if (session_status() !== PHP_SESSION_ACTIVE) session_start();
      $t = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF'] ?? '';
      if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(403); echo 'CSRF failed'; exit;
      }
    }
  }
}
