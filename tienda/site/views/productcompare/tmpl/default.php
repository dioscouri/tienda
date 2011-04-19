<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $items = @$this->items;?>
<a name="tienda-compare"></a>
<h1><?php echo JText::_('Compare')?></h1>
<?php if(count($items)):?>
<div id="tiendaCompareScroll" style="height: 100%; overflow-x: auto; overflow-y: hidden; padding-bottom: 3px; width: 100%;">
	<table class="tiendaCompareTable" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td valign="middle" rowspan="2" class="first-cell center">
					<?php echo JText::_('Compare')?>
				</td>
				<?php foreach($items as $item):?>
				<td align="center" valign="top" style="padding: 5px;">
					<a style="display: block; float: right; margin-left: -15px; padding: 2px;" title="Remove" alt="Remove" class="close-img" href="<?php echo JRoute::_('index.php?index.php?option=com_tienda&view=productcompare&task=remove&id='.$item->productcompare_id);?>">
						<img src="http://us.toshiba.com/images/showcase/ui/closeBox.gif">
					</a>
					<?php echo TiendaHelperProduct::getImage($item->product_id, '', $item->product_name); ?>
				</td>
				<?php endforeach;?>				
			</tr>	
			<tr valign="top">
				<?php foreach($items as $item):?>
				<td align="center">
					<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=products&task=view&id='. $item->product_id)?>">
					<?php echo $item->product_name?>
					</a>		
					 <div class="reset"></div>
					<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=products&task=view&id='. $item->product_id)?>">
						<?php echo JText::_('Learn More')?>
					</a>
				</td>
				<?php endforeach;?>	
			</tr>	
			<tr>
				<td>
					<?php echo JText::_('SRP')?>
				</td>			
				<?php foreach($items as $item):?>
				<td align="center">
					<?php echo TiendaHelperBase::currency($item->product_price); ?>	
				</td>
				<?php endforeach;?>		
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Average Customer Rating')?>
				</td>			
				<?php foreach($items as $item):?>
				<td align="center">
					<?php echo TiendaHelperProduct::getRatingImage( $item->product_rating ); ?>								
				</td>
				<?php endforeach;?>		
			</tr>
			<tr>
				<td>
					<?php echo JText::_('Brand')?>
				</td>			
				<?php foreach($items as $item):?>
				<td align="center">
					<?php echo $item->manufacturer_name?>
				</td>
				<?php endforeach;?>		
			</tr>
			
		</tbody>
	</table>
</div>
<?php else:?>
<div style="text-align: center;">
<p><?php echo JText::_('No products selected')?></p>
</div>
<?php endif;?>
