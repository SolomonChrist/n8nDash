<?php
namespace Utils;
use PDO;
class DB {
  private static $pdo;
  public static function pdo(): PDO {
    if (!self::$pdo) {
      $cfg = require __DIR__.'/../../config/config.php';
      $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',$cfg['db']['host'],$cfg['db']['name'],$cfg['db']['charset']);
      self::$pdo = new PDO($dsn,$cfg['db']['user'],$cfg['db']['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    }
    return self::$pdo;
  }
}
