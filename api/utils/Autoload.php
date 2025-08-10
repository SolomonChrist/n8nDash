<?php
spl_autoload_register(function($class){
  $prefixes = [
    'Utils' => __DIR__,
    'Services' => __DIR__ . '/../services',
    'Models' => __DIR__ . '/../models',
    'Controllers' => __DIR__ . '/../controllers',
  ];
  foreach ($prefixes as $ns => $base) {
    if (strpos($class, $ns.'\\') === 0) {
      $rel = substr($class, strlen($ns)+1);
      $file = $base . '/' . str_replace('\\','/',$rel) . '.php';
      if (file_exists($file)) require $file;
    }
  }
});
