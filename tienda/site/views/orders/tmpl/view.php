<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>
<?php $order = @$this->order; ?>
<?php $surrounding = @$this->surrounding; ?>
<?php $items = @$row->orderitems ? @$row->orderitems : array(); ?>
<?php $histories = @$row->orderhistory ? @$row->orderhistory : array(); ?>

<?php if (JRequest::getVar('task') == 'print') : ?>
    <script type="text/javascript">
           window.print();
    </script>
<?php endif; ?>

<div class='componentheading'>
	<span><?php echo JText::_( "Order Detail" ); ?></span>
</div>

    <?php if ($menu =& TiendaMenu::getInstance()) { $menu->display(); } ?>
    <div style="float: right;">
        <?php
        $url = JRoute::_( "index.php?option=com_tienda&view=orders&task=print&tmpl=component&id=".@$row->order_id );
        $text = JText::_( "Print Invoice" );
        echo TiendaUrl::popup( $url, $text ); 
        ?>
    </div>
    <?php
    echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=orders")."'>".JText::_( 'Return to List' )."</a>";
    ?>
		
	<div id="order_info">
		<h3><?php echo JText::_("Order Information"); ?></h3>
        <strong><?php echo JText::_("Order ID"); ?></strong>: <?php echo @$row->order_id; ?><br/>
        <strong><?php echo JText::_("Date"); ?></strong>: <?php echo JHTML::_('date', $row->created_date, TiendaConfig::getInstance()->get('date_format')); ?><br/>
        <strong><?php echo JText::_("Status"); ?></strong>: <?php echo @$row->order_state_name; ?><br/>
	</div>
	
	<div id="payment_info">
		<h3><?php echo JText::_("Payment Information"); ?></h3>
		<strong><?php echo JText::_("Amount"); ?></strong>: <?php echo TiendaHelperBase::currency( $row->order_total, $row->currency ); ?><br/>
        <strong><?php echo JText::_("Billing Address"); ?></strong>: 
                    <?php
                    echo $row->billing_first_name." ".$row->billing_last_name."<br/>";
                    echo $row->billing_address_1.", ";
                    echo $row->billing_address_2 ? $row->billing_address_2.", " : "";
                    echo $row->billing_city.", ";
                    echo $row->billing_zone_name." ";
                    echo $row->billing_postal_code." ";
                    echo $row->billing_country_name;
                    ?>
        <br/>
        <strong><?php echo JText::_("Associated Payment Records"); ?></strong>:
            <div>
		        <?php
		        if (!empty($row->orderpayments))
		        {
		        	foreach ($row->orderpayments as $orderpayment)
		        	{
		        		// TODO Make these link to view them
		        		echo JText::_( "Payment ID" ).": ".$orderpayment->orderpayment_id."<br/>";
		        	}
		        } 
		        ?>
            </div> 
        <br/>
        
	</div>

    <div id="shipping_info">
        <h3><?php echo JText::_("Shipping Information"); ?></h3>
        <strong><?php echo JText::_("Shipping Method"); ?></strong>: <?php echo JText::_( $row->shipping_method_name ); ?><br/>
        <strong><?php echo JText::_("Shipping Address"); ?></strong>: 
                    <?php
                    echo $row->shipping_first_name." ".$row->shipping_last_name."<br/>";
                    echo $row->shipping_address_1.", ";
                    echo $row->shipping_address_2 ? $row->shipping_address_2.", " : "";
                    echo $row->shipping_city.", ";
                    echo $row->shipping_zone_name." ";
                    echo $row->shipping_postal_code." ";
                    echo $row->shipping_country_name;
                    ?>
        <br/>
    </div>

	<div id="items_info">
		<h3><?php echo JText::_("Items in Order"); ?></h3>
		
		<table class="adminlist" style="clear: both;">
		<thead>
			<tr>
                <th style="text-align: left;"><?php echo JText::_("Item"); ?></th>
                <th style="width: 150px; text-align: center;"><?php echo JText::_("Quantity"); ?></th>
                <th style="width: 150px; text-align: right;"><?php echo JText::_("Amount"); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
			<tr class='row<?php echo $k; ?>'>
                <td>
                    <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&task=view&id=".$item->product_id ); ?>">
                        <?php echo JText::_( $item->orderitem_name ); ?>
                    </a>
                    <br/>
                    
                    <?php if (!empty($item->attributes_names)) : ?>
                        <?php echo $item->attributes_names; ?>
                        <br/>
                    <?php endif; ?>
                    
                    <b><?php echo JText::_( "Price" ); ?>:</b>
                    <?php echo TiendaHelperBase::currency( $item->orderitem_price, $row->currency ); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->orderitem_quantity; ?>
                </td>
                <td style="text-align: right;">
                    <?php echo TiendaHelperBase::currency( $item->orderitem_final_price, $row->currency ); ?>
                </td>
			</tr>
		<?php $i=$i+1; $k = (1 - $k); ?>
		<?php endforeach; ?>
		
		<?php if (empty($items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('No items found'); ?>
				</td>
			</tr>
		<?php endif; ?>
		</tbody>
        <tfoot>
        <tr>
            <th colspan="2" style="text-align: right;">
            <?php echo JText::_( "Subtotal" ); ?>
            </th>
            <th style="text-align: right;">
            <?php echo TiendaHelperBase::currency($row->order_subtotal, $row->currency); ?>
            </th>
        </tr>
        <?php
        if (TiendaConfig::getInstance()->get('display_taxclass_lineitems') && !empty($row->ordertaxclasses))
        {
            foreach ($row->ordertaxclasses as $taxclass)
            {
            ?>
            <tr>
                <th colspan="2" style="text-align: right;">
                <?php echo JText::_( $taxclass->ordertaxclass_description ); ?>
                </th>
                <th style="text-align: right;">
                <?php echo TiendaHelperBase::currency($taxclass->ordertaxclass_amount, $row->currency); ?>
                </th>
            </tr>
            <?php
            }
        } 
            else
        {
            ?>
            <tr>
                <th colspan="2" style="text-align: right;">
                <?php echo JText::_( "Tax" ); ?>
                </th>
                <th style="text-align: right;">
                <?php echo TiendaHelperBase::currency($row->order_tax, $row->currency); ?>
                </th>
            </tr>
            <?php            
        }
        ?>
        <tr>
            <th colspan="2" style="text-align: right;">
            <?php echo JText::_( "Shipping" ); ?>
            </th>
            <th style="text-align: right;">
            <?php echo TiendaHelperBase::currency($row->order_shipping, $row->currency); ?>
            </th>
        </tr>
        <tr>
            <th colspan="2" style="font-size: 120%; text-align: right;">
            <?php echo JText::_( "Total" ); ?>
            </th>
            <th style="font-size: 120%; text-align: right;">
            <?php echo TiendaHelperBase::currency($row->order_total, $row->currency); ?>
            </th>
        </tr>
        </tfoot>
		</table>
	</div>

	<?php if (@$row->customer_note) : ?>
		<div id="customer_note">
			<h3><?php echo JText::_("Note"); ?></h3>
			<span><?php echo @$row->customer_note; ?></span>
		</div>
	<?php endif; ?>
