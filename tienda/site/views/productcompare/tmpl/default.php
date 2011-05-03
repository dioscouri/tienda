<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $items = @$this->items;?>
<a name="tienda-compare"></a>
<h1><?php echo JText::_('Compare')?></h1>
<?php if(count($items)):?>
<div id="tiendaProductCompareScroll">
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr class="row0">
				<td valign="middle" class="first-cell center">
					<?php echo JText::_('Compare')?>
				</td>
					<?php foreach($items as $item):?>
				<td align="center" valign="top" class="border-left">
					<a title="Remove" alt="Remove" class="close-img" href="<?php echo JRoute::_('index.php?index.php?option=com_tienda&view=productcompare&task=remove&id='.$item->productcompare_id);?>">
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
			<?php if(TiendaConfig::getInstance()->get('show_srp_productcompare', '1')):?>
			<tr  class="row1">
				<td>
					<?php echo JText::_('SRP')?>
				</td>			
					<?php foreach($items as $item):?>
				<td align="center" class="border-left">
				<?php if(TiendaConfig::getInstance()->get('show_addtocart_productcompare', '1')):?>
					<div id="product_buy_<?php echo $item->product_id; ?>" class="product_buy">
						<?php echo $item->product_buy;?>
					</div>					
				<?php else:?>
				<?php echo TiendaHelperBase::currency($item->product_price); ?>	
				<?php endif;?>
					
				</td>
					<?php endforeach;?>		
			</tr>
			<?php endif?>
			<?php if(TiendaConfig::getInstance()->get('show_rating_productcompare', '1')):?>
			<tr  class="row0">
				<td>
					<?php echo JText::_('Average Customer Rating')?>
				</td>			
					<?php foreach($items as $item):?>
				<td align="center" class="border-left">
					<?php echo TiendaHelperProduct::getRatingImage( $item->product_rating ); ?>								
				</td>
					<?php endforeach;?>		
			</tr>
			<?php endif?>
			
			<?php if(TiendaConfig::getInstance()->get('show_manufacturer_productcompare', '1')):?>
			<tr  class="row1">
				<td>
					<?php echo JText::_('Manufacturer')?>
				</td>			
				<?php foreach($items as $item):?>
				<td align="center" class="border-left">
					<?php echo $item->manufacturer_name?>
				</td>
				<?php endforeach;?>		
			</tr>
			<?php endif;?>
			
			<?php if(TiendaConfig::getInstance()->get('show_model_productcompare', '1')):?>
			<tr  class="row0">
				<td>
					<?php echo JText::_('Model')?>
				</td>			
				<?php foreach($items as $item):?>
				<td align="center" class="border-left">
					<?php echo $item->product_model?>
				</td>
				<?php endforeach;?>		
			</tr>
			<?php endif;?>
			
			<?php if(TiendaConfig::getInstance()->get('show_sku_productcompare', '1')):?>
			<tr  class="row1">
				<td>
					<?php echo JText::_('SKU')?>
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
<p><?php echo JText::_('No products selected')?></p>
</div>
<?php endif;?>