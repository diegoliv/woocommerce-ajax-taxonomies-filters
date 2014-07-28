<?php
/**
 * @package   WooCommerce_ATF_Admin
 * @author    Diego de Oliveira <diego@favolla.com.br>
 * @license   GPL-2.0+
 * @link      http://favolla.com.br
 * @copyright 2014 Favolla Comunicação
 */

/**
 * @package WooCommerce_ATF_Admin
 * @author  Diego de Oliveira <diego@favolla.com.br>
 */
class WooCommerce_ATF_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = WooCommerce_ATF::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// add settings for the plugin page
		add_action( 'admin_init', array( $this, 'set_settings'), 0 );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), WooCommerce_ATF::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), WooCommerce_ATF::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'WooCommerce Ajax Taxonomies Filters', $this->plugin_slug ),
			__( 'WooCommerce Ajax Taxonomies Filters', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Register settings fields and sections for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function set_settings(){

	    global $settings;

	    // tabs array
	    $tabs = apply_filters( 'watf-settings-tabs', array(
			array(
				'group' => 'watf-general-options',
				'tab' => __( 'General Settings', $this->plugin_slug ),
			),
		) );

	    // sections array
		$sections = apply_filters( 'watf-settings-sections', array(
			array(
				'id' => 'watf-general-section',
				'title' => __( 'General Settings', $this->plugin_slug ),
				'tab' => 'watf-general-options',
			)
		) );

		// fields array
		$fields = apply_filters( 'watf-settings-fields', array(
	    	array( 
	    		'id' => 'is_ajax',
	    		'section' => 'watf-general-section',
	    		'tab' => 'watf-general-options',
	    		'label' => __( 'Use ajax filtering?', $this->plugin_slug ),
	    		'desc' => __( 'Mark this if you want to make the filtering process work with ajax.', $this->plugin_slug ),
	    		'type' => 'checkbox',
	    	),
	    	array( 
	    		'id' => 'ajax_container',
	    		'section' => 'watf-general-section',
	    		'tab' => 'watf-general-options',
	    		'label' => __( 'Products container', $this->plugin_slug ),
	    		'desc' => __( 'Insert the element with class (i.e.: ul.products) or the id (i.e.: #products) of the products list.', $this->plugin_slug ),
	    		'size' => 'regular',
	    		'type' => 'text',
	    		'default' => 'ul.products',
	    	),
	    	array( 
	    		'id' => 'count_container',
	    		'section' => 'watf-general-section',
	    		'tab' => 'watf-general-options',
	    		'label' => __( 'Product count container', $this->plugin_slug ),
	    		'desc' => __( 'Insert the element with class (i.e.: .woocommerce-result-count) or the id (i.e.: #woocommerce-result-count) of the products count container.', $this->plugin_slug ),
	    		'size' => 'regular',
	    		'type' => 'text',
	    		'default' => '.woocommerce-result-count',
	    	),
	    	array( 
	    		'id' => 'remove_styles',
	    		'section' => 'watf-general-section',
	    		'tab' => 'watf-general-options',
	    		'label' => __( 'Remove default stylesheet?', $this->plugin_slug ),
	    		'desc' => __( 'Mark this if you want to remove the default stylesheet that comes with the plugin. This way, you can have more control over the styling of the widget.', $this->plugin_slug ),
	    		'type' => 'checkbox',
	    	),
	    ) );

		// initiate the setting class and add tabs
		$settings = new WP_Settings_Wrapper( $this->plugin_slug, $tabs );

		// add settings sections
		$settings->add_sections( $sections );

		// add settings fields
	    $settings->add_fields( $fields );

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

}
