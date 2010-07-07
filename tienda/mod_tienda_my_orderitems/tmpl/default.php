<?php
/**
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// Add CSS
$document->addStyleSheet( JURI::root(true).'/modules/mod_tienda_my_orderitems/tmpl/mod_tienda_my_orderitems.css');

$resize = false;
$options = array();
if ($params->get('display_image_width'))
{
	$options['width'] = $params->get('display_image_width');
}
if ($params->get('display_image_height'))
{
	$options['height'] = $params->get('display_image_height');
}

if (!empty($products))
{
    // Loop through the products to display
    foreach ($products as $product) : ?>
        <div class="mod_tienda_my_orderitems_item">
            <?php if ($params->get('display_image')) : ?>
                <div class="mod_tienda_my_orderitems_item_image">
                <a href="<?php echo JRoute::_( $product->link ); ?>">
                <?php echo TiendaHelperProduct::getImage($product->product_id, 'id', $product->product_name, 'thumb', false, $resize, $options); ?>
                </a>
                </div>
            <?php endif; ?>    
            <span class="mod_tienda_my_orderitems_item_name"><a href="<?php echo JRoute::_( $product->link ); ?>"><?php echo $product->product_name; ?></a></span>        
        </div>
	<?php endforeach;
}  
    elseif ($display_null == '1') 
{
    echo JText::_( $null_text );
}