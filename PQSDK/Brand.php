<?php

namespace PQSDK;

class Brand {

    public $id, $name, $slug;


    public function token() {
        return Token::accessToken();
    }

    public function all() {
        $res = RestLayer::get('v1/brands', array(), array(
            'Authorization' => 'Bearer ' . self::token()
        ));

        if($res[0] == 200){

            $array = self::from_json($res[2]);

            return $array;
        }else if($res[0] == 400){
            return null;
        }else{
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        }

    }

    public function find($name){
        $res = RestLayer::get('v1/brands/search', array("q"=> $name), array('Authorization'=>'Bearer ' . self::token()));
        if ($res[0] == 200){
            return self::from_json($res[2]);
        }else if($res[0] == 404){
            return null;
        }else{
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        }
    }


    private function from_json($json) {

        $json = json_decode($json);

        $result = null;

        if(count($json)==1){
            $result = $this->to_object($json[0]);
        }else{
            foreach ($json as $elem){
                $result [] = $this->to_object($elem);
            }
        }

        return $result;
    }

    private function to_object($obj){

        $result = new Brand();

        foreach ($obj as $key => $val) {
            $result->{$key} = $val;
        }
        return $result;
    }

  
  
}
