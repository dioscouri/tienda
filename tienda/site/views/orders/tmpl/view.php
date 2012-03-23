<?php
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	$form = @$this->form;
	$row = @$this->row;
	$order = @$this->order;
	$items = @$order->getItems();
	$surrounding = @$this->surrounding;
	$histories = @$row->orderhistory ? @$row->orderhistory : array();
	Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
	$display_credits = TiendaConfig::getInstance()->get( 'display_credits', '0' );
	
	Tienda::load( 'TiendaHelperManufacturer', 'helpers.manufacturer' );
	$helperMan = new TiendaHelperManufacturer();
	$helperMan->calculateStatsOrder( $items );
?>

<?php if (JRequest::getVar('task') == 'print') : ?>
<script type="text/javascript">
           window.print();
    </script>
<?php endif; ?>
<div class='componentheading'>
	<span><?php echo JText::_('COM_TIENDA_ORDER_DETAIL'); ?> </span>
</div>

<?php if ($menu =& TiendaMenu::getInstance()) { $menu->display(); } ?>
<div style="float: right;">
<?php
	if( $row->user_id > 0 )
		$url = JRoute::_( "index.php?option=com_tienda&view=orders&task=print&tmpl=component&id=".@$row->order_id );
	else
		$url = JRoute::_( "index.php?option=com_tienda&view=orders&task=print&tmpl=component&id=".@$row->order_id ).'&h='.$row->order_hash;
	$text = JText::_('COM_TIENDA_PRINT_INVOICE');
	echo TiendaUrl::popup( $url, $text );
?>
</div>
<?php
	echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=orders")."'>".JText::_('COM_TIENDA_RETURN_TO_LIST')."</a>";
?>

<?php
	// fire plugin event here to enable extending the form
	JDispatcher::getInstance()->trigger('onBeforeDisplayOrderView', array( $row ) );
?>

<div id="order_info">
	<h3>
	<?php echo JText::_('COM_TIENDA_ORDER_INFORMATION'); ?>
	</h3>
	<strong><?php echo JText::_('COM_TIENDA_ORDER_ID'); ?> </strong>:
		<?php echo TiendaHelperOrder::displayOrderNumber( $row ); ?>
	<br /> <strong><?php echo JText::_('COM_TIENDA_DATE'); ?> </strong>:
		<?php echo JHTML::_('date', $row->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
	<br /> <strong><?php echo JText::_('COM_TIENDA_STATUS'); ?> </strong>:
		<?php echo @$row->order_state_name; ?>
	<br />
</div>

<div id="payment_info">
	<h3><?php echo JText::_('COM_TIENDA_PAYMENT_INFORMATION'); ?></h3>
	<strong><?php echo JText::_('COM_TIENDA_AMOUNT'); ?> </strong>:
		<?php echo TiendaHelperBase::currency( $row->order_total, $row->currency ); ?><br />
	<strong><?php echo JText::_('COM_TIENDA_BILLING_ADDRESS'); ?> </strong>:
	<?php
	if( strlen( $row->billing_company ) )
		echo $row->billing_company."<br/>";
	if( strlen( $row->billing_tax_number ) )
		echo $row->billing_tax_number."<br/>";
	echo $row->billing_first_name." ".$row->billing_last_name."<br/>";
	echo $row->billing_address_1.", ";
	echo $row->billing_address_2 ? $row->billing_address_2.", " : "";
	echo $row->billing_city.", ";
	echo $row->billing_zone_name." ";
	echo $row->billing_postal_code." ";
	echo $row->billing_country_name;
	?>
	<br />
	<strong><?php echo JText::_('COM_TIENDA_ASSOCIATED_PAYMENT_RECORDS'); ?> </strong>:
	<div>
	<?php
	if (!empty($row->orderpayments))
	{
		foreach ($row->orderpayments as $orderpayment)
		{
			// TODO Make these link to view them
			echo JText::_('COM_TIENDA_PAYMENT_ID').": ".$orderpayment->orderpayment_id."<br/>";
		}
	}
	?>
	</div>
	<br />
</div>

	<?php if ($order->order_ships) { ?>
<div id="shipping_info">
	<h3>
	<?php echo JText::_('COM_TIENDA_SHIPPING_INFORMATION'); ?>
	</h3>
	<strong><?php echo JText::_('COM_TIENDA_SHIPPING_METHOD'); ?> </strong>:
	<?php echo JText::_( $row->ordershipping_name ); ?>
	<br /> <strong><?php echo JText::_('COM_TIENDA_SHIPPING_ADDRESS'); ?> </strong>:
	<?php
	echo $row->shipping_first_name." ".$row->shipping_last_name."<br/>";
	echo $row->shipping_address_1.", ";
	echo $row->shipping_address_2 ? $row->shipping_address_2.", " : "";
	echo $row->shipping_city.", ";
	echo $row->shipping_zone_name." ";
	echo $row->shipping_postal_code." ";
	echo $row->shipping_country_name;
	?>
	<br />
</div>
	<?php } ?>

	<?php
	// fire plugin event here to enable extending the form
	JDispatcher::getInstance()->trigger('onBeforeDisplayOrderViewOrderItems', array( $row ) );
	?>

<div id="items_info">
	<h3>
	<?php echo JText::_('COM_TIENDA_ITEMS_IN_ORDER'); ?>
	</h3>

	<table class="adminlist" style="clear: both;">
		<thead>
			<tr>
				<th style="text-align: left;"><?php echo JText::_('COM_TIENDA_ITEM'); ?></th>
				<th style="width: 150px; text-align: center;"><?php echo JText::_('COM_TIENDA_QUANTITY'); ?>
				</th>
				<th style="width: 150px; text-align: right;"><?php echo JText::_('COM_TIENDA_AMOUNT'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php $i=0; $k=0; ?>
		<?php foreach (@$items as $item) : ?>

			<tr class='row<?php echo $k; ?>'>
				<td><a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&task=view&id=".$item->product_id ); ?>">
					<?php echo JText::_( $item->orderitem_name ); ?> </a> <br /> <?php if (!empty($item->orderitem_attribute_names)) : ?>
					<?php echo $item->orderitem_attribute_names; ?> <br /> <?php endif; ?>

					<?php if (!empty($item->orderitem_sku)) : ?> <b><?php echo JText::_('COM_TIENDA_SKU'); ?>:</b>
						<?php echo $item->orderitem_sku; ?> <br />
					<?php endif; ?>
					<?php if ($item->orderitem_recurs) : ?>
						<?php $recurring_subtotal = $item->recurring_price; ?> <?php echo JText::_('COM_TIENDA_RECURRING_PRICE'); ?>:
						<?php echo TiendaHelperBase::currency($item->recurring_price); ?> (<?php echo $item->recurring_payments . " " . JText::_('COM_TIENDA_PAYMENTS'); ?>,
						<?php echo $item->recurring_period_interval." ". JText::_('$item->recurring_period_unit PERIOD UNIT')." ".JText::_('COM_TIENDA_PERIODS'); ?>)
						<?php if ($item->recurring_trial) : ?> <br /> <?php echo JText::_('COM_TIENDA_TRIAL_PERIOD_PRICE'); ?>:
							<?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
							(<?php echo "1 " . JText::_('COM_TIENDA_PAYMENT'); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_('$item->recurring_trial_period_unit PERIOD UNIT')." ".JText::_('COM_TIENDA_PERIOD'); ?>)
						<?php endif; ?>
						<?php else : ?>
							<b><?php echo JText::_('COM_TIENDA_PRICE'); ?>:</b>
						<?php echo TiendaHelperBase::currency( $item->orderitem_price, $row->currency ); ?>
						<?php endif; ?>
						<!-- onDisplayOrderItem event: plugins can extend order item information -->
					<?php if (!empty($this->onDisplayOrderItem) && (!empty($this->onDisplayOrderItem[$i]))) : ?>
						<div class='onDisplayOrderItem_wrapper_<?php echo $i?>'>
						<?php echo $this->onDisplayOrderItem[$i]; ?>
						</div>
					<?php endif; ?>
				</td>
				<td style="text-align: center;"><?php echo $item->orderitem_quantity; ?>
				</td>
				<td style="text-align: right;"><?php echo TiendaHelperBase::currency( $item->orderitem_final_price, $row->currency ); ?>
				</td>
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>

			<?php if (empty($items)) : ?>
			<tr>
				<td colspan="10" align="center"><?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="2" style="text-align: right;"><?php echo JText::_('COM_TIENDA_SUBTOTAL'); ?>
				</th>
				<th style="text-align: right;"><?php echo TiendaHelperBase::currency($order->order_subtotal, $row->currency); ?>
				</th>
			</tr>

			<?php if (!empty($row->order_discount)) : ?>
			<tr>
				<th colspan="2" style="text-align: right;"><?php echo JText::_('COM_TIENDA_DISCOUNT'); ?>
				</th>
				<th colspan="3" style="text-align: right;"><?php echo TiendaHelperBase::currency($row->order_discount, $row->currency ); ?>
				</th>
			</tr>
			<?php
				endif;
				echo $this->displayTaxes();
			?>
			<tr>
				<th colspan="2" style="text-align: right;"><?php echo JText::_('COM_TIENDA_SHIPPING'); ?>
				</th>
				<th style="text-align: right;"><?php echo TiendaHelperBase::currency($row->order_shipping, $row->currency); ?>
				</th>
			</tr>
			<?php if ((float) $row->order_shipping_tax > (float) '0.00') : ?>
			<tr>
				<th colspan="2" style="text-align: right;">
					<?php echo JText::_('COM_TIENDA_SHIPPING_TAX'); ?>
				</th>
				<th style="text-align: right;">
					<?php echo TiendaHelperBase::currency($row->order_shipping_tax, $row->currency); ?>
				</th>
			</tr>
			<?php endif; ?>
			<?php if ( $display_credits && ( (float) $row->order_credit > (float) '0.00' ) ) : ?>
			<tr>
				<th colspan="2" style="text-align: right;"><?php echo JText::_('COM_TIENDA_STORE_CREDIT'); ?>
				</th>
				<th style="text-align: right;">- <?php echo TiendaHelperBase::currency($row->order_credit, $row->currency); ?>
				</th>
			</tr>
			<?php endif; ?>
			<tr>
				<th colspan="2" style="font-size: 120%; text-align: right;"><?php echo JText::_('COM_TIENDA_TOTAL'); ?>
				</th>
				<th style="font-size: 120%; text-align: right;"><?php echo TiendaHelperBase::currency($row->order_total, $row->currency); ?>
				</th>
			</tr>
		</tfoot>
	</table>
</div>

<?php
// fire plugin event here to enable extending the form
JDispatcher::getInstance()->trigger('onAfterDisplayOrderViewOrderItems', array( $row ) );
?>

<?php if (!empty($row->customer_note)) : ?>
	<div id="customer_note">
		<h3>
		<?php echo JText::_('COM_TIENDA_NOTE'); ?>
		</h3>
		<span><?php echo $row->customer_note; ?> </span>
	</div>
<?php endif; ?>

<?php
// fire plugin event here to enable extending the form
JDispatcher::getInstance()->trigger('onAfterDisplayOrderView', array( $row ) );
?>