<?php

namespace PQSDK;


class City
{
    public $id, $name, $inhabitants, $latitude, $longitude, $state, $country, $is_country;

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
            return $this->from_json($res[2]);
        } else if ($res[0] == 404) {
            return null;
        } else {
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        }
    }

    public function all() {
        $res = RestLayer::get('v1/cities', array(), array( "Authorization" => "Bearer " . self::token()));
        if ($res[0] == 200){
            $cities = self::from_json($res[2]);
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
            return $this->from_json($res);
        }
    }

    private function from_json($json) {

        $json = json_decode($json);

        $result = null;

        if( (is_array($json) && count($json)==1) ){
            $result = $this->to_object($json[0]);
        }else if($json instanceof \stdClass){
            $result = $this->to_object($json);
        }
        else{
            foreach ($json as $elem){
                $result [] = $this->to_object($elem);
            }
        }

        return $result;
    }

    private function to_object($obj){

        $result = new City();

        foreach ($obj as $key => $val) {
            $result->{$key} = $val;
        }
        return $result;
    }


}