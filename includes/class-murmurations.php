<?php

/**
 * Core murmurations functionality, generally not WP-dependent
 * This class gets instantiated from within the public or admin callback call stack.
*/

class Murmurations_Core extends Murmurations_Environment{

  var $schema;
  var $data;
  var $settings = array(
    'index_url' => 'https://murmurations.network/api/index',
    'plugin_context' => 'wordpress',
    'api_path' => 'murmurations/v1/get/node',
    'addon_fields_file' => 'schemas/addons.json',
    'base_schema_file' => 'schemas/base.json',
    'enable_networks' => 'false'
  );

	public function __construct() {

    // Local debugging. TODO: make this conditional on a "use local URLs" option value, rather than hard-coding it here. Or better yet, allow the index URL in general to be set via a config var, to allow the use of custom indexes.

    if($_SERVER['HTTP_HOST'] == 'localhost'){
      $this->settings['index_url'] = 'http://localhost/projects/murmurations/murmurations-index/murmurations-index.php';
    }

	}

  public function load_schema(){
    $schema = get_option(MURM_SCHEMA_OPT_KEY);
    if($schema){
      $this->schema = $schema;
    }else{
      $this->log_error('Failed to load murmurations schema');
      return false;
    }
    return $schema;
  }

  public function load_schemas(){
    $schemas = $this->json_to_array(file_get_contents($this->get_base_path().$this->settings['base_schema_file']));
    $addons = $this->json_to_array(file_get_contents($this->get_base_path().$this->settings['addon_fields_file']));

    foreach ($addons as $key => $field) {
      $schemas[$key] = $field;
    }

    if($schemas){
      $this->schemas = $schemas;
      return $schemas;
    }else{
      $this->log_error('Failed to load murmurations schema');
      return false;
    }
  }

  public function load_data(){
    $data = get_option(MURM_DATA_OPT_KEY);
    if($data){
      $this->data = $data;
    }else{
      $this->log_error('Failed to load murmurations data');
      return false;
    }
    return $data;
  }

  public function get_api_url(){
    // Should call environment for API url base here
    return get_rest_url().$this->settings['api_path'];
  }

  public function save_data($data){

    llog($data,"Saving data to local DB");

    // Save the local node data
    // Should be call to environment class
    $result = update_option(MURM_DATA_OPT_KEY,$data);
    if(!$result){
      llog("No change or failed to save option value");
    }

    $url = $this->settings['index_url'];

    $api_url = $this->get_api_url();

    /* Post to the index */
    $fields = [
        'action' => 'put_node',
        'plugin_context' => $this->settings['plugin_context'],
        'node_data' => array(
          'apiUrl' => $api_url,
          'url'      =>  $data['url'],
        ),
    ];

    llog($fields,"Data going to index");

    $fields_string = http_build_query($fields);

    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);

    if(!$result){
      $this->log_error("Failed to post data to index.");
      return false;
    }else{
      return true;
    }
  }

  /* Query the index to get all the networks with custom schemas */
  public function load_networks(){
    $url = $this->settings['index_url'];
    $data = array(
      'action' => 'get_networks'
    );
    $this->networks = $this->json_to_array($this->curl_request($url,$data,"POST"));
    llog($this->networks,"Networks retrieved from index");
    return $this->networks;
  }

  public function get_network_schema($url){
    return $this->json_to_array($this->curl_request($url));
  }

  /* Generate addon fields based on network memberships list */
  /* This needs to be mofidied so that:
      When the networks value is changed, it saves the schema locally
  */

  public function make_addon_fields($network_names_str){

    $network_names = explode(',',$network_names_str);
    $network_urls = array();
    $schemas = array();
    $html = '';

    // Load the network list from the index TODO: cache this locally
    $network_list = $this->load_networks();

    // Match network names to schema URLs
    foreach ($network_list as $url => $network) {
      if(in_array($network['name'],$network_names)){
        $network_urls[] = $network['schemaUrl'];
      }
    }

    // Query the networks to collect their schemas
    foreach ($network_urls as $schema_url) {
      $schemas[] = $this->get_network_schema($schema_url);
    }

    // Get current values from the DB. This is bad here, because it's duplicating what's already happening in the main admin form function. TODO: rewrite this whole section so that it's all loaded normally, from the main function, when networks and schemas are locally saved, and this only happens when there's a change via ajax (which will eventually be replaced with the wizard interface for new installations anyway...)
    $data = $this->load_data();

    foreach ($schemas as $schema) {

      //TODO: Query appropriate network for data on this node with which to prepopulate fields

      foreach ($schema as $key => $field) {

        $this->save_addon_field($key,$field);

        if($field['inputAs'] == 'none') continue;

        $name = "murmurations[$key]";

        $field_settings = array(
          'type' => $field['type'],
          'inputAs' => $field['inputAs'],
          'current_value' => $data[$key],
          'title' => $field['title'],
          'multiple' => $field['multiple'],
          'options' => $field['options'],
          'maxLength' => $field['maxLength'],
          'elementId' => 'murmurations_'.$key
        );

        if($field['multiple'] && $field['inputAs'] == 'text'){
          $field_settings['autocomplete'] = true;
        }

        $field = new Murmurations_Field($name, $field_settings);

        $html .= $field->show();
      }
    }
    return $html;
    /**/
  }

/* Save an addon field to the local addon schema file */
  public function save_addon_field($key,$field){
    if(!$this->addon_fields){
      $this->addon_fields = $this->load_local_addon_fields();
    }
    $this->addon_fields[$key] = $field;
    return file_put_contents($this->get_base_path().$this->settings['addon_fields_file'],json_encode($this->addon_fields));
  }


  /* Load addon fields from the local addon fields schema file */
  public function load_local_addon_fields(){
    return $this->json_to_array(file_get_contents($this->get_base_path().$this->settings['addon_fields_file']));
  }

  public function make_json_ld($data = false){
    if(!$data){
      $data = $this->data;
    }

    $jld = '{'."\n";
    $jld .= '"@context": "http://schema.org/",'."\n";
    $jld .= '"@type": "Organization",'."\n";

    $inter = '';
    foreach ($data as $key => $value) {
      $jld .= $inter.'"'.$key.'": "'.$value.'"';
      $inter = ',';
    }

    $jld .= '}';


    $this->json_ld = $jld;
    return $jld;

  }

  public function log_error($error){
    if(is_callable('llog')){
      llog($error);
    }
  }

  public function json_to_array($json){
    $array = json_decode($json,true);
    if(!is_array($array)){
      $array = json_decode($array,true);
    }
    if(!is_array($array)){
      return false;
    }else{
      return $array;
    }
  }

  public function curl_request($url, $data = null, $method = "GET"){

    $ch = curl_init();

    if($data){
      $fields_string = http_build_query($data);
      if($method == "POST"){
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
      }
    }

    curl_setopt($ch,CURLOPT_URL, $url);

    if($method == "POST"){
      curl_setopt($ch,CURLOPT_POST, true);
    }

    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);

    if($result === false){ // Actual cURL error
      $this->log_error("cURL Error:".curl_error($ch));
      $this->log_error("Connection attempted to:".$url);
      $this->log_error("Fields sent:".$fields_string);
      curl_close($ch);
      return false;
    }else if(!$result){ // Something probably went wrong, but not a cURL error
      $this->log_error("Empty result returned from cURL request");
      $this->log_error(curl_getinfo($ch));
      curl_close($ch);
      return false;
    }else{
      curl_close($ch);
      return $result;
    }
  }

}
