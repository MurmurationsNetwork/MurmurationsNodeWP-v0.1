<?php

/**
 * Core murmurations functionality, generally not WP-dependent
 * This class gets instantiated from within the public or admin callback call stack
*/

class Murmurations_Core {

  var $schema;
  var $data;
  var $settings = array(
    'index_url' => 'http://localhost/projects/murmurations//murmurations-index/murmurations-index.php',
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

}
