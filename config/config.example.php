<?php
return [
  'db' => [
    'host' => 'localhost',
    'user' => 'n8ndash',
    'pass' => 'secret',
    'name' => 'n8ndash',
    'charset' => 'utf8mb4',
  ],
  'security' => [
    'session_name' => 'n8dash_sess',
    'csrf' => true,
    'password_algo' => PASSWORD_BCRYPT,
    'status_webhook_secret' => 'CHANGE_ME_TO_A_LONG_RANDOM_STRING',
    'rate_limit_per_minute' => 30
  ],
  'n8n' => [
    'base_url' => null,
    'api_key' => null,
    'max_concurrent_views' => 10
  ],
  'app' => [
    'base_url' => null,
    'env' => 'prod'
  ]
];
