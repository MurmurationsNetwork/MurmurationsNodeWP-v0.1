<?php
/*
* Geocode addresses and other location information
*/

class Murmurations_Geocode {

  private $location_hash; // Hash of the location input, used as identifier for caching
  private $geo; // Array that holds results
  public $errors = array(); // Those things go here
  public $settings = array();

  function __construct($location){

    $this->settings = array(
      'cache_dir' => plugin_dir_path( __FILE__ ) . 'geocode_cache/',
      'api_url' => 'https://nominatim.openstreetmap.org/search',
    );

    $this->location_hash = $this->cacheHash($location);
    $this->location = $location;
  }

  /* Do the geocode lookup */
  function getCoordinates(){
    $url = $this->settings['api_url'];
    $cached_data = $this->loadCacheIfExists($this->location_hash);

    if($cached_data){
      $this->geo = $cached_data;
    }else{

      $data = array(
        'q' => $this->location,
        'format' => 'json',
      );

      $fields_string = http_build_query($data);

      llog($fields_string,"Querying geo api with string");

      $ch = curl_init();

      curl_setopt($ch,CURLOPT_URL, $url.'?'.$fields_string);
      curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch,CURLOPT_USERAGENT,'Murmurations');

      $result = curl_exec($ch);

      $data = json_decode($result,true);

      if($this->safe_count($data) > 0){
        $this->geo = $data[0]; // Get the first/best match

      }else{
        $this->setError("No matching location found");
        return false;
      }

      llog($this->geo,"Geo result. Saving to cache");

      $this->saveCache($this->geo);
    }

    $this->lat = $this->geo['lat'];
    $this->lon = $this->geo['lon'];
    return $this->geo;
    
  }

  function setError($error){
    $this->errors[] = $error;
    llog($error, "Geocoding error");
  }

  /* Generate the caching hash */
  function cacheHash($location){
    return md5($location);
  }

  /* Save caches to files. Currently using hash as filename */
  function saveCache($data){
    return file_put_contents($this->settings['cache_dir'].$this->location_hash,json_encode($data));
  }

  /* Load the cache */
  function loadCacheIfExists($hash){
    if(file_exists($this->settings['cache_dir'].$hash)){
      return json_decode(file_get_contents($this->settings['cache_dir'].$hash),true);
    }else{
      return false;
    }
  }

  /* Count an array that might not be an array */
  function safe_count($a){
    if(is_array($a)){
      return count($a);
    }else{
      return false;
    }
  }

}
