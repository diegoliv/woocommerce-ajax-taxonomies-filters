<?php
/**
 * @package   WooCommerce_ATF
 * @author    Diego de Oliveira <diego@favolla.com.br>
 * @license   GPL-2.0+
 * @link      http://favolla.com.br
 * @copyright 2014 Favolla Comunicação
 */
?>

<?php global $settings; // we'll need this below ?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

    <?php $settings->make_tabs(); ?>

	<form action="options.php" method="POST">

        <?php $settings->make_pages(); ?>

	    <?php submit_button(null, 'primary', null, true, array( 'id' => 'submit')); ?>

    </form>

</div>
