<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items; ?>
<h2><?php echo JText::_( "Results"); ?></h2>

<table class="adminlist" style="clear: both;">
	<thead>
		<tr>
			<th style="width: 5px;">
				<?php echo JText::_("Num"); ?>
			</th>
			<th style="width: 200px;">
				<?php echo JText::_("Customer"); ?>
			</th>
			<th style="text-align: center;  width: 200px;">
				<?php echo JText::_("Total Number Of Purchases"); ?>
			</th>
			<th style="width: 150px; text-align: right;">
				<?php echo JText::_("Total Amount Spent"); ?>
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
				<a href="index.php?option=com_tienda&controller=users&view=users&task=view&id=<?php echo $item->id?> target=_blank">
					<?php echo $item->username .' [ '.$item->id.' ]'; ?>
					&nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php echo $item->email; ?>
				</a>
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
			<td colspan="10" align="center"><?php echo JText::_('No items found'); ?>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>
