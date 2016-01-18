<?php

namespace PQSDK;

class Offer {
  public $title, $description, $price, $original_price, $discount, $start_date, $end_date, $image, $store_ids;


  public function __construct($params = array()){
    foreach ($params as $key => $value) {
      if( method_exists($this, $key)){
        $this->$key = $value;
      }
    }
    if(empty($this->store_ids)){
    	$this->$store_ids = [];
    }
  }


  public function token() {
    return Token::accessToken();
  }


  public function save() {
    $method = "post";
    $endpoint = "v1/offers";

    $fields = [];
    foreach (array("title","description","price","original_price","discount","start_date","end_date","image") as $field) {
      if(!empty($this->{$field})){
        $fields[$field] = $this->{$field};
      }
    }
    $fields["store_ids"] = $this->store_ids;

    $res = RestLayer::request($method, $endpoint, $fields, array(
        "Authorization" => "Bearer " . self::token(),
        "Content-Type" => "application/json"
      ));

    if($res[0] == 200 or $res[0] == 201){

    }elseif ($res[0] == 400) {
      throw new \Exception("Bad Request! Error: {$res[1]["errors"]}", 1);
    }else{
      throw new \Exception("Unexpected HTTP status code {$res[0]}", 1);      
    }

  }

}
