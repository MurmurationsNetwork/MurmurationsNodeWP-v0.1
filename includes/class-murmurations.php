<?php

/**
 * Core murmurations functionality, generally not WP-dependent
 * This class gets instantiated from within the public or admin callback call stack
*/

class Murmurations_Core {

  var $schema;
  var $data;
  var $settings = array(
    'index_url' => 'http://localhost/projects/murmurations/murmurations-index/murmurations-index.php',
    //'index_url' => 'https://murmurations.network/api/index',
    'plugin_context' => 'wordpress',
    'api_path' => 'murmurations/v1/get/node',
  );

	public function __construct() {

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
  // TODO:
  // Local data at various steps in this for performance
  // Possibly break up into multiple methods

  public function make_addon_fields($network_names_str){

    $network_names = explode(',',$network_names_str);
    $network_urls = array();
    $schemas = array();
    $html = '';

    // Load the network list from the index
    $network_list = $this->load_networks();

    $test_out = '';

    // Match network names to schema URLs
    foreach ($network_list as $url => $network) {
      //$test_out .= "Comparing $network[name] with ".print_r($network_names,true);
      if(in_array($network['name'],$network_names)){
        $network_urls[] = $network['schemaUrl'];
      }
    }

    // Query the networks to collect their schemas
    foreach ($network_urls as $schema_url) {
      $schemas[] = $this->get_network_schema($schema_url);
    }

    foreach ($schemas as $schema) {

      //TODO: Query appropriate network for data on this node with which to prepopulate fields

      foreach ($schema as $key => $field) {

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

    if(!$result){
      $this->log_error("CURL Error");
      return false;
    }else{
      return $result;
    }
  }

}
