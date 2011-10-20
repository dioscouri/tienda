<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('script', 'tienda_admin.js', 'media/com_tienda/js/');
Tienda::load( 'TiendaGrid', 'library.grid' );
$state = @$this->state;
$order = @$this->order;
$items = @$this->orderitems;
?>
<div class="cartitems">
           <table class="adminlist" style="clear: both;">
            <thead>
                <tr>
                    <th style="text-align: left;"><?php echo JText::_( "PRODUCT" ); ?></th>
                    <th style="width: 50px;"><?php echo JText::_( "QUANTITY" ); ?></th>
                    <th style="width: 50px;"><?php echo JText::_( "TOTAL" ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $i=0; $k=0; ?> 
            <?php foreach ($items as $item) : ?>
                <tr class="row<?php echo $k; ?>">
                    <td>
                        <a href="<?php echo JRoute::_("index.php?option=com_tienda&controller=products&view=products&task=view&id=".$item->product_id); ?>">
                            <?php echo $item->orderitem_name; ?>
                        </a>
                        <br/>
                        
                        <?php if (!empty($item->orderitem_attribute_names)) : ?>
                            <?php echo $item->orderitem_attribute_names; ?>
                            <br/>
                        <?php endif; ?>
                        
                        <?php if (!empty($item->orderitem_sku)) : ?>
                            <b><?php echo JText::_( "SKU" ); ?>:</b>
                            <?php echo $item->orderitem_sku; ?>
                            <br/>
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
                            <?php echo JText::_( "Price" ); ?>:
                            <?php echo TiendaHelperBase::currency($item->price); ?>                         
                        <?php endif; ?> 
                        
					    <?php if (!empty($this->onDisplayOrderItem) && (!empty($this->onDisplayOrderItem[$i]))) : ?>
					        <div class='onDisplayOrderItem_wrapper_<?php echo $i?>'>
					        <?php echo $this->onDisplayOrderItem[$i]; ?>
					        </div>
					    <?php endif; ?>  

                    </td>
                    <td style="width: 50px; text-align: center;">
                        <?php echo $item->orderitem_quantity;?>  
                    </td>
                    <td style="text-align: right;">
                        <?php echo TiendaHelperBase::currency($item->orderitem_final_price); ?>
                                               
                    </td>
                </tr>
              
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: left;font-weight: bold; white-space: nowrap;">
                        <?php echo JText::_( "Subtotal" ); ?>
                    </td>
                    <td colspan="3" style="text-align: right;">
                        <?php echo TiendaHelperBase::currency($order->order_subtotal); ?>
                    </td>
                </tr>
                
                <?php if (!empty($order->_coupons['order_price'])) : ?>
                <tr>
                    <td colspan="2" style="text-align: left;font-weight: bold; white-space: nowrap;">
                        <?php echo JText::_( "DISCOUNT" ); ?>
                    </td>
                    <td colspan="3" style="text-align: right;">
                        <?php echo TiendaHelperBase::currency($order->order_discount); ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>
        <table class="adminlist" style="clear: both;">
                <tr>
                    <td colspan="2" style="white-space: nowrap;">
                        <b><?php echo JText::_( "TAX AND SHIPPING TOTALS" ); ?></b>
                        <br/>
                    </td>
                    <td colspan="2" style="text-align: right;">
                    <?php 
                    	$display_shipping_tax = TiendaConfig::getInstance()->get('display_shipping_tax', '1');
                    	$display_taxclass_lineitems = TiendaConfig::getInstance()->get('display_taxclass_lineitems', '0');
                    	
	                    	if ($display_taxclass_lineitems)
	                    	{
	                            foreach ($order->getTaxClasses() as $taxclass)
	                            {
	                                $tax_desc = $taxclass->tax_rate_description ? $taxclass->tax_rate_description : 'Tax';
	                                if ($order->getTaxClassAmount( $taxclass->tax_class_id ))
	                                    echo JText::_( $tax_desc ).":<br/>";
	                            }
	                    	}
	                    	    else
	                    	{
		                    	if( $order->order_tax )
		                    	{
		                    		if (!empty($this->show_tax)) { echo JText::_("PRODUCT_TAX_INCLUDED").":<br>"; }
		                    	    elseif (!empty($this->using_default_geozone)) { echo JText::_("PRODUCT TAX ESTIMATE").":<br>"; } 
		                    	    else { echo JText::_("PRODUCT TAX").":<br>"; }    
		                    	}
		                    }
   						
                    	if (!empty($this->showShipping))
                    	{
                            echo JText::_("Shipping and Handling").":";
                            if ($display_shipping_tax && $order->order_shipping_tax ) {
                                echo "<br>".JText::_("SHIPPING TAX").":";
                            }                    	    
                    	}

                    ?>
                    </td>
                    <td colspan="2" style="text-align: right;">
                     <?php 
                        if ($display_taxclass_lineitems)
                        {
                            foreach ($order->getTaxClasses() as $taxclass)
                            {
                                if ($order->getTaxClassAmount( $taxclass->tax_class_id ))
                                    echo TiendaHelperBase::currency($order->getTaxClassAmount( $taxclass->tax_class_id ), $order->currency)."<br/>";
                            }
                        }
                            else
                        {
                        	if( $order->order_tax )
                            echo TiendaHelperBase::currency($order->order_tax) . "<br>";    
                        }
                        
                        if (!empty($this->showShipping))
                        {
                            echo TiendaHelperBase::currency($order->order_shipping);
                            if ($display_shipping_tax && $order->order_shipping_tax ) {
                                echo "<br>" . TiendaHelperBase::currency( (float) $order->order_shipping_tax);
                            }                               
                        }

                    ?>                  
                    </td>
                </tr>
                <tr>
                	<td colspan="3" style="font-weight: bold; white-space: nowrap;">
                        <?php echo JText::_( "Store Credit" ); ?>
                    </td>
                    <td colspan="3" style="text-align: right;">
                       - <?php echo TiendaHelperBase::currency($order->order_credit); ?>
                    </td>
                </tr> 
                <tr>
                	<td colspan="3" style="font-weight: bold; white-space: nowrap;">
                        <?php echo JText::_( "TOTAL" ); ?>
                    </td>
                    <td colspan="3" style="text-align: right;">
                        <?php echo TiendaHelperBase::currency($order->order_total); ?>
                    </td>
                </tr>                
        </table>        
</div>
