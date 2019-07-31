<?php

/**
 * Testing extending the environment to make the core...
* Plan is to define all environment-specific methods in this file. Can instantiate public of admin classes as necessary, and use pass-through/wrapper methods
*/

class Murmurations_Environment{

	public function __construct() {

	}

  public function get_base_path(){
    return plugin_dir_path(dirname( __FILE__ ));
  }

}
