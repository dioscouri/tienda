<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?> 
<?php $order = @$this->order; ?>
<?php $items = @$row->orderitems ? @$row->orderitems : array(); ?>
<?php $histories = @$row->orderhistory ? @$row->orderhistory : array(); ?>
<?php $config = TiendaConfig::getInstance(); ?>


<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplayOrderPrint', array( $row ) );                    
    ?>

    <table style="width: 100%;">
    <tr>
    	<td colspan="2" style="vertical-align:top;">
    	
    	 <fieldset>
            <legend><?php echo JText::_('Shop Information'); ?></legend>
            
            <table class="admintable" style="float:left; width:50%;">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Shop Name"); ?>
                </td>
                <td>
                    <?php echo $config->get('shop_name', ''); ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Company Name"); ?>
                </td>
                <td>
                    <?php echo $config->get('shop_company_name', ''); ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Shop Owner"); ?>
                </td>
                <td>
                    <?php echo $config->get('shop_owner_name', '') ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Phone"); ?>
                </td>
                <td>
                    <?php echo $config->get('shop_phone', '') ?>
                </td>
            </tr>
            </table>
            
            <table class="admintable" style="float:right; width:50%;">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Address"); ?>
                </td>
                <td>
                    <?php echo $config->get('shop_address_1', '') ?>
                    <?php 
                    	$address_2 = $config->get('shop_address_2', '');
						if (!empty($address_2))
						{
						    echo "<br/>".$address_2."<br />";
						}

						echo $config->get('shop_city', ''). " ";
						echo $row->shop_zone_name. " ";
						echo $config->get('shop_zip', ''). "<br/>";
						echo $row->shop_country_name;
                    ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Tax Number 1"); ?>
                </td>
                <td>
                    <?php echo $config->get('shop_tax_number_1', ''); ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Tax Number 2"); ?>
                </td>
                <td>
                    <?php echo $config->get('shop_tax_number_2', ''); ?>
                </td>
            </tr>
            </table>
            
         </fieldset>
    	</td>
    </tr>
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
                    <?php echo $row->created_date; ?>
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
        
        </td>
        <td style="width: 50%; vertical-align: top;">
        
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
            
            <table class="admintable" style="clear: both;">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_("Payment Amount"); ?>
                </td>
                <td>
                    <?php echo TiendaHelperBase::currency( $row->order_total, $row->currency ); ?>
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
            
        </td>
    </tr>
    </table>

    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplayOrderPrintOrderItems', array( $row ) );                    
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

                    <?php if (!empty($item->orderitem_sku)) : ?>
                        <b><?php echo JText::_( "SKU" ); ?>:</b>
                        <?php echo $item->orderitem_sku; ?>
                        <?php if (!empty($item->attributes_codes)) : ?>
                            <?php echo $item->attributes_codes; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if ($item->orderitem_recurs) : ?>
                        <?php $recurring_subtotal = $item->recurring_price; ?>
                        <?php echo JText::_( "RECURRING PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_price); ?>
                        (<?php echo $item->recurring_payments . " " . JText::_( "PAYMENTS" ); ?>, <?php echo $item->recurring_period_interval." ". JText::_( "$item->recurring_period_unit PERIOD UNIT" )." ".JText::_( "PERIODS" ); ?>) 
                        <?php if ($item->recurring_trial) : ?>
                            <br/>
                            <?php echo JText::_( "TRIAL PERIOD PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
                            (<?php echo "1 " . JText::_( "PAYMENT" ); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_( "$item->recurring_trial_period_unit PERIOD UNIT" )." ".JText::_( "PERIOD" ); ?>)
                        <?php endif; ?>    
                    <?php else : ?>
                        <b><?php echo JText::_( "Price" ); ?>:</b>
                        <?php echo TiendaHelperBase::currency( $item->orderitem_price, $row->currency ); ?>                         
                    <?php endif; ?> 

                    <!-- onDisplayOrderItem event: plugins can extend order item information -->
				    <?php if (!empty($this->onDisplayOrderItem) && (!empty($this->onDisplayOrderItem[$i]))) : ?>
				        <div class='onDisplayOrderItem_wrapper_<?php echo $i?>'>
				        <?php echo $this->onDisplayOrderItem[$i]; ?>
				        </div>
				    <?php endif; ?>                        
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
        
        <?php if (!empty($row->order_discount)) : ?>
        <tr>
            <th colspan="2" style="text-align: right;">
                <?php echo JText::_( "Discount" ); ?>
            </th>
            <td colspan="3" style="text-align: right;">
                <?php echo TiendaHelperBase::currency($row->order_discount); ?>
            </td>
        </tr>
        <?php endif; ?>
        
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
        JDispatcher::getInstance()->trigger('onAfterDisplayOrderPrintOrderItems', array( $row ) );                    
    ?>
    
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onAfterDisplayOrderPrint', array( $row ) );                    
    ?>
    
    <input type="hidden" name="prev" value="<?php echo intval(@$surrounding["prev"]); ?>" />
    <input type="hidden" name="next" value="<?php echo intval(@$surrounding["next"]); ?>" />        
    <input type="hidden" name="id" value="<?php echo @$row->id; ?>" />
    <input type="hidden" name="task" id="task" value="" />
        
</form>

<script type="text/javascript">
    window.onload = tiendaPrintPage();
    function tiendaPrintPage()
    {
        window.print();
    }
</script>