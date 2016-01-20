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
            return array_map(function($brand){
                return self::from_json($brand);
            }, $res[1]);
        }else if($res[0] == 400){
            return null;
        }else{
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        }

    }

    public function find($name){
        $res = RestLayer::get('v1/brands/search', array("q"=> $name), array('Authorization'=>'Bearer ' . self::token()));
        if ($res[0] == 200){
            return self::from_json($res[1]);
        }else if($res[0] == 404){
            return null;
        }else{
            throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}");
        }
    }


    private function from_json($json) {
        $result = new Brand();

        foreach ($json as $key => $val) {
            $result->{$key} = $val;
        }

        return $result;
    }

  
  
}
