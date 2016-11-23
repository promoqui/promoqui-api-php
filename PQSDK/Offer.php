<?php

namespace PQSDK;

class Offer {
  public $title, $description, $price, $original_price, $discount, $start_date, $end_date, $image, $store_ids, $national, $partner_link, $go_to_partner_link, $btn_other_offers_visible, $btn_partner_link_text, $btn_partner_link_visible, $btn_print_visible, $btn_stores_visible, $btn_online_offers_visible;


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

  public function national(){
    return empty($this->national) ? False : $this->national;
  }

  public function store_ids(){
    return empty($this->store_ids) ? array() : $this->store_ids;
  }

  public function btn_other_offers_visible() {
    if (empty($this->btn_other_offers_visible)) {
      return $this->btn_other_offers_visible = true;
    }
    return $this->btn_other_offers_visible;
  }

  public function btn_partner_link_visible() {
    if (empty($this->btn_partner_link_visible)){
      return $this->btn_partner_link_visible = true;
    }
    return $this->btn_partner_link_visible;
  }

  public function btn_print_visible() {
    if (empty($this->btn_print_visible)) {
      return $this->btn_print_visible = true;
    }
    return $this->btn_print_visible;
  }

  public function btn_stores_visible() {
    if (empty($this->btn_stores_visible)) {
      return $this->btn_stores_visible = true;
    }
    return $this->btn_stores_visible;
  }

  public function btn_online_offers_visible() {
    if (empty($this->btn_online_offers_visible)) {
      return $this->btn_online_offers_visible = true;
    }
    return $this->btn_online_offers_visible;
  }

  public function go_to_partner_link() {
    if (empty($this->go_to_partner_link)) {
      return $this->go_to_partner_link = true;
    }
    return $this->go_to_partner_link;
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
      throw new \Exception("Unexpected HTTP status code {$res[0]}, {$res[1]}", 1);      
    }

  }

}
