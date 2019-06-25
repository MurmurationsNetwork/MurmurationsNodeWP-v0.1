<?php
/*
* Geocode addresses and other location information
*/

class Murmurations_Geocode {

  private $location_hash;
  private $geo

  protected $settings = array(
    'cache_dir' => plugin_dir_path( __FILE__ ) . 'geocode_cache/';
  );

  function __construct($location){
    $this->location_hash = $this->cacheHash($location);
  }

  function getCoordinates(){
    $cached_data = loadCacheIfExists($this->location_hash);
    if($cached_data){
      $this->geo = $cached_data;
    }else{
      
    }
  }

  function cacheHash($location){
    return md5($location);
  }

  function saveCache($data){
    return file_put_contents($this->settings['cache_dir'].$this->location_hash,$data);
  }

  function loadCacheIfExists($hash){
    if(file_exists($this->settings['cache_dir'].$hash)){
      return json_decode(file_get_contents($this->settings['cache_dir'].$hash));
    }else{
      return false;
    }
  }

}
