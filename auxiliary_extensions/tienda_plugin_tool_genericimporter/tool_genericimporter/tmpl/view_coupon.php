<?php	defined('_JEXEC') or die('Restricted access');?>
<?php	$items = @$vars->results;?>
<table class="adminlist" style="clear: both;">
	<thead>
		<tr>
			<th style="width: 5px;">
			<?php	echo JText::_('NUM');?>
			</th>
			<th style="text-align: left;">
			<?php	echo JText::_('TITLE');?>
			</th>
			<th style="width: 50px;">
			<?php	echo JText::_('AFFECTED ROWS');?>
			</th>
			<th>
			<?php	echo JText::_('ERRORS');?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="20">

			</td>
		</tr>
	</tfoot>
	<tbody>
		<?php	$i = 0;
			$k = 0;?>
		<?php foreach (@$items as $item) : ?>
		<tr class='row<?php	echo $k;?>'>
			<td align="center">
			<?php	echo $i + 1;?>
			</td>
			<td style="text-align: left;">
			<?php	echo JText::_($item->title);?>
			</td>
			<td style="text-align: center;">
			<?php	echo $item->affectedRows;?>
			</td>
			<td style="text-align: center;">
			<ul>
				<?php  foreach($item->errors as $error ) : ?>
				<?php if(!empty($error)) : ?>
				<li>
					<?php	echo $error;?>
				</li>
				<?php	endif;?>
				<?php	endforeach;?>
			</ul>
			</td>
		</tr>
		<?php	++$i;
			$k = (1 - $k);?>
		<?php	endforeach;?>

		<?php if (!count(@$items)) : ?>
		<tr>
			<td colspan="10" align="center">
			<?php	echo JText::_('COM_TIENDA_NO_ITEMS_FOUND');?>
			</td>
		</tr>
		<?php endif;?>
	</tbody>
</table>