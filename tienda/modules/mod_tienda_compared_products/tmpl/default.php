<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda_compared_products.css', 'modules/mod_tienda_compared_products/tmpl/'); ?>
<?php $items = $helper -> getComparedProducts(); ?>

	<?php if(count($items)){?>
<div id="tiendaComparedProducts">
	<ul>	
		<?php foreach($items as $item):?>
		<li>
			<a href="<?php echo JRoute::_($item->link)?>">
				<?php echo $item -> product_name; ?>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<div class="compared-right">	
	<a href="<?php echo JRoute::_("index.php?option=com_tienda&view=productcompare"); ?>" title="<?php echo JText::_('COM_TIENDA_COMPARED_PRODUCTS')?>"><?php echo JText::_('COM_TIENDA_COMPARE_NOW'); ?></a>
</div>
	<?php }else{ ?>
		<?php echo JText::_('COM_TIENDA_NO_COMPARED_PRODUCTS'); ?>
	<?php } ?>
