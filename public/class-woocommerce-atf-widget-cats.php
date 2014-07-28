<?php
/**
 * @package   WooCommerce_ATF
 * @author    Diego de Oliveira <diego@favolla.com.br>
 * @license   GPL-2.0+
 * @link      http://favolla.com.br
 * @copyright 2014 Favolla Comunicação
 */

// Creating the widget
class WATF_Widget_Cats extends WP_Widget {

    function __construct() {

        $plugin = WooCommerce_ATF::get_instance();
        $this->plugin_slug = $plugin->get_plugin_slug();
        $this->plugin_options = get_option( 'watf-general-options' );

    	parent::__construct(
    		// Base ID of your widget
    		'watf_widget_cats', 

    		// Widget name will appear in UI
    		__( 'WooCommerce - Ajax Filter (Taxonomies like Categories)', 'woocommerce-atf' ), 

    		// Widget description
    		array( 'description' => __( 'Generates a list of hierarchic taxonomies with ajax filtering.', 'woocommerce-atf' ), ) 
    	);

        // Load public-facing style sheet and JavaScript.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_print_styles', array( $this, 'do_styles' ) );
        add_action( 'wp_print_scripts', array( $this, 'do_scripts' ) );
    }

    /**
     * Register public-facing style sheet.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_register_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), WooCommerce_ATF::VERSION );
    }

    /**
     * Register public-facing JavaScript files.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $ajax = $this->plugin_options['is_ajax'] ? '-ajax' : '';
        wp_register_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public'. $ajax .'.js', __FILE__ ), array( 'jquery' ), WooCommerce_ATF::VERSION, true );
    }

    /**
     * Enqueue public-facing style sheet.
     *
     * @since    1.0.0
     */
    public function do_styles() {
    
        if ( is_active_widget( false, false, $this->id_base, true ) && !isset( $this->plugin_options['remove_styles'] ) ) {
            wp_enqueue_style( $this->plugin_slug . '-plugin-styles' );
        }

    }

    /**
     * Enqueue public-facing JavaScript files.
     *
     * @since    1.0.0
     */
    public function do_scripts() {

        if ( is_active_widget( false, false, $this->id_base, true ) ) {
            wp_enqueue_script( $this->plugin_slug . '-plugin-script' );

            if( $this->plugin_options['is_ajax'] ){

                $watf_shop_container = $this->plugin_options['ajax_container'] ? $this->plugin_options['ajax_container'] : 'ul.products';
                $watf_count_container = $this->plugin_options['count_container'] ? $this->plugin_options['count_container'] : '.woocommerce-result-count';

                $args = array(
                    'wc_shop_container' => apply_filters( 'watf_wc_shop_container', $watf_shop_container ),
                    'wc_count_container' => apply_filters( 'watf_wc_count_container', $watf_count_container ),
                    'loading_text' => __( 'Filtering Results...', $this->plugin_slug ),
                    'error_text' => __( 'Sorry, an error ocurred while filtering products. Please, try again.', $this->plugin_slug ),
                );

                wp_localize_script( $this->plugin_slug . '-plugin-script', 'watf_vars', $args );
            }

        }

    }

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', $instance['title'] );
		$hide_empty = ( isset( $instance['hide_empty'] ) ) ? true : false;
		$show_count = ( isset( $instance['show_count'] ) ) ? true : false;
        $order_options = ( isset( $instance['order_options'] ) ) ? explode( '/', $instance['order_options'] ) : array( '', '' );

        $get_terms_args = array(
            'hide_empty' => $hide_empty,
            'orderby'    => ( isset( $order_options[0] ) ) ? $order_options[0] : 'name',
            'order'      => ( isset( $order_options[1] ) ) ? $order_options[1] : 'ASC',
            'number'     => ( isset( $instance['max_terms'] )) ? $instance['max_terms'] : '',
            'exclude'    => ( isset( $instance['exclude'] )) ? $instance['exclude'] : '',
            'include'    => ( isset( $instance['include'] )) ? $instance['include'] : '',          
            'show_count' => ( isset( $instance['show_count'] )) ? $instance['show_count'] : null,          
            'pad_counts' => true,
            'parent'     => 0
        );

        $terms_raw = get_terms( $instance['selected_taxonomies'], $get_terms_args );

        if ( empty( $terms_raw ) && isset( $instance['hide_widget_empty'] ) )
            return;

        $filters = $this->order_terms_by_taxonomy( $terms_raw );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];

		if ( ! empty( $title ) ){
			echo $args['before_title'] . $title . $args['after_title'];
		}

		include_once( 'views/widget-ui.php' );

   		echo $args['after_widget'];

	}
		
	// Widget Backend
	public function form( $instance ) {

		$field_data = array(
            'title' => array(
                'id'    => $this->get_field_id('title'),
                'name'  => $this->get_field_name('title'),
                'value' => ( isset( $instance['title'] ) ) ? $instance['title'] : __( 'New Title', 'woocommerce-atf' )
            ),
            'taxonomies' => array(
                'name'   => $this->get_field_name( 'selected_taxonomies' ),
                'value'  => ( isset( $instance['selected_taxonomies'] ) ) ? $instance['selected_taxonomies'] : ''
            ),
            'max_terms' => array(
                'id'    => $this->get_field_id( 'max_terms' ),
                'name'  => $this->get_field_name( 'max_terms' ),
                'value' => ( isset( $instance['max_terms'] ) ) ? $instance['max_terms'] : ''
            ),
            'hide_widget_empty' => array(
                'id'    => $this->get_field_id( 'hide_widget_empty' ),
                'name'  => $this->get_field_name( 'hide_widget_empty' ),
                'value' => ( isset( $instance['hide_widget_empty'] ) ) ? 'true' : ''
            ),
            'hide_empty' => array(
                'id'    => $this->get_field_id( 'hide_empty' ),
                'name'  => $this->get_field_name( 'hide_empty' ),
                'value' => ( isset( $instance['hide_empty'] ) ) ? 'true' : ''
            ),
            'order_options' => array(
                'id'    => $this->get_field_id( 'order_options' ),
                'name'  => $this->get_field_name( 'order_options' ),
                'value' => ( isset( $instance['order_options'] ) ) ? $instance['order_options'] : 'name'
            ),
            'exclude' => array(
                'id'    => $this->get_field_id( 'exclude' ),
                'name'  => $this->get_field_name( 'exclude' ),
                'value' => ( isset( $instance['exclude'] ) ) ? $instance['exclude'] : ''
            ),
            'include' => array(
                'id'    => $this->get_field_id( 'include' ),
                'name'  => $this->get_field_name( 'include' ),
                'value' => ( isset( $instance['include'] ) ) ? $instance['include'] : ''
            ),
            'show_count' => array(
                'id'    => $this->get_field_id( 'show_count' ),
                'name'  => $this->get_field_name( 'show_count' ),
                'value' => ( isset( $instance['show_count'] ) ) ? 'true' : ''
            )
        );

        $taxonomies = get_object_taxonomies( 'product', 'objects' );
		
		// Widget admin form
		?>

        <p>
            <label for="<?php echo $field_data['title']['id']; ?>"><?php _e( 'Title:', 'woocommerce-atf' ); ?></label>
            <input class="widefat" id="<?php echo $field_data['title']['id']; ?>" name="<?php echo $field_data['title']['name']; ?>" type="text" value="<?php echo esc_attr( $field_data['title']['value'] ); ?>">
        </p>


        <p style='font-weight: bold;'><?php _e( 'Options:', 'woocommerce-atf' ); ?></p>

        <p>
            <input id="<?php echo $field_data['hide_widget_empty']['id']; ?>" name="<?php echo $field_data['hide_widget_empty']['name']; ?>" type="checkbox" value="true" <?php checked( $field_data['hide_widget_empty']['value'], 'true' ); ?>>
            <label for="<?php echo $field_data['hide_widget_empty']['id']; ?>"><?php _e( 'Hide Widget if there are no terms to be displayed?', 'woocommerce-atf' ); ?></label>
        </p>

        <p>
            <input id="<?php echo $field_data['hide_empty']['id']; ?>" name="<?php echo $field_data['hide_empty']['name']; ?>" type="checkbox" value="true" <?php checked( $field_data['hide_empty']['value'], 'true' ); ?>>
            <label for="<?php echo $field_data['hide_empty']['id']; ?>"><?php _e( 'Hide terms that have no related posts?', 'woocommerce-atf' ); ?></label>
        </p>

        <p>
            <input id="<?php echo $field_data['show_count']['id']; ?>" name="<?php echo $field_data['show_count']['name']; ?>" type="checkbox" value="true" <?php checked( $field_data['show_count']['value'], 'true' ); ?>>
            <label for="<?php echo $field_data['show_count']['id']; ?>"><?php _e( 'Show term count?', 'woocommerce-atf', 'woocommerce-atf' ); ?></label>
        </p>

        <p>
            <label for="<?php echo $field_data['order_options']['id']; ?>"><?php _e( 'Order Terms By:', 'woocommerce-atf' ); ?></label><br>
            <select id="<?php echo $field_data['order_options']['id']; ?>" name="<?php echo $field_data['order_options']['name']; ?>">
                <option value="id/ASC" <?php selected( $field_data['order_options']['value'], 'id/ASC' ); ?>><?php _e( 'ID Ascending', 'woocommerce-atf' ) ?></option>
                <option value="id/DESC" <?php selected( $field_data['order_options']['value'], 'id/DESC' ); ?>><?php _e( 'ID Descending', 'woocommerce-atf' ) ?></option>
                <option value="count/ASC" <?php selected( $field_data['order_options']['value'], 'count/ASC' ); ?>><?php _e( 'Count Ascending', 'woocommerce-atf' ) ?></option>
                <option value="count/DESC" <?php selected( $field_data['order_options']['value'], 'count/DESC' ); ?>><?php _e( 'Count Descending', 'woocommerce-atf' ) ?></option>
                <option value="name/ASC" <?php selected( $field_data['order_options']['value'], 'name/ASC' ); ?>><?php _e( 'Name Ascending', 'woocommerce-atf' ) ?></option>
                <option value="name/DESC" <?php selected( $field_data['order_options']['value'], 'name/DESC' ); ?>><?php _e( 'Name Descending', 'woocommerce-atf' ) ?></option>               
                <option value="slug/ASC" <?php selected( $field_data['order_options']['value'], 'slug/ASC' ); ?>><?php _e( 'Slug Ascending', 'woocommerce-atf' ) ?></option>
                <option value="slug/DESC" <?php selected( $field_data['order_options']['value'], 'slug/DESC' ); ?>><?php _e( 'Slug Descending', 'woocommerce-atf' ) ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo $field_data['max_terms']['id']; ?>"><?php _e('Maximum Number Of Terms To Return:'); ?></label>
            <input class="widefat" id="<?php echo $field_data['max_terms']['id']; ?>" name="<?php echo $field_data['max_terms']['name']; ?>" type="text" value="<?php echo esc_attr($field_data['max_terms']['value']); ?>" placeholder="Keep Empty To Display All">
        </p>

        <p>
            <label for="<?php echo $field_data['exclude']['id']; ?>"><?php _e('Ids To Exclude From Being Displayed:'); ?></label>
            <input class="widefat" id="<?php echo $field_data['exclude']['id']; ?>" name="<?php echo $field_data['exclude']['name']; ?>" type="text" value="<?php echo esc_attr($field_data['exclude']['value']); ?>" placeholder="Separate ids with a comma ','">
        </p>

        <p>
            <label for="<?php echo $field_data['include']['id']; ?>"><?php _e('Only Display Terms With The Following Ids:'); ?></label>
            <input class="widefat" id="<?php echo $field_data['include']['id']; ?>" name="<?php echo $field_data['include']['name']; ?>" type="text" value="<?php echo esc_attr($field_data['include']['value']); ?>" placeholder="Separate ids with a comma ','">
        </p>


        <p style='font-weight: bold;'><?php _e( 'Selected Taxonomy:', 'woocommerce-atf' ); ?></p>
	
		<p>
            <?php foreach($taxonomies as $taxonomy): ?>
                <p>
                    <input id="<?php echo $taxonomy->name; ?>" name="<?php echo $field_data['taxonomies']['name']; ?>[]" type="checkbox" value="<?php echo $taxonomy->name; ?>" <?php echo $this->is_taxonomy_checked( $field_data['taxonomies']['value'], $taxonomy->name ); ?>>
                    <label for="<?php echo $taxonomy->name; ?>"><?php echo $taxonomy->labels->name; ?></label>
                </p>
            <?php endforeach; ?>
		</p>

	<?php 
	}
	
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {

        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['hide_widget_empty'] = $new_instance['hide_widget_empty'];
        $instance['hide_empty']        = $new_instance['hide_empty'];
        $instance['show_count']        = $new_instance['show_count'];
        $instance['order_options']     = $new_instance['order_options'];
        $instance['max_terms']         = $new_instance['max_terms'];
        $instance['exclude']           = $new_instance['exclude'];
        $instance['include']           = $new_instance['include'];
        $instance['selected_taxonomies'] = $new_instance['selected_taxonomies'];

        return $instance;
	}

    public function is_taxonomy_checked( $custom_taxonomies_checked, $taxonomy_name ){
        if ( ! is_array( $custom_taxonomies_checked ) )
            return checked( $custom_taxonomies_checked, $taxonomy_name );

        if ( in_array( $taxonomy_name, $custom_taxonomies_checked ) )
            return 'checked="checked"';
    }

    public function order_terms_by_taxonomy( $terms ){

    	$taxonomies = array();

    	// build taxonomies array
    	foreach ( $terms as $term ) {
    		$taxonomies[ $term->taxonomy ][] = $term;
    	}

    	return $taxonomies;

    }

    public function get_current_url(){
        global $wp;
        $current_url = trailingslashit( home_url( add_query_arg( array(),$wp->request ) ) );

        return $current_url;
    }

} // Class ends here

// Register and load the widget
function watf_load_widget_cats() {
	register_widget( 'WATF_Widget_Cats' );
}

add_action( 'widgets_init', 'watf_load_widget_cats' );