<?php

namespace PQSDK;

ini_set('date.timezone', 'UTC');

class Token {
  public static $access_token = null;
  public static $expiration = null;

  public static function get() {
    $res = RestLayer::get(
      'v1/token',
      array(),
      array('Authentication' => "Key " . Settings::$appSecret)
    );
    
    if ($res[0] == 200) {
      $body = json_decode($res[2], true);
      $expiration = date("U", strtotime($body['expired_at']));

      self::$access_token = $body['token'];
      self::$expiration = $expiration;
    }

    return self::$access_token;
  }

  public static function accessToken() {
    if (self::$access_token == null || self::$expiration <= time())
      return self::get();
    else
      return self::$access_token;
  }

  public static function reset() {
    self::$access_token = null;
    self::$expiration = null;
  }
}
