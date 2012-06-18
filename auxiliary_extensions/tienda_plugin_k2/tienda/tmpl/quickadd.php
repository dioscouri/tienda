<?php
/**
 * @version		1.0
 * @package		K2 Tienda plugin
 * @author    	JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access'); ?>
<div class="product_buy">
	<div>
		<form action="" method="post" class="adminform" name="adminForm" enctype="multipart/form-data">

			<!--base price-->
			<span class="product_price"><?php echo TiendaHelperBase::currency($product->price); ?></span>

			<!--attribute options-->
			<div id='product_attributeoptions'>
			<?php $attributes = TiendaHelperProduct::getAttributes( $product->product_id );	foreach ($attributes as $attribute):?>
				<div class="pao" id='productattributeoption_<?php echo $attribute->productattribute_id; ?>'>
					<?php echo TiendaSelect::productattributeoptions( $attribute->productattribute_id, '', 'attribute_'.$attribute->productattribute_id );?>
				</div>
			<?php endforeach;?>
			</div>

			<!--quantity-->
			<div id='product_quantity_input'>
				<span class="title"><?php echo JText::_('COM_TIENDA_QUANTITY'); ?>:</span>
				<input type="text" name="product_qty" value="1" size="5" />
			</div>

			<input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>" size="5" />
			<?php $url = "index.php?option=com_tienda&format=raw&controller=carts&task=addToCart&productid=".$product->product_id; ?>
			<?php $onclick = 'tiendaDoTask(\''.$url.'\', \'tiendaUserShoppingCart\', document.adminForm);'; ?>
			<?php $text = "<img class='addcart' src='".Tienda::getUrl('images')."addcart.png' alt='".JText::_('COM_TIENDA_ADD_TO_CART')."' onclick=\"$onclick\" />"; ?>
			<?php $lightbox_attribs = array(); $lightbox['update'] = false; if ($lightbox_width = Tienda::getInstance()->get( 'lightbox_width' )) { $lightbox_attribs['width'] = $lightbox_width; }; ?>
			<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=carts&task=confirmAdd&tmpl=component", $text, $lightbox_attribs );  ?>
		</form>
	</div>
</div>
