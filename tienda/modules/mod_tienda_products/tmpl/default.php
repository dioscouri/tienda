<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// Add CSS
$document->addStyleSheet( JURI::root(true).'/modules/mod_tienda_products/tmpl/mod_tienda_products.css');

$resize = false;
$options = array();
if ($params->get('display_image_width', '') != '')
{
	$options['width'] = $params->get('display_image_width');
}
if ($params->get('display_image_height', '') != '')
{
	$options['height'] = $params->get('display_image_height');
}

if ($num > 0 && @$products)
{
	$k = 0;
	echo '<div class="tienda_products_'.$params->get('display_style','flat').'">';
	echo '<div class="tienda_product_box">';
    // Loop through the products to display
    foreach (@$products as $product) : 
    	$k++;
    ?>

		<div class="tienda_product_item <?php echo $params->get('moduleclass_sfx');?><?php if ($params->get('display_style','flat') == 'grid') echo ' grid' .$params->get('display_grid_items' ,'3'); ?>">
		<div class="inner">
        <?php if ($params->get('display_title','1') != '0') : ?>
	<h4 class="product_title"><a href="<?php echo JRoute::_( $product->link."&Itemid=".$product->itemid ); ?>"><?php echo $product->product_name; ?></a></h4>
		<?php endif; ?>
		<?php if ($params->get('display_image','1') != '0') : ?>
			<?php if ($params->get('display_image_link','1') != '0') : ?>
				<p class="product_image"><a href="<?php echo JRoute::_( $product->link."&Itemid=".$product->itemid ); ?>">
				<?php echo TiendaHelperProduct::getImage($product->product_id, 'id', $product->product_name, 'thumb', false, $resize, $options); ?>
				</a></p>
			<?php else : ?>
				<p class="product_image"><?php echo TiendaHelperProduct::getImage($product->product_id, 'id', $product->product_name, 'thumb', false, $resize, $options); ?></p>
			<?php endif; ?>
		<?php endif; ?>

        <?php if ($params->get('display_price','1') != '0') : ?><p class="product_price"><?php echo TiendaHelperProduct::dispayPriceWithTax($product->price, $product->tax, Tienda::getInstance()->get('display_prices_with_tax')) ?></p><?php endif; ?>

		<?php if ($params->get('display_description','1') != '0' && $product->product_description_short != null) : ?><p class="product_description"><?php echo $product->product_description_short ?></p><?php endif; ?>
		</div>
		</div>
		<?php if ($params->get('display_style','flat') == 'grid' && $params->get('display_grid_items' ,'3') == $k): ?>
			<?php echo '</div>'; ?>
			<?php echo '<div class="tienda_product_box">'; ?>
		<?php $k = 0; endif; ?>
		
		<?php  endforeach;
	echo '</div>';
	echo '</div>';

}
    elseif ($display_null == '1')
{
    $text = JText::_( $null_text );
    echo $text;
}
?> 
<script type="text/javascript"> 
	window.addEvent('domready', function() {
		$$('.tienda_product_box .tienda_product_item:last-child').addClass('right');
	});
</script>