<?php
declare(strict_types=1);
session_start();
$cfgPath = __DIR__.'/../config/config.php';
$schemaPath = __DIR__.'/../sql/schema.sql';
if (!file_exists($cfgPath)) die('Create config/config.php first (copy from config.example.php).');
$cfg = require $cfgPath;
try {
  $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',$cfg['db']['host'],$cfg['db']['name'],$cfg['db']['charset']);
  $pdo = new PDO($dsn,$cfg['db']['user'],$cfg['db']['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  $sql = file_get_contents($schemaPath);
  $pdo->exec($sql);
  echo "<h2>Schema installed.</h2>";
  $exists = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
  if ((int)$exists === 0) {
    $stmt = $pdo->prepare("INSERT INTO users (username,email,password_hash,role) VALUES (?,?,?, 'admin')");
    $stmt->execute(['admin','admin@example.com',password_hash('password', PASSWORD_BCRYPT)]);
    echo '<p>Admin user created: <b>admin / password</b> — change after login.</p>';
  }
  echo '<p><a href="../public/">Go to app</a></p>';
} catch(Exception $e){
  echo '<pre>Error: '.htmlentities($e->getMessage()).'</pre>';
}
