<?php

namespace PQSDK;

class Store {
    public $id, $name, $address, $zipcode, $latitude, $longitude, $phone, $city_id, $city, $origin, $opening_hours, $opening_hours_text, $leaflet_ids;

    public function __construct() {
        $this->leaflet_ids = array();
        $this->opening_hours = array();
    }

    public function token() {
        return Token::accessToken();
    }

    public function createOrUpdate($fields) {
        $sid = self::find($fields["address"], $fields["zipcode"]);

        echo "Creating or updating store\n";

        if ($sid) {
            return $sid;
        } else {
            $opening_hours = array();

            foreach ($fields['openingHours'] as $day => $hours) {
                switch (count($hours)) {
                    case 0:
                        $oh = array('weekday' => $day, 'closed' => true);
                        break;
                    case 1:
                        $oh = array('weekday' => $day, 'open_am' => $hours[0]);
                        break;
                    case 2:
                        $oh = array('weekday' => $day, 'open_am' => $hours[0], 'close_am' => $hours[1]);
                        break;
                    case 3:
                        $oh = array('weekday' => $day, 'open_am' => $hours[0], 'close_am' => $hours[1], 'open_pm' => $hours[2]);
                        break;
                    case 4:
                        $oh = array('weekday' => $day, 'open_am' => $hours[0], 'close_am' => $hours[1], 'open_pm' => $hours[2], 'close_pm' => $hours[3]);
                        break;
                }

                $opening_hours[] = $oh;
            }

            $fields['openingHours'] = $opening_hours;

            $res = RestLayer::post('v1/stores', $fields, array(
                'Authorization' => "Bearer " . self::token()
            ))[2];

            return json_decode($res, true)["id"];
        }
    }

    public function find($address, $zipcode) {
        $res = RestLayer::get('v1/stores', array(
            'address'=> $address,
            'zipcode'=> $zipcode,
        ), array(
            'Authorization' => "Bearer " . self::token()
        ));

        if ($res[0] == 200) {
            return self::from_json(json_decode($res[2], true));
        } else if ($res[0] == 404) {
            return null;
        } else {
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        }
    }

    public function get($id) {
        $res = RestLayer::get("v1/stores/{$id}", array(),  array(
            'Authorization' => "Bearer " . self::token()
        ));

        if ($res[0] == 200) {
            self::from_json(json_decode($res[2], true));
        } else if ($res[0] == 404) {
            return null;
        } else {
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        }
    }

    public function save() {
        if ($this->id != null) {
            $method = "put";
            $url = "v1/stores/{$this->id}";
            $expected_status = 200;
        } else {
            $method = "post";
            $url = "v1/stores";
            $expected_status = 201;
        }
        $fields = array();
        foreach (array('name', 'address', 'zipcode', 'latitude', 'longitude', 'origin') as $field) {
            if ($this->{$field} == null) {
                throw new \Exception("Missing required {$field} field", 1);
            } else {
                $fields[$field] = $this->{$field};
            }
        }

        if ( $this->city == null and $this->city_id == null ) {
          throw new \Exception("city or city_id must be set");
        }

        $fields["city_id"] = ($this->city_id == null) ? null : $this->city_id;
        $fields["city"] = ($this->city == null) ? null : $this->city;

        $fields['leaflet_ids'] = (empty($this->leaflet_ids)) ? array() : json_encode($this->leaflet_ids);

        $fields['phone'] = ($this->phone == null) ? null : $this->phone;
        $fields['opening_hours'] = (empty($this->opening_hours)) ? null : json_encode($this->opening_hours);
        $fields['opening_hours_text'] = ($this->opening_hours_text== null) ? null : $this->opening_hours_text;
        $res = RestLayer::request($method, $url, $fields, array(
            'Authorization' => "Bearer " . self::token()
        ));

        if ($res[0] != $expected_status) {
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        } else {
            if ($method == "post")
                $this->id=json_decode($res[2], true)['id'];
        }
        return $this->id;
    }

    private function from_json($json) {
        $result = new Store();

        foreach ($json as $key => $val) {
            if (property_exists($result, $key)) {
                if ($key != 'country' && $key != 'city') {
                    $result->{$key} = $val;
                }
            } else {
                if ($key != 'country' && $key != 'city' && $key != 'retailer_id') {
                    $result->{$key} = $val;
                }
            }
        }

        return $result;
    }

}
