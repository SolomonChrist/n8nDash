<?php
// Database configuration
define('DB_HOST', 'localhost');     // Your database host
define('DB_USER', 'your_db_user');  // Your database username
define('DB_PASS', 'your_db_pass');  // Your database password
define('DB_NAME', 'n8ndash');       // Your database name

// Application configuration
define('APP_URL', 'http://your-domain.com/n8ndash'); // Your application URL
define('DEBUG_MODE', false);  // Set to true for development

// Session configuration
define('SESSION_LIFETIME', 86400); // 24 hours
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME); 