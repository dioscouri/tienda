<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>
<?php $shop_info = @$this->shop_info; ?>
<?php $surrounding = @$this->surrounding; ?>
<?php $order = @$this->order; ?>
<?php $items = @$order->orderitems; ?>
<?php $histories = @$row->orderhistory ? @$row->orderhistory : array(); ?>
<?php $config = Tienda::getInstance(); ?>
<?php Tienda::load( 'TiendaHelperOrder', 'helpers.order' );?>

<div id="order_email">
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplayOrderPrint', array( $row ) );                    
    ?>
    
	<div id="order_shop_info">
		<strong><?php echo $config->get('shop_name', ''); ?></strong><br />
		
        <?php echo $config->get('shop_company_name', ''); ?><br />
		<?php echo $config->get('shop_owner_name', ''); ?><br />
		
		<?php if ($config->get('shop_address_1', '')) { ?>
            <strong><?php echo JText::_('COM_TIENDA_ADDRESS'); ?></strong>: <br />
            <?php echo $config->get('shop_address_1', ''); ?>, <?php echo $config->get('shop_address_2', ''); ?><br />
            <?php echo $config->get('shop_city', ''); ?>, <?php echo $shop_info->shop_zone_name; ?>, <?php echo $config->get('shop_zip', ''); ?>, <?php echo $shop_info->shop_country_name; ?><br />		
		<?php } ?>
		
		<?php if ($config->get('shop_phone', '')) { ?>
            <strong><?php echo JText::_('COM_TIENDA_PHONE'); ?></strong>: <?php echo $config->get('shop_phone', ''); ?><br />
		<?php } ?>
        <?php if ($config->get('shop_tax_number_1', '')) { ?>
            <strong><?php echo JText::_('COM_TIENDA_TAX_NUMBER_1'); ?></strong>:<?php echo $config->get('shop_tax_number_1', ''); ?><br />
        <?php } ?>
        <?php if ($config->get('shop_tax_number_2', '')) { ?>
            <strong><?php echo JText::_('COM_TIENDA_TAX_NUMBER_2'); ?></strong>:<?php echo $config->get('shop_tax_number_2', ''); ?>
        <?php } ?>
	</div>
		
    <div id="order_info">
        <h3><?php echo JText::_('COM_TIENDA_ORDER_INFORMATION'); ?></h3>
        <strong><?php echo JText::_('COM_TIENDA_ORDER_ID'); ?></strong>: <?php echo TiendaHelperOrder::displayOrderNumber( $row ); ?><br/>
        <strong><?php echo JText::_('COM_TIENDA_DATE'); ?></strong>: <?php echo JHTML::_('date', $row->created_date, Tienda::getInstance()->get('date_format')); ?><br/>
        <strong><?php echo JText::_('COM_TIENDA_STATUS'); ?></strong>: <?php echo @$row->order_state_name; ?><br/>
    </div>
    
    <div id="payment_info">
        <h3><?php echo JText::_('COM_TIENDA_PAYMENT_INFORMATION'); ?></h3>
        <strong><?php echo JText::_('COM_TIENDA_AMOUNT'); ?></strong>: <?php echo TiendaHelperBase::currency( $row->order_total, $row->currency ); ?><br/>
        <strong><?php echo JText::_('COM_TIENDA_BILLING_ADDRESS'); ?></strong>: 
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
        <strong><?php echo JText::_('COM_TIENDA_ASSOCIATED_PAYMENT_RECORDS'); ?></strong>:
            <div>
                <?php
                if (!empty($row->orderpayments))
                {
                    foreach ($row->orderpayments as $orderpayment)
                    {
                        echo JText::_('COM_TIENDA_PAYMENT_ID').": ".$orderpayment->orderpayment_id."<br/>";
                    }
                } 
                ?>
            </div> 
        <br/>
    </div>

    <?php if ($row->order_ships) { ?>
        <div id="shipping_info">
            <h3><?php echo JText::_('COM_TIENDA_SHIPPING_INFORMATION'); ?></h3>
            <strong><?php echo JText::_('COM_TIENDA_SHIPPING_METHOD'); ?></strong>: <?php echo JText::_( $row->ordershipping_name ); ?><br/>
            <strong><?php echo JText::_('COM_TIENDA_SHIPPING_ADDRESS'); ?></strong>: 
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
    <?php } ?>
    
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplayOrderPrintOrderItems', array( $row ) );                    
    ?>
    
    <div id="items_info">
        <h3><?php echo JText::_('COM_TIENDA_ITEMS_IN_ORDER'); ?></h3>
        
        <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="text-align: left;"><?php echo JText::_('COM_TIENDA_ITEM'); ?></th>
                <th style="width: 150px; text-align: center;"><?php echo JText::_('COM_TIENDA_QUANTITY'); ?></th>
                <th style="width: 150px; text-align: right;"><?php echo JText::_('COM_TIENDA_AMOUNT'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
                <td>
                    <?php echo JText::_( $item->orderitem_name ); ?>
                    <br/>
                    
                    <?php if (!empty($item->orderitem_attribute_names)) : ?>
                        <?php echo $item->orderitem_attribute_names; ?>
                        <br/>
                    <?php endif; ?>
                    
                    <?php if (!empty($item->orderitem_sku)) : ?>
                        <b><?php echo JText::_('COM_TIENDA_SKU'); ?>:</b>
                        <?php echo $item->orderitem_sku; ?>
                        <br/>
                    <?php endif; ?>
                    
                    <b><?php echo JText::_('COM_TIENDA_PRICE'); ?>:</b>
                    <?php echo TiendaHelperBase::currency( $item->orderitem_price, $row->currency ); ?>
                    
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
                    <?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
        <tfoot>
        <tr>
            <th colspan="2" style="text-align: right;">
            <?php echo JText::_('COM_TIENDA_SUBTOTAL'); ?>
            </th>
            <th style="text-align: right;">
            <?php echo TiendaHelperBase::currency($order->order_subtotal, $row->currency); ?>
            </th>
        </tr>
        <?php echo $this->displayTaxes(); ?>
        <tr>
            <th colspan="2" style="font-size: 120%; text-align: right;">
            <?php echo JText::_('COM_TIENDA_TOTAL'); ?>
            </th>
            <th style="font-size: 120%; text-align: right;">
            <?php echo TiendaHelperBase::currency($row->order_total, $row->currency); ?>
            </th>
        </tr>
        </tfoot>
        </table>
    </div>

    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onAfterDisplayOrderPrintOrderItems', array( $row ) );                    
    ?>

    <?php if (!empty($row->customer_note)) : ?>
        <div id="customer_note">
            <h3><?php echo JText::_('COM_TIENDA_NOTE'); ?></h3>
            <span><?php echo @$row->customer_note; ?></span>
        </div>
    <?php endif; ?>
    
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onAfterDisplayOrderPrint', array( $row ) );                    
    ?>
</div>