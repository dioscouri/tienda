<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $form = @$this->form2; ?>
<?php $row = @$this->row; ?>
<?php $items = @$this->items; ?>
<?php $baseLink = $this->baseLink; ?>

<h3>
<?php echo JText::_('COM_TIENDA_SET_RATES_FOR'); ?> :
	<?php echo $row->shipping_method_weightbased_name; ?>
</h3>

<div class="note" style="width: 95%; text-align: center; margin-left: auto; margin-right: auto;">
	<?php echo JText::_('COM_TIENDA_BE_SURE_TO_SAVE_YOUR_WORK'); ?>:
	<button onclick="document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.getElementById('shippingTask').value='saverates'; document.adminForm.submit();">
		<?php echo JText::_('COM_TIENDA_SAVE_CHANGES'); ?>
	</button>
</div>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">
	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

	<table>
		<tr>
			<td align="left" width="100%"></td>
			<td nowrap="nowrap">
				<table class="adminlist">
					<thead>
						<tr>
							<th></th>
							<th><?php echo JText::_('COM_TIENDA_GEOZONE'); ?></th>
							<th><?php echo JText::_('COM_TIENDA_BASE_PRICE'); ?></th>
							<th><?php echo JText::_('Price Step'); ?></th>
							<th><?php echo JText::_('COM_TIENDA_WEIGHT_RANGE'); ?></th>
							<th><?php echo JText::_('Weight Step'); ?></th>
							<th><?php echo JText::_('COM_TIENDA_HANDLING_FEE'); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<?php echo JText::_('COM_TIENDA_COMPLETE_THIS_FORM_TO_ADD_A_NEW_RATE'); ?>:
							</td>
							<td>
								<?php echo TiendaSelect::geozone("", "geozone_id"); ?>
								<input type="hidden" name="shipping_method_weightbased_id" value="<?php echo $row->shipping_method_weightbased_id; ?>" />
							</td>
							<td>
								<input id="base_price" name="base_price" value="" />
							</td>
							<td>
								<input id="price_step" name="price_step" value="" />
							</td>
							<td>
								<input id="weight_start" name="weight_start" value="" size="5" /> <?php echo JText::_('COM_TIENDA_TO'); ?>
								<input id="weight_end" name="weight_end" value="" size="5" />
							</td>
							<td>
								<input id="weight_step_size" name="weight_step_size" value="" />
							</td>
							<td>
								<input id="shipping_handling" name="shipping_handling" value="" />
							</td>
							<td>
								<input type="button" onclick="document.getElementById('shippingTask').value='createrate'; document.adminForm.submit();" value="<?php echo JText::_('COM_TIENDA_CREATE_RATE'); ?>" class="button" />
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</table>

	<table class="adminlist" style="clear: both;">
		<thead>
			<tr>
				<th style="width: 20px;"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
				</th>
				<th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'Geo Zone', "tbl.geozone_id", @$state->direction, @$state->order ); ?>
				</th>
				<th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'Base Price', "tbl.base_price", @$state->direction, @$state->order ); ?>
				</th>
				<th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'Price Step', "tbl.price_step", @$state->direction, @$state->order ); ?>
				</th>
				<th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'Weight Range', "tbl.weight_start", @$state->direction, @$state->order ); ?>
				</th>
				<th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'Weight Step', "tbl.weight_step", @$state->direction, @$state->order ); ?>
				</th>
				<th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'Handling Fee', "tbl.shipping_handling", @$state->direction, @$state->order ); ?>
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php $i=0; $k=0; ?>
		<?php foreach (@$items as $item) : ?>
			<tr class='row<?php echo $k; ?>'>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'shipping_rate_weightbased_id' ); ?>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaSelect::geozone($item->geozone_id, "geozones[{$item->shipping_rate_weightbased_id}]"); ?>
				</td>
				<td style="text-align: center;">
					<input type="text" name="base_prices[<?php echo $item->shipping_rate_weightbased_id; ?>]" value="<?php echo $item->base_price; ?>" />
				</td>
				<td style="text-align: center;">
					<input type="text" name="price_steps[<?php echo $item->shipping_rate_weightbased_id; ?>]" value="<?php echo $item->price_step; ?>" />
				</td>
				<td style="text-align: center;">
					<input type="text" name="weight_starts[<?php echo $item->shipping_rate_weightbased_id; ?>]" value="<?php echo $item->weight_start; ?>" /> <?php echo JText::_('COM_TIENDA_TO'); ?>
					<input type="text" name="weight_ends[<?php echo $item->shipping_rate_weightbased_id; ?>]" value="<?php echo $item->weight_end; ?>" />
				</td>
				<td style="text-align: center;">
					<input type="text" name="weight_steps_size[<?php echo $item->shipping_rate_weightbased_id; ?>]" value="<?php echo $item->weight_step_size; ?>" />
				</td>
					<td style="text-align: center;">
					<input type="text" name="handlings[<?php echo $item->shipping_rate_weightbased_id; ?>]" value="<?php echo $item->shipping_handling; ?>" />
				</td>
				<td style="text-align: center;">
					[<a href="<?php echo $baseLink; ?>&shippingTask=deleterate&cid[]=<?php echo $item->shipping_rate_weightbased_id; ?>&return=<?php echo base64_encode($baseLink."&shippingTask=setrates&sid={$row->shipping_method_weightbased_id}&tmpl=component"); ?>">
						<?php echo JText::_('COM_TIENDA_DELETE_RATE'); ?>
					</a> ]
				</td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>

			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="20">
					<?php echo @$this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="sid" value="<?php echo $row->shipping_method_weightbased_id; ?>" />
	<input type="hidden" name="shippingTask" id="shippingTask" value="setrates" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	<?php echo $this->form['validate']; ?>
</form>
