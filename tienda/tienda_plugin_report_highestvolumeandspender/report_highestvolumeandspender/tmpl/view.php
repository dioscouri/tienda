<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items; ?>

<h2><?php echo JText::_('COM_TIENDA_RESULTS'); ?></h2>

<table class="adminlist" style="clear: both;">
	<thead>
		<tr>
			<th style="width: 10px;">
				<?php echo JText::_('COM_TIENDA_NUM'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_TIENDA_CUSTOMER'); ?>
			</th>
			<th style="text-align: center;  width: 200px;">
				<?php echo JText::_('COM_TIENDA_TOTAL_NUMBER_OF_ORDERS'); ?>
			</th>
			<th style="text-align: center;  width: 200px;">
				<?php echo JText::_('COM_TIENDA_TOTAL_NUMBER_OF_PURCHASES'); ?>
			</th>
			<th style="width: 200px;; text-align: right;">
				<?php echo JText::_('COM_TIENDA_TOTAL_AMOUNT_SPENT'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="20"></td>
		</tr>
	</tfoot>
	<tbody>
	<?php $i=0; $k=0; ?>
	<?php foreach (@$items as $item) : ?>
		<tr class='row<?php echo $k; ?>'>
			<td align="center">
				<?php echo $i + 1; ?>
			</td>
			<td style="text-align: left;">	
				<a href="index.php?option=com_tienda&view=users&task=view&id=<?php echo $item->user_id;?>">
					<?php echo $item->billing_last_name." ".$item->billing_middle_name." ".$item->billing_middle_name;?>
					[<?php echo $item->user_id;?>]
					&nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php echo !empty($item->email) ? $item->email : $item->user_email; ?>
				</a>	
			</td>
			<td style="text-align: center;">
				<?php echo empty($item->volume) ? 0 : $item->number_of_orders; ?>
			</td>
			<td style="text-align: center;">
				<?php echo empty($item->volume) ? 0 : $item->volume; ?>
			</td>
			<td style="text-align: right;">
				<?php echo TiendaHelperBase::currency( $item->spent); ?>
			</td>

		</tr>
		<?php ++$i; $k = (1 - $k); ?>
		<?php endforeach; ?>

		<?php if (!count(@$items)) : ?>
		<tr>
			<td colspan="10" align="center"><?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>
