<?php

namespace PQSDK;
/* @var Network */
class Network {
    public static function GET($url, $params = array(), $customHeaders = array()) {
        $ch = self::initCurl($url, $params, $customHeaders);

        return self::doCurl($ch);
    }

    public static function HEAD($url, $params = array(), $customHeaders = array()) {
        $ch = self::initCurl($url, $params, $customHeaders);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        return self::doCurl($ch);
    }

    public static function POST($url, $params = array(), $customHeaders = array(), $asJson = false) {
        $ch = self::initCurl($url, array(), $customHeaders, $asJson);

        curl_setopt($ch, CURLOPT_POST, true);

        if ($asJson) {
            if (count($params) > 0)
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        } else {
            if (count($params) > 0)
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        return self::doCurl($ch);
    }

    public static function PUT($url, $params = array(), $customHeaders = array(), $asJson = false) {
        $ch = self::initCurl($url, array(), $customHeaders, $asJson);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

        if ($asJson) {
            if (count($params) > 0)
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        } else {
            if (count($params) > 0)
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        return self::doCurl($ch);
    }

    private static function initCurl($url, $params, $customHeaders, $asJson = false) {
        $ch = curl_init();
        $url = str_replace(' ', '%20', trim($url));

        if (count($params) > 0)
            $url = $url . "?" . http_build_query($params);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        //setting user-agent
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/43.0.2357.130 Chrome/43.0.2357.130 Safari/537.36');

        if ($asJson)
            $customHeaders["Content-Type"] = "application/json";

        if (count($customHeaders) > 0) {
            $headers_joined = array();
            foreach ($customHeaders as $key => $value)
                $headers_joined[] = "${key}: ${value}";

            //var_dump($headers_joined);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_joined);
        }

        return $ch;
    }

    private static function doCurl($ch) {
        $response = curl_exec($ch);

        // Extract response code, Headers and Body
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        curl_close($ch);

        $headers = explode("\r\n", $headers);
        $headers = array_slice($headers, 1, count($headers) - 3);

        $headers_hash = array();
        foreach ($headers as $header) {
            if (strpos($header, ':') !== false) {
                list($key, $value) = explode(":", $header);
                $headers_hash[trim($key)] = trim($value);
            }
        }

        return array($code, $headers_hash, trim($body));
    }
}
/* @var HtmlDocument */
class HtmlDocument {
    public $xpath;

    public function __construct($html) {
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        $this->xpath = new \DOMXpath($doc);
    }

    public function query($expression) {
        return new HtmlNodeList($this->xpath, $this->xpath->query($expression));
    }
}
/* @var HtmlNodeList */
class HtmlNodeList implements \ArrayAccess, \Iterator {
    public $xpath;
    public $nodes;

    public function __construct($xpath, $list) {
        $this->xpath = $xpath;
        $this->nodes = $list;
    }

    public function length() {
        return $this->nodes->length;
    }

    public function offsetExists($index) {
        return $index >= 0 and $index < $this->nodes->length;
    }

    public function offsetGet($index) {
        if ($this->offsetExists($index))
            return new HtmlNode($this->xpath, $this->nodes->item($index));

        throw new \Exception("Index out of bounds");
    }

    public function offsetSet($index, $value) {
        throw new \Exception("Can't set node on a query result");
    }

    public function offsetUnset($index) {
        throw new \Exception("Can't unset node on a query result");
    }

    private $_position = 0;

    public function rewind() {
        $this->_position = 0;
    }

    public function current() {
        return $this->offsetGet($this->_position);
    }

    public function key() {
        return $this->_position;
    }

    public function next() {
        return ++$this->_position;
    }

    public function valid() {
        return $this->offsetExists($this->_position);
    }
}
/* @var HtmlNode */
class HtmlNode implements \ArrayAccess {
    public $xpath;
    public $node;

    public function __construct($xpath, $node) {
        $this->xpath = $xpath;
        $this->node = $node;
    }

    public function query($expression) {
        return new HtmlNodeList($this->xpath, $this->xpath->query($expression, $this->node));
    }

    public function text() {
        return trim($this->node->nodeValue);
    }

    public function parent() {
        return new HtmlNode($this->xpath, $this->node->parentNode);
    }

    public function previous() {
        return new HtmlNode($this->xpath, $this->node->previousSibling);
    }

    public function next() {
        return new HtmlNode($this->xpath, $this->node->nextSibling);
    }

    public function offsetExists($name) {
        return $this->node->hasAttribute($name);
    }

    public function offsetGet($name) {
        if ($this->node->hasAttribute($name))
            return $this->node->getAttribute($name);

        return null;
    }

    public function offsetSet($index, $value) {
        throw new \Exception("Never set an attribute on a node");
    }

    public function offsetUnset($index) {
        throw new \Exception("Never unset an attribute on a node");
    }
}
/* @var JSON */
class JSON {
    public static function decode($string) {
        // The internal PHP json_decode has does not like the UTF8 BOM. Strip it if present.
        if (substr($string, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF))
            $string = substr($string, 3);

        return json_decode($string, true);
    }
}
/* @var ISSUU */
class ISSUU {
    public static function get_leaflet_data($url){
        $doc = new \DOMDocument();
        $page = Network::GET($url)[2];
        $doc->loadHTML($page);
        $xpath = new \DOMXPath($doc);
        $scripts = $xpath->query("//script");
        $script = "";
        foreach ($scripts as $s){
            if (strpos($s->nodeValue,"window.issuuDataCache") !== false){
                $script = $s->nodeValue;
            }
        }
        $pages = JSON::decode(preg_split('/\s=\s/',$script)[1]);
        $chiavi = array();
        foreach($pages['apiCache'] as $key => $value){
            $chiavi[] = $key;
        }
        preg_match("/\"username\":\"([a-z0-9\.]+)\",\"/",$page, $username);
        $username = $username[1];
        $doc_name = end(preg_split('/\//',$url));
        $chiave1 = "api-node.issuu.com/query|actionissuu.document.get_user_doc|documentusername{$username}|formatjson|name{$doc_name}|verifystate";
        $chiave2 = "/query|actionissuu.document.get_user_doc|documentusername{$username}|formatjson|name{$doc_name}|verifystate";
        $id_chiave = 0;
        for($i=0; $i<sizeof($chiavi); $i++){
            if($chiavi[$i] == $chiave1 || $chiavi[$i] == $chiave2){
                $id_chiave = $i;
            }
        }

        $leaflet_pages = $pages['apiCache'][$chiavi[$id_chiave]]['document']['pageCount'];
        $doc_id = $pages['apiCache'][$chiavi[$id_chiave]]['document']['documentId'];
        $leaflet_name = $pages['apiCache'][$chiavi[$id_chiave]]['document']['title'];
        $desciption = $pages["apiCache"][$chiavi[$id_chiave]]["document"]["description"];
        if ($desciption != null){
            $desciption = preg_split('/-/',$desciption);
            $start_date = end(preg_split("/\s/",$desciption[0]));
            preg_match('/([0-9.]{4,10})/',$desciption[1],$end_date);
            if (sizeof($start_date) == 1){
                $parts = preg_split(".",$end_date[1]);
                $start_date = "{$start_date}/{$parts[1]}/".date('Y');
            }elseif(sizeof($start_date) == 5){
                $parts = preg_split(".",$end_date[1]);
                preg_replace(".",$start_date,'/');
                $start_date+="/{$parts[2]}";
            }
            if (sizeof($end_date[1]) == 5){
                preg_replace(".",$end_date[1],'/');
                $end_date[1]+='/'.date('Y');
            }elseif(sizeof($end_date[1]) < 4){
                preg_replace(".",$end_date[1],'/');
                $end_date[1]+='/'.date('Y');
            }
            $end_date = $end_date[1];
        }else{
            $start_date = date('d/m/Y');
            $end_date = date('d/m/Y');
        }

        $images = array();
        $images['name'] = mb_convert_encoding($leaflet_name, "iso-8859-1", 'auto');
        $images['start_date'] = $start_date;
        $images['end_date'] = $end_date;
        $images['url'] = $url;
        $images['image_urls'] = array();
        for($i=1; $i<=$leaflet_pages; $i++){
            $image_url = "http://image.issuu.com/{$doc_id}/jpg/page_{$i}.jpg";
            $images['image_urls'][] = $image_url;
        }
        return $images;
    }
}
/* @var ArrayClass */
class ArrayClass{
    public static function flatten($array){
        $ret_array = array();
        foreach(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array)) as $value)
            {
                $ret_array[] = $value;
            }
        return $ret_array;
    }
}
/* @var Strings */
class Strings{
    public static function toUpCase($string){
        $string_parts = explode(" ", $string);
        $parts = [];
        foreach ($string_parts as $part) {
            $parts[] = ucfirst($part);
        }
        $string = implode(" ",$parts);
        return $string;
    }
}

/* @var OpeningHoursParser */
class OpeningHoursParser {
    static $WDAYS_LONG = array(
        "it" => array("lunedi", "martedi", "mercoledi", "giovedi", "venerdi", "sabato", "domenica"),
        "en" => array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"),
        "es" => array("lunes", "martes", "miercoles", "jueves", "viernes", "sabados", "domingos"),
        "ro" => array("luni", "marti", "miercuri", "joi", "vineri", "sambata", "duminica")
    );

    static $WDAYS_3 = array(
        "it" => array("lun", "mar", "mer", "gio", "ven", "sab", "dom"),
        "en" => array("mon", "tue", "wed", "thu", "fri", "sat", "sun"),
        "es" => array("lun", "mar", "mie", "jue", "vie", "sab", "dom"),
        "ro" => array("lun", "mar", "mie", "joi", "vin", "sam", "dum")
    );

    static $WDAYS_2 = array(
        "it" => array("lu", "ma", "me", "gi", "ve", "sa", "do"),
        "en" => array("mo", "tu", "we", "th", "fr", "sa", "su"),
        "es" => array("lu", "ma", "mi", "ju", "vi", "sa", "do"),
        "ro" => array("lu", "ma", "mi", "jo", "vi", "sa", "du")
    );

    static $WDAYS_1 = array(
        "it" => array("l", "m", "m", "g", "v", "s", "d"),
        "en" => array("m", "t", "w", "t", "f", "s", "s"),
        "es" => array("l", "m", "m", "J", "v", "s", "d"),
        "ro" => array("l", "m", "m", "j", "v", "s", "d")
    );

    static $BRIDGES = array(
        "it" => array("\s+al\s+", "\s+a\s+", "\s*-\s*"),
        "en" => array("\s+to\s+", "\s*-\s*"),
        "es" => array("\s+a\s+", "\s*-\s*"),
        "ro" => array("\s+pana la\s+", "\s*-\s*"),
    );

    static $CLOSED = array(
        "it" => array("chiuso"),
        "en" => array("closed"),
        "es" => array("cerrado"),
        "ro" => array("inchis", "INCHIS", "închis", "Închis"),
    );

    public static function tryMatch($s, $lang) {
        preg_match_all("/(\d{1,2}(?:(?::|\.)\d{2})?)/", $s, $matches);

        if (count($matches[1]) == 2 || count($matches[1]) == 4) {
            $hours = self::formatHours($matches[1]);

            // Strip hours from the text
            $s = preg_replace("/\d{1,2}(?:(?::|\.)\d{2})?[^\d]+\d{1,2}(?:(?::|\.)\d{2})?/", "", $s);
        } else {
            $closed = implode('|', self::$CLOSED[$lang]);

            if (preg_match("/\b{$closed}\b/i", $s)) {
                $hours = [];
            }
        }

        $days = self::matchDays($s, $lang);
        if ($days) {
            $final = array();

            foreach ($days as $dayNum) {
                $final[$dayNum] = $hours;
            }

            return $final;
        }
        else
            return false;
    }

    public static function formatHours($hours) {
        foreach ($hours as &$hour) {
            $hour = trim($hour);
            if (preg_match("/^\d{1,2}$/", $hour)) {
                $hour = $hour . ":00";
            } elseif (preg_match("/^\d{1,2}\.\d{1,2}$/", $hour)) {
                $hour = str_replace(".", ":", $hour);
            } elseif (preg_match("/^\d{1,2}:\d{1,2}$/", $hour)) {
                // We're fine
            } else {
                throw new \Exception("The hour {$hour} can't be formatted");
            }
        }

        return $hours;
    }

    public static function matchDays($s, $lang) {
        $result = self::matchDaysArray($s, self::$WDAYS_LONG[$lang], $lang);
        if (count($result) > 0)
            return $result;

        $result = self::matchDaysArray($s, self::$WDAYS_3[$lang], $lang);
        if (count($result) > 0)
            return $result;

        $result = self::matchDaysArray($s, self::$WDAYS_2[$lang], $lang);
        if (count($result) > 0)
            return $result;

        $result = self::matchDaysArray($s, self::$WDAYS_1[$lang], $lang);
        if (count($result) > 0)
            return $result;

        return FALSE;
    }

    private static function matchDaysArray($s, $days, $lang) {
        $s = strtolower(self::stripAccents($s));

        $daysRegex = '(' . implode('|', $days) . ')';
        $bridgesRegex = '(?:' . implode('|', self::$BRIDGES[$lang]) . ')';

        $foundDays = array();

        if (preg_match_all("/{$daysRegex}{$bridgesRegex}{$daysRegex}/i", $s, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $d1 = array_search($matches[1][$i], $days);
                $d2 = array_search($matches[2][$i], $days);

                for ($j = $d1; $j <= $d2; $j++)
                    $foundDays[] = $j;
            }

            $s = preg_replace("/{$daysRegex}{$bridgesRegex}{$daysRegex}/i", "", $s);
        }

        if (preg_match_all("/\b{$daysRegex}\b/", $s, $matches)) {
            foreach ($matches[1] as $day) {
                $d = array_search($day, $days);
                $foundDays[] = $d;
            }
        }

        sort($foundDays);
        return array_unique($foundDays);
    }

    private static function stripAccents($s) {
        $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'Ğ'=>'G', 'İ'=>'I', 'Ş'=>'S', 'ğ'=>'g', 'ı'=>'i', 'ş'=>'s',
            'ü'=>'u', 'ă'=>'a', 'Ă'=>'A', 'ș'=>'s', 'Ș'=>'S', 'ț'=>'t', 'Ț'=>'T', 'ę'=>'e'
        );

        return strtr($s, $unwanted_array);
    }
}
