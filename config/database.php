<?php
// Sri Lankan time for everything PHP prints or saves (XAMPP's default is Europe/Berlin).
date_default_timezone_set('Asia/Colombo');

// Single shared PDO connection.
class Db {
  private static $pdo = null;
  public static function get() {
    if (self::$pdo === null) {
      self::$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
      // make MySQL's NOW() / CURDATE() agree with PHP's date() (Sri Lanka has no daylight saving)
      self::$pdo->exec("SET time_zone = '+05:30'");
    }
    return self::$pdo;
  }
}