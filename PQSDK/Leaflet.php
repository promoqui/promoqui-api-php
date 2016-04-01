<?php

namespace PQSDK;

class Leaflet {
    public $id, $name, $url, $start_date, $end_date, $pdf_data, $image_urls, $store_ids;

    public function __construct(){
        $this->image_urls = [];
    }

    public function token() {
        return Token::accessToken();
    }

    public function createOrUpdate($fields) {
        $lid = self::find($fields["url"]);

        echo "Creating or updating leaflet\n";

        if ($lid) {
            return $lid;
        } else {
            $res = RestLayer::post('v1/leaflets', $fields, array(
                'Authorization' => "Bearer " . self::token()
            ))[2];

            return json_decode($res, true)["id"];
        }
    }

    public function find($url) {
        $res = RestLayer::get('v1/leaflets', array("url" => $url), array(
            'Authorization' => "Bearer " . self::token()
        ));
        if ($res[0] == 200)
            return self::from_json(json_decode($res[2], true));
        else if ($res[0] == 404)
            return null;
        else
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
    }

    public function show() {
        $method = "get";
        $endpoint = "v1/leaflet";
        $expected_status = 201;
        $fields = [];
        $fields['id'] = (is_int($this->id) && ($this->id != null)) ? $this->id : null;

        $res = RestLayer::get($method, $endpoint, $fields, array(
            'Authorization' => "Bearer " . self::token()
        ));

        if ($res[0] != $expected_status) {
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        }else{
            return self::from_json(json_decode($res[2], true));
        }
    }

    public function save() {

        if ($this->id != null) {
            $method = "put";
            $url = "v1/leaflets/{$this->id}";
            $expected_status = 200;
        } else {
            $method = "post";
            $url = "v1/leaflets";
            $expected_status = 201;
        }
        $fields = [];
        foreach (array('name','url') as $field) {
            if($this->{$field} == null){
                throw new \Exception("Missing required {$field} field!",1);
            }else{
                $fields[$field] = $this->{$field};
            }
        }
        //$fields['name'] = ($this->name == null) ? null : $this->name;
        //$fields['url'] = ($this->url == null) ? null : $this->url;
        $fields['start_date'] = ($this->start_date == null) ? null : $this->start_date;
        $fields['end_date'] = ($this->end_date == null) ? null : $this->end_date;
        $fields['pdf_data'] = ($this->pdf_data == null) ? null : $this->pdf_data;
        $fields['image_urls'] = (empty($this->image_urls)) ? array() : json_encode(array_values($this->image_urls));
        $fields['store_ids'] = (empty($this->store_ids)) ? array() : json_encode(array_values($this->store_ids));
        $res = RestLayer::request($method, $endpoint, $fields, array(
            'Authorization' => "Bearer " . self::token()
        ));
        if ($res[0] != $expected_status) {
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        } else if ($method == "post") {
            $this->id = json_decode($res[2], true)['id'];
        }
        return $this->id;
    }

    private function from_json($json) {
        $result = new Leaflet();
        foreach ($json as $key => $val) {
            if (property_exists($result, $key)) {
                if ($key != 'retailer' && $key != 'image_urls' && $key != 'pages') {
                    $result->{$key} = $val;
                }
            }elseif($key == 'retailer' && property_exists($this, 'retailer_id')){
                $result['retailer_id'] = $val['id'];
            } elseif($key == 'pages' && property_exists($this, 'page')) {
                foreach($val as $page){
                    $result['page_ids'][] = $page['id'];
                }
            }
        }
        return $result;
    }
}
