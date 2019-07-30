<?php

/**
 * Plugin Name:       Murmurations
 * Plugin URI:        Murmurations.network
 * Description:       Making movement visible
 * Version:           0.1.0-alpha
 * Author:            A. McKenty / Photosynthesis
 * Author URI:        Photosynthesis.ca
 * License:           Peer Production License
 * Text Domain:       murmurations
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('MURMURATIONS_VERSION', '0.0.1');
define('MURM_DATA_OPT_KEY', 'murmurations_data');
define('MURM_SCHEMA_OPT_KEY', 'murmurations_schema');
define('MURM_SETTINGS_OPT_KEY', 'murmurations_settings');


function activate_murmurations() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-murmurations-activator.php';
	Murmurations_Activator::activate();
}

function deactivate_murmurations() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-murmurations-deactivator.php';
	Murmurations_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_murmurations' );
register_deactivation_hook( __FILE__, 'deactivate_murmurations' );

/* Include the wordpress class */
require plugin_dir_path( __FILE__ ) . 'includes/class-murmurations-wp.php';
if(!class_exists('LazyLog')){
  require plugin_dir_path( __FILE__ ) . 'includes/lazylog.php';
}

/* Temporary development only logging activity */
LazyLog::setSetting('bufferLog',true);

add_action('wp_footer', 'murms_flush_log');
add_action('admin_footer', 'murms_flush_log');

function murms_flush_log(){
   echo "<div style=\"margin-left:200px;\">";
   LazyLog::flush();
   echo "</div>";
}

// Ajax action to refresh the logo image
add_action( 'wp_ajax_murmurations_get_image', 'murmurations_get_image'   );

function murmurations_get_image() {
    if(isset($_GET['id']) ){
        $image = wp_get_attachment_image( filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT ), 'medium', false, array( 'id' => 'murmurations-preview-image' ) );
        $data = array(
            'image'    => $image,
        );
        wp_send_json_success( $data );
    } else {
        wp_send_json_error();
    }
}


// Ajax action to get add-on fields
add_action( 'wp_ajax_murmurations_get_addon_fields', 'murmurations_get_addon_fields'   );

function murmurations_get_addon_fields() {
  if(isset($_GET['networks'])){
    $murmurations_core_instance = new Murmurations_Core();
    $html = $murmurations_core_instance->make_addon_fields($_GET['networks']);
    wp_send_json_success($html);
  }else{
    wp_send_json_error();
  }
}

function run_murmurations_wp() {

	$murmurations_env_instance = new Murmurations_WP();
	$murmurations_env_instance->run();

}
run_murmurations_wp();
?>
