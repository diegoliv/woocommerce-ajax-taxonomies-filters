<?php
/**
 * @package   WooCommerce_TAF
 * @author    Diego de Oliveira <diego@favolla.com.br>
 * @license   GPL-2.0+
 * @link      http://favolla.com.br
 * @copyright 2014 Favolla Comunicação
 */
?>

<?php $action = $this->plugin_options['is_ajax'] ? 'method="post" action="'. $this->get_current_url() .'"' : 'method="get"'; ?>

<form class="woocommerce-atf-filters" <?php echo $action; ?>>

<?php foreach( $filters as $taxonomy => $terms ): ?>
	<div class="watf-filter watf-filter-<?php echo $taxonomy ?>" data-taxonomy="<?php echo $taxonomy ?>">
	
	<?php 

		$curr_terms = get_query_var( $taxonomy );
		$terms_list = explode( ',', $curr_terms );

		// retrieves taxonomy label and creates a dynamic filter to change taxonomy label 
		$tax = get_taxonomy( $taxonomy );

		$label = apply_filters( 'woocommerce-atf-filter-label_'. $taxonomy, $tax->labels->name );
		echo '<h4 class="watf-filter-title">'. $label .' <span class="watf-tax-count"></span></h4>';		
	
	?>

	<?php if( !empty( $terms ) ): ?>
        <ul>

            <?php foreach ( $terms as $term ): ?>
				<?php // get terms children
					$term_children = get_term_children( $term->term_id, $taxonomy );

					$checked = ( $terms_list && in_array( $term->slug, $terms_list ) ) ? 'checked="checked"' : '';
				?>

                <li class="<?php echo ( $term->parent == "0" ) ? 'taxonomy-has-children' : null; ?>">
					<input type="checkbox" name="wtaf-terms-<?php echo $taxonomy ?>[]" id="wtaf-term-<?php echo $term->term_id ?>" value="<?php echo $term->slug ?>" <?php echo $checked ?>>
					<label for="wtaf-term-<?php echo $term->term_id ?>">
					<span class="wtaf-term-label"><?php echo $term->name; ?></span>
                	<?php if( $show_count ) echo '<span class="wtaf-term-count">'. $term->count .'</span>' ?>
					</label>
				
					<?php if( !empty( $term_children ) ): ?>
						<ul>
						<?php foreach($term_children as $term_child_id): ?>
					 		<?php 
					 			$term_child = get_term_by('id', $term_child_id, $taxonomy ); 
								$checked = ( $terms_list && in_array( $term_child->slug, $terms_list ) ) ? 'checked="checked"' : '';
					 		?>
							<li>
								<input type="checkbox" name="wtaf-terms-<?php echo $taxonomy ?>[]" id="wtaf-term-<?php echo $term_child->term_id ?>" value="<?php echo $term_child->slug ?>" <?php echo $checked ?>>
								<label for="wtaf-term-<?php echo $term_child->term_id ?>">
								<span class="wtaf-term-label"><?php echo $term_child->name; ?></span>
			                	<?php if( $show_count ) echo '<span class="wtaf-term-count">'. $term_child->count .'</span>' ?>
								</label>
							</li>
					 	<?php endforeach; ?>
						</ul>
					<?php endif; ?>

                </li>
            <?php endforeach; ?>
        </ul>
	<?php endif; ?>

	</div>
<?php endforeach; ?>

	<p class="watf-form-submit">
		<button type="reset" class="reset-filters <?php echo apply_filters( 'woocommerce-atf-reset-button-classes', 'button' ) ?>"><?php _e( 'Reset Filters', $this->plugin_slug ); ?></button>
		<button type="submit" class="<?php echo apply_filters( 'woocommerce-atf-submit-button-classes', 'button' ) ?>"><?php _e( 'Filter', $this->plugin_slug ); ?></button>
	</p>

</form>