<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items;?>
<table class="adminlist" style="clear: both;">
	<thead>
		<tr>
			<th style="width: 5px;"><?php echo JText::_('Num'); ?>
			</th>
			<th style="width: 50px;"><?php echo JText::_('ID'); ?>
			</th>
			<th style="text-align: left;"><?php echo JText::_('COM_TIENDA_NAME'); ?>
			</th>
			<th style="text-align: left; width: 267px;"><?php echo JText::_('COM_TIENDA_EMAIL'); ?>
			</th>
			<th style="text-align: left;"><?php echo JText::_('COM_TIENDA_DATE'); ?>
			</th>

			<th style="width: 100px;"><?php echo JText::_('Numbers of Items'); ?>
			</th>
			<th style="width: 85px;"><?php echo JText::_('Subtotal'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="20"></td>
		</tr>
	</tfoot>
	<tbody>
	<?php $i=0; $k=0; $subtotal = 0; ?>
	<?php foreach (@$items as $item) : ?>
		<tr class='row<?php echo $k; ?>'>
			<td align="center"><?php echo $i + 1; ?>
			</td>
			<td style="text-align: center;"><?php echo $item->user_id;?>
			</td>
			<td style="text-align: left;">
				<a href="index.php?option=com_tienda&view=users&task=view&id=<?php echo $item->user_id;?>">
					<?php echo $item->name; ?>
				</a>		
			</td>
			<td style="text-align: left;"><?php echo $item->email; ?>
			</td>
			<td style="text-align: left;"><?php echo JHTML::_('date', $item->last_updated, TiendaConfig::getInstance()->get('date_format')); ?>
			</td>
			<td style="text-align: center;"><?php echo $item->total_items; ?>
			</td>
			<td style="text-align: center;">
			<?php echo TiendaHelperBase::currency($item->subtotal); ?>
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
