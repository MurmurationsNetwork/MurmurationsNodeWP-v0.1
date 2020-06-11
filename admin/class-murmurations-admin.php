<?php



/**
 * The admin-specific functionality of the plugin.
 */
class Murmurations_Admin {

	private $plugin_name;
	private $version;
  public $notices = array();

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
    $this->core = new Murmurations_Core();

	}

	/*
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/murmurations-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'murmurations_awesomeplete', plugin_dir_url( __FILE__ ) . 'css/awesomplete.css');

	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/murmurations-admin.js');

    wp_enqueue_script( $this->plugin_name."_awesomeplete", plugin_dir_url( __FILE__ ) . 'js/awesomplete.min.js',array( 'jquery' ), $this->version, false );

    wp_enqueue_media();

	}

  /**
	 * Register the settings for the admin area. (Currently unused, since we're avoiding the settings API for now)
	 */
  public function register_settings() {
  }

  public function register_admin_page() {

      $page_title = 'Murmurations';
      $menu_title = 'Murmurations';
      $capability = 'edit_posts';
      $menu_slug = 'murmurations';
      $function = array($this,'show_admin_page');
      $icon_url = '';
      $position = 24;

      add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
  }

  public function show_admin_page(){

    // Process form data
    if (isset($_POST['murmurations'])) {
      $this->process_admin_form();
    }

    echo "<h1>Murmurations</h1>";
    echo "<h3>Edit and add information about your organisation or project </h3>";

    llog(get_tags(array('orderby'=>'count','order' => 'DESC')));

    echo $this->show_notices();

    $this->show_admin_form($murm_post_data);

  }

  public function show_admin_form($post_data = false){

    $schema = $this->core->load_schema();
    $data = $this->core->load_data();


    if($this->core->settings['enable_networks'] == 'true'){

      $networks = $this->core->load_networks();

      foreach ($networks as $url => $network) {
        $network_options[] = $network['name'];
      }

      llog($network_options,"Network options");

      $schema['networks']['options'] = $network_options;
      $schema['networks']['multiple'] = true;

    }

    llog($schema);

    ?>
    <form method="POST">
    <?php
    wp_nonce_field( 'murmurations_admin_form' );

    foreach ($schema as $key => $field) {

      if($field['inputAs'] == 'none') continue;

      $name = "murmurations[$key]";

      $field_settings = array(
        'type' => $field['type'],
        'inputAs' => $field['inputAs'],
        'required' => $field['required'],
        'current_value' => $data[$key],
        'title' => $field['title'],
        'multiple' => $field['multiple'],
        'options' => $field['options'],
        'maxLength' => $field['maxLength'],
        'comment' => $field['comment'],
        'elementId' => 'murmurations_'.$key
      );

      if($field['multiple'] && $field['inputAs'] == 'text'){
        $field_settings['autocomplete'] = true;
      }

      $field = new Murmurations_Field($name, $field_settings);

      echo $field->show();
    }
    ?>
    <div id="addon-fields">
      <?php
      if($this->core->settings['enable_networks'] == 'true' && $data['networks']){
        echo $this->core->make_addon_fields($data['networks']);
      }
      ?>
    </div>
    <input type="submit" value="Save" class="button button-primary button-large">
</form>
<?php

  }



  /* Process node data saved from the admin page */
  public function process_admin_form(){

    // Load the networks list
    //TODO: This is not a good spot for this, and/or it should be conditional on difference between current and new networks data...
    if($this->core->settings['enable_networks'] == 'true'){
      $networks = $this->core->load_networks();
    }

    // Load the schema to measure against
    $schema = $this->core->load_schemas();


    $murm_post_data = $_POST['murmurations'];

    // Check the WP nonce
    check_admin_referer( 'murmurations_admin_form');

    // Form will send the attachment ID if image has been edited, needs to be converted to URL
    if($murm_post_data['logo']){
      if(is_numeric($murm_post_data['logo'])){
        $murm_post_data['logo'] = wp_get_attachment_url($murm_post_data['logo']);
      }
    }

    foreach ($murm_post_data as $key => $value) {
      $murm_post_data[$key] = trim($value);
    }

    // Trim trailing commas from nodeTypes which Awesomecomplete leaves there
    // TODO: Find a better autocomplete library...
    if(substr($murm_post_data['nodeTypes'],-1) == ','){
      $murm_post_data['nodeTypes'] = substr($murm_post_data['nodeTypes'],0,(strlen($murm_post_data['nodeTypes'])-1));
    }

    llog($murm_post_data,"POST");

    // Validate the POST data
    $val = new Murmurations_Validator();

    $invalid = false;

    foreach ($schema as $key => $field) {
      if($murm_post_data[$key]){
        if($val->isValid($field['validateAs'],$murm_post_data[$key])){
          $save_data[$key] = $murm_post_data[$key];
        }else{
          $this->set_notice('Invalid input field: '.$field['title'],'error');
          $invalid = true;
        }
      }
    }

    if(!$invalid){

      if($save_data['location']){

        llog($save_data['location'],"Geocoding");

        $geo = new Murmurations_Geocode($save_data['location']);
        if($geo->getCoordinates()){
          $save_data['lat'] = $geo->lat;
          $save_data['lon'] = $geo->lon;
        }else{
          $this->set_notice("Couldn't get coordinates for location. Try a more specific address.",'warn');
        }
      }

      $this->core->save_data($save_data);
      $this->set_notice('Murmurations data saved','success');
    }
  }



  public function set_notice($message,$type = 'notice'){

    $this->notices[] = array('message'=>$message,'type'=>$type);
    $_SESSION['murmurations_notices'] = $this->notices;

  }

  function get_notices(){
    $notices = array();
    if(count($this->notices) > 0){
      $notices = $this->notices;
    }else if(isset($_SESSION['murmurations_notices'])){
      $notices = $_SESSION['murmurations_notices'];
    }
    unset($_SESSION['murmurations_notices']);
    return $notices;
  }

  function show_notices(){
    $notices = $this->get_notices();
    foreach ($notices as $notice) {
      ?>
      <div class="notice notice-<?php echo $notice['type']; ?>">
					<p><?php echo $notice['message']; ?></p>
			</div>

      <?php
    }

  }
}
