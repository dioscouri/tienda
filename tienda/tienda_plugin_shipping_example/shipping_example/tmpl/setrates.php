<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $form = @$this->form2; ?>
<?php $row = @$this->row; ?>
<?php $items = @$this->items; ?>
<?php $baseLink = $this->baseLink; ?>

<h3><?php echo JText::_( "Set Rates for" ); ?>: <?php echo $row->shipping_method_name; ?></h3>

<div class="note" style="width: 95%; text-align: center; margin-left: auto; margin-right: auto;">
	<?php echo JText::_( "Be Sure to Save Your Work" ); ?>:
	<button onclick="document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.getElementById('shippingTask').value='saverates'; document.adminForm.submit();"><?php echo JText::_('Save Changes'); ?></button>
</div>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
	
    <table>
        <tr>
            <td align="left" width="100%">
            </td>
            <td nowrap="nowrap">
            	<table class="adminlist">
            	<thead>
            	<tr>
            		<th></th>
                    <th><?php echo JText::_( "GeoZone" ); ?></th>
            		<th><?php echo JText::_( "Weight Range" ); ?></th>
            		<th><?php echo JText::_( "Price" ); ?></th>
            		<th><?php echo JText::_( "Handling Fee" ); ?></th>
            		<th></th>
            	</tr>
            	</thead>
            	<tbody>
            	<tr>
            		<td>
            			<?php echo JText::_( "Complete this form to add a new rate" ); ?>:
                	</td>
            		<td>
                		<?php echo TiendaSelect::geozone("", "geozone_id"); ?>
                		<input type="hidden" name="shipping_method_id" value="<?php echo $row->shipping_method_id; ?>" />
            		</td>
            		<td>
            			<input id="shipping_rate_weight_start" name="shipping_rate_weight_start" value="" size="5" />
            			<?php echo JText::_("to"); ?>
                		<input id="shipping_rate_weight_end" name="shipping_rate_weight_end" value="" size="5" />
                	</td>
            		<td>
            			<input id="shipping_rate_price" name="shipping_rate_price" value="" />
            		</td>
                    <td>
                        <input id="shipping_rate_handling" name="shipping_rate_handling" value="" />
                    </td>
            		<td>
            			<input type="button" onclick="document.getElementById('shippingTask').value='createrate'; document.adminForm.submit();" value="<?php echo JText::_('Create Rate'); ?>" class="button" />
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
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="text-align: center;">
                    <?php echo TiendaGrid::sort( 'Geo Zone', "tbl.geozone_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                	<?php echo TiendaGrid::sort( 'Price', "tbl.shipping_rate_price", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                	<?php echo TiendaGrid::sort( 'Weight Range', "tbl.shipping_rate_weight_start", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                	<?php echo TiendaGrid::sort( 'Handling Fee', "tbl.shipping_rate_handling", @$state->direction, @$state->order ); ?>
                </th>
				<th>
				</th>
            </tr>
		</thead>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'shipping_rate_id' ); ?>
				</td>
                <td style="text-align: center;">
                    <?php echo TiendaSelect::geozone($item->geozone_id, "geozone[{$item->shipping_rate_id}]"); ?>
                </td>				
				<td style="text-align: center;">
					<input type="text" name="price[<?php echo $item->shipping_rate_id; ?>]" value="<?php echo $item->shipping_rate_price; ?>" />
				</td>
				<td style="text-align: center;">
				    <input type="text" name="weight_start[<?php echo $item->shipping_rate_id; ?>]" value="<?php echo $item->shipping_rate_weight_start; ?>" />
				    <?php echo JText::_("to"); ?>
				    <input type="text" name="weight_end[<?php echo $item->shipping_rate_id; ?>]" value="<?php echo $item->shipping_rate_weight_end; ?>" />
				</td>
				<td style="text-align: center;">
					<input type="text" name="handling[<?php echo $item->shipping_rate_id; ?>]" value="<?php echo $item->shipping_rate_handling; ?>" />
				</td>
				<td style="text-align: center;">
					[<a href="<?php echo $baseLink; ?>&shippingTask=delete&cid[]=<?php echo $item->shipping_rate_id; ?>&return=<?php echo base64_encode($baseLink."&shippingTask=setrates&sid={$row->shipping_method_id}&tmpl=component"); ?>">
						<?php echo JText::_( "Delete Rate" ); ?>	
					</a>
					]
				</td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>
			
			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('No items found'); ?>
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
	<input type="hidden" name="sid" value="<?php echo $row->shipping_method_id; ?>" />
	<input type="hidden" name="shippingTask" id="shippingTask" value="setrates" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>