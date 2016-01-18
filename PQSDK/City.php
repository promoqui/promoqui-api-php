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
            throw new \Exception("Unexpected HTTP status code {$res[0]}");
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



}