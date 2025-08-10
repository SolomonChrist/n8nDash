<?php
namespace Controllers;
use Utils\\Response;
use Models\\User;
class AuthController {
  public static function login(){
    session_start();
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $u = trim($body['username'] ?? '');
    $p = $body['password'] ?? '';
    $user = (new User())->verify($u,$p);
    if (!$user) Response::json(['ok'=>false,'error'=>'Invalid credentials'], 401);
    $_SESSION['uid'] = (int)$user['id'];
    $_SESSION['role'] = $user['role'];
    Response::json(['ok'=>true]);
  }
  public static function logout(){
    session_start(); session_destroy(); Response::json(['ok'=>true]);
  }
  public static function me(){
    session_start();
    if (!($_SESSION['uid'] ?? null)) Response::json(['ok'=>false,'user'=>null]);
    Response::json(['ok'=>true,'user'=>['id'=>$_SESSION['uid'],'role'=>$_SESSION['role']]]);
  }
}
