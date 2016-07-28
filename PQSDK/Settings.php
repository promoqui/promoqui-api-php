<?php

namespace PQSDK;

class Settings {
  public static $country;
  public static $schema;
  public static $host;
  public static $appSecret;

  public function __construct(){
    $this->setSchema("https");
  }

  public static function setSchema($schema) {
    self::$schema = $schema;
  }

  public static function getSchema() {
    return self::$schema;
  }

  public static function setHost($host) {
    self::$host = $host;
  }

  public static function getHost() {
    return self::$host;
  }

  public static function setAppSecret($app_secret) {
    self::$appSecret = $appSecret;
  }

  public static function getAppSecret() {
    return self::$appSecret;
  }
}
