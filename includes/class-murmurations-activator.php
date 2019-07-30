<?php

/**
 * Fired during plugin activation
**/

class Murmurations_Activator {

	public static function activate() {

    // Load the base schema
    $base_schema = json_decode(file_get_contents(plugin_dir_path( dirname( __FILE__ ) ) . 'schemas/base.json'),true);

    if(!$base_schema){
      die('No base schema found');
    }

    llog($base_schema,"Base schema from FS");

    $data = array();

    $wp_data['name'] = get_bloginfo('title');
    $wp_data['url'] = get_site_url();
    $wp_data['tagline'] = get_bloginfo('description');
    $wp_data['feed'] = get_bloginfo('rss2_url');

    $logo_data = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
    $wp_data['logo'] = $logo_data[0];
    $wp_data['tags'] = self::get_site_tags();

    // Check if there are already values in the DB
    $db_data = get_option(MURM_DATA_OPT_KEY);
    if(is_array($db_data)){
      foreach ($wp_data as $key => $value) {
       if(!isset($db_data[$key])){
         $db_data[$key] = $value;
       }
      }
      update_option(MURM_DATA_OPT_KEY,$db_data);
    }else{
      add_option(MURM_DATA_OPT_KEY,$wp_data);
    }

    add_option(MURM_SETTINGS_OPT_KEY,"");
    update_option(MURM_SCHEMA_OPT_KEY,$base_schema);

	}

  public function get_site_tags(){
    $tags = get_tags(array('orderby'=>'count','order' => 'DESC','number' => 7));
    $tags_a = array();
    foreach ($tags as $tag) {
      $tags_a[] = $tag->name;
    }
    return join(', ',$tags_a);
  }

}
