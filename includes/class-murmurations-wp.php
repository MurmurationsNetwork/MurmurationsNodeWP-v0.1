<?php

/*
This class is the main handler for Murmurations' interactions with the WP environment

 What it does:
  Handles version data
  Loads dependencies
  Sets up public and admin WP hooks

*/
class Murmurations_WP {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 */
	protected $version;

  public $core; // Holds the core clas instance (this all needs to be seriously rethought)

	public function __construct() {
		if ( defined( 'MURMURATIONS_VERSION' ) ) {
			$this->version = MURMURATIONS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'murmurations';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-murmurations-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-murmurations-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-murmurations-validator.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-murmurations-field.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-murmurations-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-murmurations-public.php';

    if(!class_exists('Murmurations_Geocode')){
  		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-murmurations-geocode.php';
    }

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-murmurations.php';

		$this->loader = new Murmurations_Loader();

	}

  /* For eventual internationalization */
	private function set_locale() {

		$plugin_i18n = new Murmurations_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function define_admin_hooks() {

		$plugin_admin = new Murmurations_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
    $this->loader->add_action('admin_menu',$plugin_admin,'register_admin_page');
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

	}

  /* Wrapper, because for now core methods can't be called directly... */
  public function make_addon_fields($network_names){
    //return "In the env ajax target function";
    return $this->core->make_addon_fields($networks);
  }

	private function define_public_hooks() {

		$plugin_public = new Murmurations_Public( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

    $this->loader->add_action('wp_head',$plugin_public,'show_structured_data');
    $this->loader->add_action( 'rest_api_init',$plugin_public,'register_api_route');

	}

	/*
  Start the ball rolling
	 */
	public function run() {
		$this->loader->run();
	}


	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}


}
