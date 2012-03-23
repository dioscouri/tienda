<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>

<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_('Set Prices for'); ?>: <?php echo $row->product_name; ?></h1>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
	
<div class="note" style="width: 96%; margin-left: auto; margin-right: auto; margin-bottom: 20px;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Add a New Price'); ?></div>
    <div style="float: right;">
        <button onclick="document.getElementById('task').value='createprice'; document.adminForm.submit();"><?php echo JText::_('Create Price'); ?></button>
    </div>
    <div class="reset"></div>
	<table class="adminlist">
    	<thead>
        	<tr>
        		<th><?php echo JText::_('Date Range'); ?></th>
        		<th><?php echo JText::_('Quantity Range'); ?></th>
        		<th><?php echo JText::_('COM_TIENDA_GROUP'); ?></th>
        		<th><?php echo JText::_('COM_TIENDA_PRICE'); ?></th>
        	</tr>
        	</thead>
        	<tbody>
        	<tr>
        		<td style="text-align: center;">
            		<?php echo JHTML::calendar( "", "createprice_date_start", "createprice_date_start", '%Y-%m-%d %H:%M:%S' ); ?>
            		<?php echo JText::_('COM_TIENDA_TO'); ?>
            		<?php echo JHTML::calendar( "", "createprice_date_end", "createprice_date_end", '%Y-%m-%d %H:%M:%S' ); ?>
        		</td>
        		<td style="text-align: center;">
        			<input id="createprice_quantity_start" name="createprice_quantity_start" value="" size="5" />
        			<?php echo JText::_('COM_TIENDA_TO'); ?>
            		<input id="createprice_quantity_end" name="createprice_quantity_end" value="" size="5" />
            	</td>
            	<td style="text-align: center;">
        			<?php echo TiendaSelect::groups('', 'createprice_group_id'); ?>
        		</td>
        		<td style="text-align: center;">
        			<input id="createprice_price" name="createprice_price" value="" />
        		</td>
        	</tr>
    	</tbody>
	</table>
</div>

<div class="note_green" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Current Prices'); ?></div>
    <div style="float: right;">
        <button onclick="document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.getElementById('task').value='saveprices'; document.adminForm.submit();"><?php echo JText::_('Save All Changes'); ?></button>
    </div>
    <div class="reset"></div>
	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="text-align: center;">
                	<?php echo TiendaGrid::sort( 'Price', "tbl.product_price", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                	<?php echo TiendaGrid::sort( 'Date Range', "tbl.product_price_startdate", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                	<?php echo TiendaGrid::sort( 'Quantity Range', "tbl.price_quantity_start", @$state->direction, @$state->order ); ?>
                </th>
                <th>
                	<?php echo TiendaGrid::sort( 'Group', "tbl.group_id", @$state->direction, @$state->order ); ?>
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
					<?php echo TiendaGrid::checkedout( $item, $i, 'product_price_id' ); ?>
				</td>
				<td style="text-align: center;">
					<input type="text" name="price[<?php echo $item->product_price_id; ?>]" value="<?php echo $item->product_price; ?>" />
				</td>
				<td style="text-align: center;">
                	<?php echo JHTML::calendar( $item->product_price_startdate, "date_start[{$item->product_price_id}]", "date_start_{$item->product_price_id}", '%Y-%m-%d %H:%M:%S' ); ?>
                	<?php echo JText::_('COM_TIENDA_TO'); ?>
                	<?php echo JHTML::calendar( $item->product_price_enddate, "date_end[{$item->product_price_id}]", "date_end_{$item->product_price_id}", '%Y-%m-%d %H:%M:%S' ); ?>
				</td>
				<td style="text-align: center;">
					<input type="text" name="quantity_start[<?php echo $item->product_price_id; ?>]" value="<?php echo $item->price_quantity_start; ?>" size="5" />
					<?php echo JText::_('COM_TIENDA_TO'); ?>
					<input type="text" name="quantity_end[<?php echo $item->product_price_id; ?>]" value="<?php echo $item->price_quantity_end; ?>" size="5" />
				</td>
				<td style="text-align: center;">
					<?php echo TiendaSelect::groups($item->group_id, "price_group_id[{$item->product_price_id}]"); ?>
				</td>
				<td style="text-align: center;">
					[<a href="index.php?option=com_tienda&controller=productprices&task=delete&cid[]=<?php echo $item->product_price_id; ?>&return=<?php echo base64_encode("index.php?option=com_tienda&controller=products&task=setprices&id={$row->product_id}&tmpl=component"); ?>">
						<?php echo JText::_('Delete Price'); ?>	
					</a>
					]
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
	<input type="hidden" name="id" value="<?php echo $row->product_id; ?>" />
	<input type="hidden" name="task" id="task" value="setprices" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</div>
</form>