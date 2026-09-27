<?php
// Copy this file to config.php and change it for your own machine.
// config.php is git-ignored, so each of us keeps our own database password.
define('BASE_URL', '/ccwlcs');
define('UPLOAD_DIR', __DIR__ . '/../uploads/radiology/');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);

define('DB_HOST', 'localhost');
define('DB_NAME', 'ccwlcs');
define('DB_USER', 'root');
define('DB_PASS', '');   // XAMPP's default is an empty password
