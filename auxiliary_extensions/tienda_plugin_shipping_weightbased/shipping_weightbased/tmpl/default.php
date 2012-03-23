	<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php $form = @$vars->form; ?>
<?php $items = @$vars->list; ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post"	name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

	<table class="adminlist" style="clear: both;">
		<thead>
			<tr>
				<th style="width: 5px;">
					<?php echo JText::_('Num'); ?>
				</th>
				<th style="width: 20px;"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" /></th>
				<th style="width: 50px;">
					<?php echo JText::_('ID'); ?>
				</th>
				<th style="text-align: left;">
					<?php echo JText::_('COM_TIENDA_NAME'); ?>
				</th>
				<th style="text-align: center; width: 100px;">
					<?php echo JText::_('Price Start'); ?>
				</th>
				<th style="text-align: center; width: 100px;">
					<?php echo JText::_('Price End'); ?>
				</th>
				<th style="width: 100px;">
					<?php echo JText::_('Tax Class'); ?>
				</th>
				<th style="width: 100px;">
					<?php echo JText::_('Enabled'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="20">&nbsp;</td>
			</tr>
		</tfoot>
		<tbody>
		<?php $i=0; $k=0; ?>
		<?php foreach (@$items as $item) : ?>
			<tr class='row<?php echo $k; ?>'>
				<td align="center"><?php echo $i + 1; ?>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'shipping_method_weightbased_id' ); ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>"><?php echo $item->shipping_method_weightbased_id; ?> </a>
				</td>
				<td style="text-align: left;">
					<a href="<?php echo $item->link; ?>"><?php echo $item->shipping_method_weightbased_name; ?> </a>
					<?php
						Tienda::load( 'TiendaUrl', 'library.url' );
						Tienda::load( 'TiendaHelperShipping', 'helpers.shipping' );
						$id = JRequest::getInt('id', '0');
					?>
					<span style="float: right;">
						[<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=shipping&task=view&id={$id}&shippingTask=setRates&tmpl=component&sid={$item->shipping_method_weightbased_id}",JText::_('Set Rates') ); ?>]
					</span>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaHelperBase::currency( $item->shipping_method_price_start ); ?>
				</td>
				<td style="text-align: center;">
					<?php echo ( float )$item->shipping_method_price_end ?  TiendaHelperBase::currency( $item->shipping_method_price_end ) : JText::_('Infinity'); ?>
				</td>
				<td style="text-align: center;"><?php echo $item->tax_class_name; ?>
				</td>
				<td style="text-align: center;"><?php echo TiendaGrid::boolean( $item->shipping_method_weightbased_enabled ); ?>
				</td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>

			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center"><?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="sid" value=" <?php echo $vars->sid; ?>" />
	<input type="hidden" name="shippingTask" value="_default" />
	<input type="hidden" name="task" value="view" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order"	value="<?php echo @$state->order; ?>" />
	<input type="hidden"	name="filter_direction" value="<?php echo @$state->direction; ?>" />
</form>
