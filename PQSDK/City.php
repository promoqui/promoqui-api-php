<?php

namespace PQSDK;


class City
{
    public $id, $name, $inhabitants, $latitude, $longitude;

    public function token() {
        return Token::accessToken();
    }

    public function find($name){
        $res = RestLayer::get('v1/cities', array(
            'q'=> $name,
        ), array(
            'Authorization' => "Bearer " . self::token()
        ));

        if ($res[0] == 200) {
            return json_decode($res[2], true)["id"];
        } else if ($res[0] == 404) {
            return null;
        } else {
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        }
    }

    public function all() {
        $res = RestLayer::get('v1/cities', array(), array( "Authorization" => "Bearer " . self::token()));
        if ($res[0] == 200){
            $cities = array();
            foreach ($res[1] as $city) {
                $cities[] = self::from_json($city);
            }
            return $cities;
        }else if($res[0] == 404){
            return null;
        }else{
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        }
    }

    public function createOrUpdate($name){
        $city = self::find($name);
        if ($city){
            return $city;
        }else{
            $res = RestLayer::post('v1/cities',
                array("name" => $name),
                array('Authorization' => "Bearer " . self::token())
            )[2];
            return json_decode($res, true)['id'];
        }
    }

    private function from_json($json) {
        $result = new City();

        foreach ($json as $key => $val) {
            $result->{$key} = $val;
        }

        return $result;
    }


}