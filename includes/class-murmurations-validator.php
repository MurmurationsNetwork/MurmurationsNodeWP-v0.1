<?php
/*
* Validate input from the admin form
*/

class Murmurations_Validator {

  var $invalid = array();

  public function __construct(){

  }

  var $types = array(
    'url',
    'text',
    'numeric',
    'integer',
  );


  public function validate_to_schema($schema,$data){
    foreach ($schema as $key => $field) {

      $value = $data[$key];

      if(!$this->isValid($field['type'],$value)){
        $this->invalid[] = $key;
      }
    }

    if(count($this->invalid) > 0){
      return false;
    }else{
      return true;
    }
  }

  public function isValid($type,$value){

    if(in_array($type,$this->types)){
       $f = "validate_".$type;
    }else{
      return false;
    }

    if(method_exists($this,$f)){
      if($this->$f($value)){
        return true;
      }else{
        return false;
      }
    }
  }

  public function validate_text($string){
    if($string != strip_tags($string)) {
      return false;
    }else{
      return true;
    }
  }

  public function validate_numeric($value){
     return is_numeric($value);
  }

  public function validate_int($value){
     return is_int($value);
  }

  public function validate_url($url){
    if(filter_var($url,FILTER_VALIDATE_URL)){
       return true;
    }else{
       return false;
    }
  }
}
