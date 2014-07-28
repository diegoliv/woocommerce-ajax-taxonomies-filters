<?php
/**
 * @package   WooCommerce_ATF
 * @author    Diego de Oliveira <diego@favolla.com.br>
 * @license   GPL-2.0+
 * @link      http://favolla.com.br
 * @copyright 2014 Diego de Oliveira
 */

/**
 * @package WooCommerce_ATF
 * @author  Diego de Oliveira <diego@favolla.com.br>
 */
class WooCommerce_ATF {

	/**
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '0.0.1';

	/**
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'woocommerce-atf';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// filter product page based on selected terms
		// add_action( 'pre_get_posts', array( $this, 'filter_products' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
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
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	public function filter_products( $query ){

	    if ( is_admin() || ! $query->is_main_query() )
    	    return;

		$request = $_GET;
		$term_list = array();

		if ( $request && is_post_type_archive( 'product' ) ){

			// $tax_query = array(
			// 	'relation' => 'AND',
			// );

			// $query->tax_query->queries['relation'] = 'AND';

			foreach( $request as $tax => $string ){

				$terms = explode(',', $string );

				// foreach( $terms as $term_id ){
				// 	$term = get_term_by( 'id', $term_id, $tax );
				// 	array_push( $term_list, $term->slug );
				// }

				$array = array(
					'taxonomy' => $tax,
					'terms' => $terms,
					'field' => 'slug',
					'operator'
				);

				// array_push( $tax_query, $array );
				$query->tax_query->queries[] = $array;
			}

			$query->query_vars['tax_query'] = $query->tax_query->queries;

	    	// $query->init();

		    // // reset query vars here. eg:
		    // $query->set('post_type', 'product');

		    // re-run validation and conditional set up.
			// $query->set( 'tax_query', $tax_query );

		    // $query->parse_query();
		}

	}

}
