<?php defined('_JEXEC') 	or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$items = @$this->items;

$show_manufacturer = TiendaConfig::getInstance()->get('show_manufacturer_productcompare', '1');
$show_srp = TiendaConfig::getInstance()->get('show_srp_productcompare', '1');
$show_addtocart = TiendaConfig::getInstance()->get('show_addtocart_productcompare', '1');
$show_rating = TiendaConfig::getInstance()->get('show_rating_productcompare', '1');
$show_model = TiendaConfig::getInstance()->get('show_model_productcompare', '1');
$show_sku = TiendaConfig::getInstance()->get('show_sku_productcompare', '1');
?>
<a name="tienda-compare"></a>
<h1><?php echo JText::_('COM_TIENDA_COMPARE')?></h1>
<?php if(count($items)):?>
<div id="tiendaProductCompareScroll">
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr class="row0">
				<td valign="middle" class="first-cell center">
					<?php echo JText::_('COM_TIENDA_COMPARE')?>
				</td>
					<?php foreach($items as $item):?>
				<td align="center" valign="top" class="border-left">
					<a title="<?php echo JText::_('COM_TIENDA_REMOVE_PRODUCT_COMPARISON'); ?>" class="close-img" href="<?php echo JRoute::_('index.php?index.php?option=com_tienda&view=productcompare&task=remove&id='.$item->productcompare_id);?>">
						<img src="<?php echo Tienda::getURL('images');?>closebox.gif">
					</a>
						<?php echo TiendaHelperProduct::getImage($item->product_id, '', $item->product_name); ?>
				</td>
						<?php endforeach;?>
			</tr>	
			<tr valign="top"  class="row0">
				<td></td>
					<?php foreach($items as $item):?>
				<td align="center" class="border-left">
					<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=products&task=view&id='. $item->product_id)?>">
					<?php echo $item->product_name?>
					</a>		
					 <div class="reset"></div>
					<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=products&task=view&id='. $item->product_id)?>">
						<span class="arrow">>></span> <?php echo JText::_('Learn More')?> <span class="arrow"><<</span>
					</a>
				</td>
					<?php endforeach;?>	
			</tr>	
			<?php if($show_srp ):?>
			<tr  class="row1">
				<td>
					<?php echo JText::_('COM_TIENDA_SRP')?>
				</td>			
					<?php foreach($items as $item):?>
				<td align="center" class="border-left">
				<?php if( $show_addtocart ):?>
					<div id="product_buy_<?php echo $item->product_id; ?>" class="product_buy">
						<?php echo TiendaHelperProduct::getCartButton( $item->product_id, 'product_buy', array() );?>
					</div>					
				<?php else:?>
				<?php echo TiendaHelperBase::currency($item->product_price); ?>	
				<?php endif;?>
					
				</td>
					<?php endforeach;?>		
			</tr>
			<?php endif?>
			<?php if( $show_rating ):?>
			<tr  class="row0">
				<td>
					<?php echo JText::_('COM_TIENDA_AVERAGE_CUSTOMER_RATING')?>
				</td>			
					<?php foreach($items as $item):?>
				<td align="center" class="border-left">
					<?php echo TiendaHelperProduct::getRatingImage( $this, $item->product_rating ); ?>								
				</td>
					<?php endforeach;?>		
			</tr>
			<?php endif?>
			
			<?php if( $show_manufacturer ):?>
			<tr  class="row1">
				<td>
					<?php echo JText::_('COM_TIENDA_MANUFACTURER')?>
				</td>			
				<?php foreach($items as $item):?>
				<td align="center" class="border-left">
					<?php echo $item->manufacturer_name?>
				</td>
				<?php endforeach;?>		
			</tr>
			<?php endif;?>
			
			<?php if( $show_model ):?>
			<tr  class="row0">
				<td>
					<?php echo JText::_('COM_TIENDA_MODEL')?>
				</td>			
				<?php foreach($items as $item):?>
				<td align="center" class="border-left">
					<?php echo $item->product_model?>
				</td>
				<?php endforeach;?>		
			</tr>
			<?php endif;?>
			
			<?php if( $show_sku ):?>
			<tr  class="row1">
				<td>
					<?php echo JText::_('COM_TIENDA_SKU')?>
				</td>			
				<?php foreach($items as $item):?>
				<td align="center" class="border-left">
					<?php echo $item->product_sku?>
				</td>
				<?php endforeach;?>		
			</tr>
			<?php endif;?>	
		</tbody>
	</table>
</div>
<?php else:?>
<div style="text-align: center;">
<p><?php echo JText::_('COM_TIENDA_NO_PRODUCTS_SELECTED')?></p>
</div>
<?php endif;?>