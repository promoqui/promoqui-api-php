<?php

namespace PQSDK;

use PQSDK\Network;
use PQSDK\Settings;

class RestLayer {
    public static function get($url, $params = array(), $headers = array()) {
        $url = Settings::$schema . "://" . Settings::$host . "/" . $url;

        //var_dump($headers);
        $res = Network::GET($url, $params, $headers);
        self::check_status($res[0], $res[2]);

        return $res;
    }

    public static function post($url, $params = array(), $headers = array()) {
        $url = Settings::$schema . "://" . Settings::$host . "/" . $url;

        //var_dump($headers);
        $res = Network::POST($url, $params, $headers, true);
        self::check_status($res[0], $res[2]);

        return $res;
    }

    public static function put($url, $params = array(), $headers = array()) {
        $url = Settings::$schema . "://" . Settings::$host . "/" . $url;
        $res = Network::PUT($url, $params, $headers, true);
        self::check_status($res[0], $res[2]);

        return $res;
    }

    public static function request($method, $endpoint, $fields, $headers){
        $res = null;
        switch($method){
            case "put":
                $res = self::put($endpoint,$fields,$headers);
                break;
            case "post":
                $res = self::post($endpoint,$fields,$headers);
                break;
            case "get":
                $res = self::get($endpoint,$fields,$headers);
                break;
        }
        return $res;
    }

    private static function check_status($code, $body) {
        if ($code >= 500)
            throw new \Exception("Internal Server Error");
        else if ($code == 400) {
            $error = json_decode($body, true)['error'];
            throw new \Exception("Bad Request. You have probably missed some attributes! Error: {$error}");
        }
        else if ($code == 401)
            throw new \Exception("You are not authorized to perform that request");
    }
}
