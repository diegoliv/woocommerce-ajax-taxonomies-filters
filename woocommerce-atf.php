<?php
/**
 * @package   WooCommerce_ATF
 * @author    Diego de Oliveira <diego@favolla.com.br>
 * @license   GPL-2.0+
 * @link      http://favolla.com.br
 * @copyright 2014 Favolla Comunicação
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Ajax Taxonomies Filters
 * Plugin URI:        http://favolla.com.br
 * Description:       Creates Widgets for taxonomies with ajax filtering for the products archive page.
 * Version:           0.0.1
 * Author:            Diego de Oliveira
 * Author URI:        http://favolla.com.br
 * Text Domain:       woocommerce-atf
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/diegoliv/woocommerce-ajax-taxonomies-filters
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

	require_once( plugin_dir_path( __FILE__ ) . 'public/class-woocommerce-atf.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'public/class-woocommerce-atf-widget-cats.php' );

	add_action( 'plugins_loaded', array( 'WooCommerce_ATF', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

	if ( is_admin() ) {

		require_once( plugin_dir_path( __FILE__ ) . 'admin/class-woocommerce-atf-admin.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/settings-api-wrapper.php' );
		add_action( 'plugins_loaded', array( 'WooCommerce_ATF_Admin', 'get_instance' ) );

	}

}