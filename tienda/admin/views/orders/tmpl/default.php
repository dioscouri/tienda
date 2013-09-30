<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php Tienda::load( 'TiendaHelperBase', 'helpers._base' ); ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
	
    <?php echo TiendaGrid::searchform(@$state->filter,JText::_('COM_TIENDA_SEARCH'), JText::_('COM_TIENDA_RESET') ) ?>
	

	<table class="table table-striped table-bordered" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_('COM_TIENDA_NUM'); ?>
                </th>
                <th style="width: 20px;">
                	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_ID', "tbl.order_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 200px;">
                    <?php echo TiendaGrid::sort( 'COM_TIENDA_DATE', "tbl.created_date", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: left;" colspan="2">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_CUSTOMER', "ui.last_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                	<?php echo TiendaGrid::sort( 'COM_TIENDA_TOTAL', "tbl.order_total", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
    	            <?php echo TiendaGrid::sort( 'COM_TIENDA_STATE', "s.order_state_name", @$state->direction, @$state->order ); ?>
                </th>
            </tr>
            <tr class="filterline">
                <th colspan="3">
	                <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                	<div class="range">
                        <div class="rangeline">
                            <input type="text" placeholder="<?php echo JText::_('COM_TIENDA_FROM'); ?>" id="filter_id_from" name="filter_id_from" value="<?php echo @$state->filter_id_from; ?>" size="5" class="input input-tiny" />
                        </div>
                        <div class="rangeline">
                            <input type="text" placeholder="<?php echo JText::_('COM_TIENDA_TO'); ?>" id="filter_id_to" name="filter_id_to" value="<?php echo @$state->filter_id_to; ?>" size="5" class="input input-tiny" />
                        </div>
                    </div>
                </th>
                <th>
                    <div class="range">
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
                            <?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d 00:00:00' ); ?>
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
                            <?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d 00:00:00' ); ?>
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_TYPE'); ?>:</span>
                            <?php echo TiendaSelect::datetype( @$state->filter_datetype, 'filter_datetype', '', 'datetype' ); ?>
                        </div>
                    </div>
                </th>
                <th style="text-align: left;" colspan="2">
                	<input id="filter_user" name="filter_user" type="text" value="<?php echo @$state->filter_user; ?>" size="25"/>
                </th>
                <th>
                    <div class="range">
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span> <input id="filter_total_from" name="filter_total_from" value="<?php echo @$state->filter_total_from; ?>" size="5" class="input" />
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span> <input id="filter_total_to" name="filter_total_to" value="<?php echo @$state->filter_total_to; ?>" size="5" class="input" />
                        </div>
                    </div>
                </th>
                <th>
    	            <?php echo TiendaSelect::orderstate(@$state->filter_orderstate, 'filter_orderstate', $attribs, 'order_state_id', true ); ?>
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
        <?php foreach (@$items as $item) :
        	$guest = $item->user_id < Tienda::getGuestIdStart();
            ?>
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'order_id' ); ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->order_id; ?>
					</a>
				</td>
                <td style="text-align: center;">
                    <a href="<?php echo $item->link; ?>">
                        <?php echo JHTML::_('date', $item->created_date, Tienda::getInstance()->get('date_format')); ?>
                    </a>
                </td>
                <td style="text-align: center; width: 50px;">
                    <a href="<?php echo $item->link_view; ?>">
                        <img src="<?php echo Tienda::getURL('images').'page_edit.png' ?>" title="<?php echo JText::_('COM_TIENDA_ORDER_DASHBOARD'); ?>"/>
                    </a>
                </td>
				<td style="text-align: left;">
					<?php
						if( $guest ) 
						{
						    ?>
						    <a href="<?php echo $item->link_view; ?>">
						    <?php echo $item->billing_first_name . " " . $item->billing_last_name; ?>
						    </a>
						    <br/>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php echo ' [ '. JText::_('COM_TIENDA_GUEST') .' ]'; ?>
						    <?php
						} 
						else
						{
							?>
                            <a href="index.php?option=com_tienda&view=users&task=view&id=<?php echo $item->user_id; ?>">
                            <?php echo $item->user_name .' [ '.$item->user_id.' ]'; ?>
                            </a>
                            <br/>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php echo $item->email .' [ '.$item->user_username.' ]'; ?>
							<?php
						}
					?>
					<br/>
					<b><?php echo JText::_('COM_TIENDA_SHIP_TO'); ?></b>:
					<?php 
					if ((empty($item->shipping_address_1) and (empty($item->shipping_address_2))))
					{
					   echo JText::_('COM_TIENDA_UNDEFINED_SHIPPING_ADDRESS'); 
					}
					   else
					{
						echo $item->shipping_address_1 ? $item->shipping_address_1.", " : "";
	                    echo $item->shipping_address_2 ? $item->shipping_address_2.", " : "";
	                    echo $item->shipping_city.", ";
	                    echo $item->shipping_zone_name." ";
	                    echo $item->shipping_postal_code." ";
	                    echo $item->shipping_country_name;
					}
					?>
                    <?php 
                    if (!empty($item->order_number))
                    {
                        echo "<br/><b>".JText::_('COM_TIENDA_ORDER_NUMBER')."</b>: ".$item->order_number;
                    }
                    ?>
				</td>
				<td style="text-align: center;">
					<?php echo TiendaHelperBase::currency( $item->order_total, $item->currency ); ?>
                    <?php if (!empty($item->commissions)) { ?>
                        <br/>
                        <?php JHTML::_('behavior.tooltip'); ?>
                        <a href="index.php?option=com_amigos&view=commissions&filter_orderid=<?php echo $item->order_id; ?>" target="_blank">
                            <img src='<?php echo JURI::root(true); ?>/media/com_amigos/images/amigos_16.png' title="<?php echo JText::_('COM_TIENDA_ORDER_HAS_A_COMMISSION'); ?>::<?php echo JText::_('COM_TIENDA_VIEW_COMMISSION_RECORDS'); ?>" class="hasTip" />
                        </a>
                    <?php } ?>
				</td>
				<td style="text-align: center;">
					<?php echo $item->order_state_name; ?>
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
