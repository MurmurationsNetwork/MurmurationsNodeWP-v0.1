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

    // Check if there are already values in the DB
    if(!get_option(MURM_DATA_OPT_KEY)){

      $data['name'] = get_bloginfo('title');
      $data['url'] = get_site_url();
      $data['tagline'] = get_bloginfo('description');
      $data['feed'] = get_bloginfo('rss2_url');

      $logo_data = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
      $data['logo'] = $logo_data[0];

      add_option(MURM_DATA_OPT_KEY,$data);

    }

    add_option(MURM_SETTINGS_OPT_KEY,"");
    update_option(MURM_SCHEMA_OPT_KEY,$base_schema);



	}

}
