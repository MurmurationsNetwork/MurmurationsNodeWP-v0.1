<?php
/*
* Make admin form fields
*/

class Murmurations_Field {

  private $default_settings = array(
    'type' => 'string',
    'validateAs' => 'text',
    'inputAs' => 'text',
    'title' => null,
    'current_value' => null,
    'options' => null,
    'multiple' => false,
    'autocomplete' => false,
    'element_id' => false,
    'classes' => array(),
    'maxLength' => 500
  );

  var $input_types = array(
    'url',
    'text',
    'numeric',
    'integer',
    'email',
    'image',
    'location'
  );

  public function __construct($name, $settings = null){

    if(is_array($settings)){
      $this->settings = array_merge($this->default_settings,$settings);
    }

    if(!in_array($this->settings['inputAs'],$this->input_types)){
      llog($this->settings['inputAs'],"Invalid field input type");
      return false;
    }

    $this->name = $name;

    llog($settings,"New form field with settings");

  }



  public function show(){
    $f = "show_".$this->settings['inputAs'];

    if($this->settings['multiple'] && $this->settings['autocomplete']){
      $this->datalist = join(', ',$this->settings['options']);
      $this->settings['classes'][] = 'autocomplete-multiple';
    }

    $this->css_classes = join(' ',$this->settings['classes']);

    if(method_exists($this,$f)){
      $field .= $this->$f();
    }else{
      llog("Missing field function: $f");
      return false;
    }

    $html = '<div class="murmurations-admin-field">'."\n";
    $html .= '<label for="'.$this->name.'">'.$this->settings['title'].'</label>';
    $html .= $field;
    $html .= '</div>';

    return $html;

  }

  public function show_text(){
    if((int) $this->settings['maxLength'] > 500){
      $html = '<textarea class="'.$this->css_classes.'" name="'.$this->name.'" id="'.$this->name.'">';
      $html .= $this->settings['current_value'];
      $html .= '</textarea>'."\n";

      return $html;
    }else{
      $html = '<input type="text" class="'.$this->css_classes.'" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->settings['current_value'].'" ';

      if($this->datalist){
        $html .= 'data-list="'.$this->datalist.'" ';
      }

      $html .=  '/>'."\n";

      return $html;
    }
  }

  public function show_image(){

    if($this->settings['current_value']){
      $image_id = attachment_url_to_postid($this->settings['current_value']);
    }

    if( intval($image_id) > 0 ) {
        $image = wp_get_attachment_image( $image_id, 'small', false, array( 'id' => 'murmurations-preview-image' ) );
    } else {
        $image = '<img id="murmurations-preview-image" src="'.$this->settings['current_value'].'" />';
    }

    $html = '<div>'.$image;
    $html .= '<input type="hidden" name="'.$this->name.'" id="murmurations_image_id" value="'.esc_attr( $image_id ).'" />';
    $html .= '<br><input type="button" value="Select image" id="murmurations_image_select"/></div>';
        return $html;
  }


  public function show_location(){

    return "location field";

  }

  // This needs a whole bunch of things -- images, etc.


}
