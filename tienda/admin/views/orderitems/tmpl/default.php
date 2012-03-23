<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php Tienda::load( 'TiendaHelperBase', 'helpers._base' ); ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
	
    <table>
        <tr>
            <td align="left" width="100%">
            </td>
            <td nowrap="nowrap" style="text-align: right;">
                <input name="filter" value="<?php echo @$state->filter; ?>" />
                <button onclick="this.form.submit();"><?php echo JText::_('COM_TIENDA_SEARCH'); ?></button>
                <button onclick="tiendaFormReset(this.form);"><?php echo JText::_('COM_TIENDA_RESET'); ?></button>
            </td>
        </tr>
    </table>

	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_('Num'); ?>
                </th>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'ID', "tbl.orderitem_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 150px;">
                    <?php echo TiendaGrid::sort( 'Date', "o.created_date", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 200px;">
                    <?php echo TiendaGrid::sort( 'Order', "tbl.order_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'Item', "tbl.orderitem_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'Quantity', "tbl.orderitem_quantity", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
    	            <?php echo TiendaGrid::sort( 'Price', "tbl.orderitem_price", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'Status', "tbl.orderitem_status", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'Payment Status', "op.transaction_status", @$state->direction, @$state->order ); ?>
                </th>
            </tr>
            <tr class="filterline">
                <th colspan="3">
	                <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                	<div class="range">
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span> <input id="filter_id_from" name="filter_id_from" value="<?php echo @$state->filter_id_from; ?>" size="5" class="input" />
	                	</div>
	                	<div class="rangeline">
	                		<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span> <input id="filter_id_to" name="filter_id_to" value="<?php echo @$state->filter_id_to; ?>" size="5" class="input" />
	                	</div>
                	</div>
                </th>
                <th>
                    <div class="range">
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
                            <?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
                            <?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
                        </div>
                    </div>
                </th>
                <th>
                    <input name="filter_orderid" value="<?php echo @$state->filter_orderid; ?>" size="15"/>
                </th>
                <th style="text-align: left;">
                	<input name="filter_product_name" value="<?php echo @$state->filter_product_name; ?>" size="15"/>
                </th>
                <th>
                <!--
                    <div class="range">
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span> <input id="filter_total_from" name="filter_total_from" value="<?php echo @$state->filter_total_from; ?>" size="5" class="input" />
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span> <input id="filter_total_to" name="filter_total_to" value="<?php echo @$state->filter_total_to; ?>" size="5" class="input" />
                        </div>
                    </div>
                -->
                </th>
                <th>
                    <!-- <input name="filter_price" value="<?php echo @$state->filter_price; ?>" size="25"/>-->
                </th>
                <th>
                    <!-- <input name="filter_status" value="<?php echo @$state->filter_status; ?>" size="25"/>-->
                </th>
                <th>
                	<input name="filter_paymentstatus" value="<?php echo @$state->filter_paymentstatus; ?>" size="15"/>
                </th>
            </tr>
			<tr>
				<th colspan="20" style="font-weight: normal;">
					<div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
					<div style="float: left;"><?php echo @$this->pagination->getListFooter(); ?></div>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="20">
					<div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
					<?php echo @$this->pagination->getPagesLinks(); ?>
				</td>
			</tr>
		</tfoot>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'orderitem_id' ); ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->orderitem_id; ?>
					</a>
				</td>
                <td style="text-align: center;">
                    <a href="<?php echo $item->link; ?>">
                        <?php echo JHTML::_('date', $item->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
                    </a>
                </td>
                <td style="text-align: center;">
                    <a href="<?php echo $item->link; ?>">
                        <?php echo $item->order_id; ?>
                    </a>
                    <br/>
                    <?php echo $item->order_state_name; ?>
                    <br/>
                    <?php echo $item->user_name .' [ '.$item->user_id.' ]'; ?>
                </td>
				<td style="text-align: left;">
                    <?php echo $item->orderitem_name .' [ '.$item->product_id.' ]'; ?>
                    <?php if (!empty($item->orderitem_sku)) { ?>
                        <br/>
                        <b><?php echo JText::_('COM_TIENDA_SKU'); ?></b>: <?php echo $item->orderitem_sku; ?>
                    <?php } ?>
				</td>
                <td style="text-align: center;">
                    <?php echo $item->orderitem_quantity; ?>
                </td>
				<td style="text-align: center;">
				    <?php echo TiendaHelperBase::currency( $item->orderitem_price ); ?>
				</td>
				<td style="text-align: center;">
					<?php echo $item->orderitem_status; ?>
				</td>
				<td style="text-align: center;">
					<?php echo $item->transaction_status; ?>
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
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>
