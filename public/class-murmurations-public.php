<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       uri
 * @since      1.0.0
 *
 * @package    Murmurations
 * @subpackage Murmurations/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Murmurations
 * @subpackage Murmurations/public
 * @author     Murmurations <murmurations>
 */
class Murmurations_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Murmurations_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Murmurations_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/murmurations-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Murmurations_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Murmurations_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/murmurations-public.js', array( 'jquery' ), $this->version, false );

	}

  public function show_structured_data(){

    echo "<!-- Murmurations structured data block -->";
    echo '<script type="application/ld+json">'."\n";
    echo $this->get_structured_data();
    echo '</script>'."\n";

  }

  public function get_structured_data(){

    $murm = new Murmurations_Core();

    $murm->load_data();

    $murm->make_json_ld();

    return $murm->json_ld;

  }

  public function register_api_route(){
    register_rest_route( 'murmurations/v1', '/get/node', array(
    'methods' => 'GET',
    'callback' => array($this,'api_request'),
    ) );
  }

  public function api_request(){
    return $this->get_structured_data();
  }

}
?>
