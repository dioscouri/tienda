<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>
<?php $order = @$this->order; ?>
<?php $surrounding = @$this->surrounding; ?>
<?php $items = @$row->orderitems ? @$row->orderitems : array(); ?>
<?php $histories = @$row->orderhistory ? @$row->orderhistory : array(); ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

    <table>
        <tr>
            <td align="left" width="100%">
			    <?php
			    echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=orders")."'>".JText::_( 'Return to List' )."</a>";
			    ?>
            </td>
            <td nowrap="nowrap" style="text-align: right;">
		        [<?php
		        $url = "index.php?option=com_tienda&view=orders&task=print&tmpl=component&id=".@$row->order_id;
		        $text = JText::_( "Print Invoice" );
		        echo TiendaUrl::popup( $url, $text );
		        ?>]
            </td>
        </tr>
    </table>

    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplayOrderView', array( $row ) );                    
    ?>

	<table style="width: 100%;">
	<tr>
		<td style="width: 50%; vertical-align: top;">
		
            <fieldset>
            <legend><?php echo JText::_('Order Information'); ?></legend>
	        	
		    <table class="admintable" style="clear: both;">
		    <tr>
		        <td style="width: 100px; text-align: right;" class="key">
		            <?php echo JText::_("Order ID"); ?>
		        </td>
		        <td>
		            <?php echo $row->order_id; ?>
		        </td>
		    </tr>
		    <tr>
		        <td style="width: 100px; text-align: right;" class="key">
		            <?php echo JText::_("Order Date"); ?>
		        </td>
		        <td>
		            <?php echo JHTML::_('date', $row->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
		        </td>
		    </tr>
		    <tr>
		        <td style="width: 100px; text-align: right;" class="key">
		            <?php echo JText::_("Order Status"); ?>
		        </td>
		        <td>
		            <?php echo $row->order_state_name; ?>
		        </td>
		    </tr>
            <?php if (!empty($row->commissions)) { ?>
                <?php JHTML::_('behavior.tooltip'); ?>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_("Commissions"); ?>
                        <img src='<?php echo JURI::root(true); ?>/media/com_amigos/images/amigos_16.png' title="<?php echo JText::_( "Order Has a Commission" ); ?>" class="hasTip" />
                    </td>
                    <td>
                        <a href="index.php?option=com_amigos&view=commissions&filter_orderid=<?php echo $row->order_id; ?>" target="_blank">
                            <?php echo JText::_( "View Commission Records" ); ?>
                        </a>
                    </td>
                </tr>
            <?php } ?>		    
		    
		    </table>
		    </fieldset>
		    
            <fieldset>
            <legend><?php echo JText::_('Customer Information'); ?></legend>
                
            <table class="admintable" style="clear: both;">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Name"); ?>
                </td>
                <td>
                    <?php echo $row->user_name; ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Email"); ?>
                </td>
                <td>
                    <?php echo $row->email; ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("IP Address"); ?>
                </td>
                <td>
                    <?php echo $row->ip_address; ?>
                </td>
            </tr>
            
            <?php if (@$row->customer_note) : ?>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Note"); ?>
                </td>
                <td>
                    <?php echo $row->customer_note; ?>
                </td>
            </tr>
            <?php endif; ?>
            </table>
            
            </fieldset>

		    <?php if ($order->order_ships) { ?>  
            <fieldset>
            <legend><?php echo JText::_('Shipping Information'); ?></legend>
            	        
		    <table class="admintable" style="clear: both;">
		    <tr>
		        <td style="width: 100px; text-align: right;" class="key">
		            <?php echo JText::_("Shipping Method"); ?>
		        </td>
	            <td>
	                <?php echo JText::_( $row->ordershipping_name ); ?>
	            </td>
	        </tr>
	        <tr>
		        <td style="width: 100px; text-align: right;" class="key">
		            <?php echo JText::_("Shipping Address"); ?>
		        </td>
	            <td>
	                <?php
	                echo $row->shipping_first_name." ".$row->shipping_last_name."<br/>";
	                echo $row->shipping_address_1.", ";
	                echo $row->shipping_address_2 ? $row->shipping_address_2.", " : "";
	                echo $row->shipping_city.", ";
	                echo $row->shipping_zone_name." ";
	                echo $row->shipping_postal_code." ";
	                echo $row->shipping_country_name;
	                ?>
	            </td>
		    </tr>
		    </table>
		    </fieldset>
		    <?php } ?>
		    
            <fieldset>
            <legend><?php echo JText::_('Payment Information'); ?></legend>
            <?php // TODO Make this assume multiple payments, and display all of them ?>
            <table class="admintable" style="clear: both;">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Payment Amount"); ?>
                </td>
                <td>
                    <?php // Force to display the order currency, instead of the global one 
                    echo TiendaHelperBase::currency( $row->order_total, $row->currency );
                    ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Associated Payment Records"); ?>
                </td>
                <td>
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
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Billing Address"); ?>
                </td>
                <td>
                    <?php
                    echo $row->billing_first_name." ".$row->billing_last_name."<br/>";
                    echo $row->billing_address_1.", ";
                    echo $row->billing_address_2 ? $row->billing_address_2.", " : "";
                    echo $row->billing_city.", ";
                    echo $row->billing_zone_name." ";
                    echo $row->billing_postal_code." ";
                    echo $row->billing_country_name;
                    ?>
                </td>
            </tr>
            </table>
            
        </fieldset>
        
        <?php
            // fire plugin event here to enable extending the form
            JDispatcher::getInstance()->trigger('onBeforeDisplayOrderViewOrderItems', array( $row ) );                    
        ?>

        <div id="orderitems">
    	<fieldset>
    	    <legend><?php echo JText::_('Items in Order'); ?></legend>
    
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
                        <?php echo JText::_( $item->orderitem_name ); ?>
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
            </fieldset>
        </div>

        <?php
            // fire plugin event here to enable extending the form
            JDispatcher::getInstance()->trigger('onAfterDisplayOrderViewOrderItems', array( $row ) );                    
        ?>

    </td>
    <td style="width: 50%; vertical-align: top;">

        <?php
            // fire plugin event here to enable extending the form
            JDispatcher::getInstance()->trigger('onBeforeDisplayOrderViewOrderHistory', array( $row ) );                    
        ?>

    	<?php
    	if (!empty($histories))
    	{ 
    	?>
        <div id="orderhistory">
        <fieldset>
            <legend><?php echo JText::_('Order History'); ?></legend>
    
            <table class="adminlist" style="clear: both;">
            <thead>
                <tr>
                    <th style="text-align: left;"><?php echo JText::_("Date"); ?></th>
                    <th style="text-align: center;"><?php echo JText::_("Status"); ?></th>
                    <th style="text-align: center;"><?php echo JText::_("Notification Sent"); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $i=0; $k=0; ?>
            <?php foreach (@$histories as $history) : ?>
                <tr class='row<?php echo $k; ?>'>
                    <td style="text-align: left;">
                        <?php echo JHTML::_('date', $history->date_added, TiendaConfig::getInstance()->get('date_format')); ?>
                    </td>
                    <td style="text-align: center;">
                        <?php echo JText::_( $history->order_state_name ); ?>
                    </td>
                    <td style="text-align: center;">
                        <?php echo TiendaGrid::boolean( $history->notify_customer ); ?>
                    </td>
                </tr>
                <?php
                if (!empty($history->comments))
                { 
                    ?>
    	            <tr class='row<?php echo $k; ?>'>
    	                <td colspan="3" style="text-align: left; padding-left: 10px;">
    	                    <b><?php echo JText::_( "Comments" ); ?></b>:
    	                    <?php echo $history->comments; ?>
    	                </td>
    	            </tr>            	
                    <?php 
                }
                ?>
                
            <?php $i=$i+1; $k = (1 - $k); ?>
            <?php endforeach; ?>
            
            <?php if (empty($histories)) : ?>
                <tr>
                    <td colspan="10" align="center">
                        <?php echo JText::_('No order history found'); ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
            </table>
            </fieldset>
        </div>
        <?php
    	}
        ?>
    
    	<fieldset>
    	<legend><?php echo JText::_('Update Order'); ?></legend>
    	
    	<table class="admintable" style="clear: both; width: 100%;">
    	<tr>
    	    <td style="width: 100px; text-align: right;" class="key">
    	        <?php echo JText::_("New Status"); ?>
    	    </td>
    	    <td>
    	        <input value="<?php echo JText::_( "Update Order" ); ?>" onclick="document.getElementById('task').value='update_status'; this.form.submit();" style="float: right;" type="button" />
    	        <?php echo TiendaSelect::orderstate( $row->order_state_id, 'new_orderstate_id' ); ?>
    	    </td>
    	</tr>
    	<tr>
        	<td style="width: 100px; text-align: right;" class="key">
                <?php echo JText::_("Do Completed Order Tasks")."?"; ?>
            </td>
        	<td>
        	   <?php if (empty($order->completed_tasks)) {?>
        	     <input id="completed_tasks" name="completed_tasks" type="checkbox" />
        	     <?php } else {?>
        	     <input id="completed_tasks" name="completed_tasks" type="checkbox" checked="checked" disabled="disabled" />
        	     <?php }?>
        	</td>	   
    	</tr>	
    	<tr>
    	    <td style="width: 100px; text-align: right;" class="key">
    	        <?php echo JText::_("Notify Customer about Change in Status"); ?>
    	    </td>
    	    <td>
    	        <?php echo TiendaSelect::booleans( '0', 'new_orderstate_notify', '', '', '', '', 'Yes', 'No' ); ?>
    	    </td>
    	</tr>
    	<tr>
    	    <td style="width: 100px; text-align: right;" class="key">
    	        <?php echo JText::_("Comments"); ?>
    	    </td>
    	    <td>
                <textarea name="new_orderstate_comments" rows="5" style="width: 100%;"></textarea>
    	    </td>
    	</tr>
    	</table>
    	
    	</fieldset>

        <?php
            // fire plugin event here to enable extending the form
            JDispatcher::getInstance()->trigger('onAfterDisplayOrderViewOrderHistory', array( $row ) );                    
        ?>

        </td>
    </tr>
    </table>

    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onAfterDisplayOrderView', array( $row ) );                    
    ?>
    
    <input type="hidden" name="prev" value="<?php echo intval(@$surrounding["prev"]); ?>" />
    <input type="hidden" name="next" value="<?php echo intval(@$surrounding["next"]); ?>" />        
    <input type="hidden" name="id" value="<?php echo @$row->order_id; ?>" />
    <input type="hidden" name="task" id="task" value="" />
        
</form>