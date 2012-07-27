<?php
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	$form = @$this->form;
	$row = @$this->row;
	$order = @$this->order;
	$items = @$order->getItems();
	$surrounding = @$this->surrounding;
	$histories = @$row->orderhistory ? @$row->orderhistory : array();
	$guest = $row->user_id < Tienda::getGuestIdStart();
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

    <table>
        <tr>
            <td align="left" width="100%">
			    <?php
			    echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=orders")."'>".JText::_('COM_TIENDA_RETURN_TO_LIST')."</a>";
			    ?>
            </td>
            <td nowrap="nowrap" style="text-align: right; padding: 0px 5px;">
                <input type="button" onclick="window.location='<?php echo JRoute::_( "index.php?option=com_tienda&view=orders&task=editaddresses&id=" . @$row->order_id ); ?>'" value="<?php echo JText::_('COM_TIENDA_EDIT_ADDRESSES'); ?>"/>
            </td>
            <td nowrap="nowrap" style="text-align: right; padding: 0px 5px;">
                <input value="<?php echo JText::_('COM_TIENDA_RESEND_EMAIL_INVOICE'); ?>" onclick="document.getElementById('task').value='resend_email'; this.form.submit();" style="float: right;" type="button" />
            </td>
            <td nowrap="nowrap" style="text-align: right; padding: 0px 5px;">
                [<?php
                $url = "index.php?option=com_tienda&view=orders&task=print&tmpl=component&id=".@$row->order_id;
                $text = JText::_('COM_TIENDA_PRINT_INVOICE');
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
            <legend><?php echo JText::_('COM_TIENDA_ORDER_INFORMATION'); ?></legend>
	        	
		    <table class="table table-striped table-bordered" style="clear: both;">
		    <tr>
		        <td style="width: 100px; text-align: right;" class="key">
		            <?php echo JText::_('COM_TIENDA_ORDER_ID'); ?>
		        </td>
		        <td>
		            <?php echo $row->order_id; ?>
		        </td>
		    </tr>
		    <tr>
		        <td style="width: 100px; text-align: right;" class="key">
		            <?php echo JText::_('COM_TIENDA_ORDER_DATE'); ?>
		        </td>
		        <td>
		            <?php echo JHTML::_('date', $row->created_date, Tienda::getInstance()->get('date_format')); ?>
		        </td>
		    </tr>
		    <tr>
		        <td style="width: 100px; text-align: right;" class="key">
		            <?php echo JText::_('COM_TIENDA_ORDER_STATUS'); ?>
		        </td>
		        <td>
		            <?php echo $row->order_state_name; ?>
		        </td>
		    </tr>
            <?php if (!empty($row->commissions)) { ?>
                <?php JHTML::_('behavior.tooltip'); ?>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_COMMISSIONS'); ?>
                        <img src='<?php echo JURI::root(true); ?>/media/com_amigos/images/amigos_16.png' title="<?php echo JText::_('COM_TIENDA_ORDER_HAS_A_COMMISSION'); ?>" class="hasTip" />
                    </td>
                    <td>
                        <a href="index.php?option=com_amigos&view=commissions&filter_orderid=<?php echo $row->order_id; ?>" target="_blank">
                            <?php echo JText::_('COM_TIENDA_VIEW_COMMISSION_RECORDS'); ?>
                        </a>
                    </td>
                </tr>
            <?php } ?>		    
		    
		    </table>
		    </fieldset>
		    
            <fieldset>
            <legend><?php echo JText::_('COM_TIENDA_CUSTOMER_INFORMATION'); ?></legend>
                
            <table class="table table-striped table-bordered" style="clear: both;">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_('COM_TIENDA_NAME'); ?>
                </td>
                <td>
                	<?php
                	if( $guest )
                	{
                		echo JText::_('COM_TIENDA_GUEST');
                	}
                	else
                	{
                		echo $row->user_name;
                		if ( !empty( $row->user_id ) )
                		{
                			?>
		                    [
		                    <a href="index.php?option=com_tienda&view=users&task=view&id=<?php echo $row->user_id; ?>"><?php echo $row->user_id; ?></a>
		                    ]
                			<?php
                		}
                	}
                	?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_('COM_TIENDA_EMAIL'); ?>
                </td>
                <td>
                	<?php
                		if( $guest ) 
                		{
                			if( Tienda::getInstance()->get( 'obfuscate_guest_email', 0 ) ) // obfuscate guest email
                				echo '*****';
                			else
	                			echo $row->userinfo_email;
                		}
                		else
											echo $row->email;
                	?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_('COM_TIENDA_IP_ADDRESS'); ?>
                </td>
                <td>
                    <?php echo $row->ip_address; ?>
                </td>
            </tr>
            
            <?php if (@$row->customer_note) : ?>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_('COM_TIENDA_NOTE'); ?>
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
            <legend><?php echo JText::_('COM_TIENDA_SHIPPING_INFORMATION'); ?></legend>
            	        
		    <table class="admintable" style="clear: both;">
		    <tr>
		        <td style="width: 100px; text-align: right;" class="key">
		            <?php echo JText::_('COM_TIENDA_SHIPPING_METHOD'); ?>
		        </td>
	            <td>
	                <?php echo JText::_( $row->ordershipping_name ); ?>
	            </td>
	        </tr>
	        <tr>
		        <td style="width: 100px; text-align: right;" class="key">
		            <?php echo JText::_('COM_TIENDA_SHIPPING_ADDRESS'); ?>
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
            <legend><?php echo JText::_('COM_TIENDA_PAYMENT_INFORMATION'); ?></legend>
            <?php // TODO Make this assume multiple payments, and display all of them ?>
            <table class="table table-striped table-bordered" style="clear: both;">
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_('COM_TIENDA_PAYMENT_AMOUNT'); ?>
                </td>
                <td>
                    <?php // Force to display the order currency, instead of the global one 
                    echo TiendaHelperBase::currency( $row->order_total, $row->currency );
                    ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_('COM_TIENDA_ASSOCIATED_PAYMENT_RECORDS'); ?>
                </td>
                <td>
	                <?php
	                if (!empty($row->orderpayments))
	                {
	                    foreach ($row->orderpayments as $orderpayment)
	                    {
	                        // TODO Make these link to view them
	                        echo JText::_('COM_TIENDA_PAYMENT_ID'); ?>:                             
	                        <a href="index.php?option=com_tienda&view=orderpayments&task=edit&id=<?php echo $orderpayment->orderpayment_id; ?>">
                            <?php echo $orderpayment->orderpayment_id; ?>
                            </a>
	                        <br/>
	                        <?php
	                        echo JText::_('COM_TIENDA_PAYMENT_TYPE').": ".JText::_($orderpayment->orderpayment_type)."<br/>";
	                        echo JText::_('COM_TIENDA_DETAILS').": ".JText::_($orderpayment->transaction_details)."<br/>";
	                    }
	                } 
	                ?>
                </td>
            </tr>
            <tr>
                <td style="width: 100px; text-align: right;" class="key">
                    <?php echo JText::_('COM_TIENDA_BILLING_ADDRESS'); ?>
                </td>
                <td>
                    <?php
                    echo $row->billing_first_name." ".$row->billing_last_name."<br/>";
                    echo $row->billing_address_1.", ";
                    echo $row->billing_address_2 ? $row->billing_address_2.", " : "";
                    echo $row->billing_city.", ";
                    echo $row->billing_zone_name." ";
                    echo $row->billing_postal_code." ";
                    echo $row->billing_country_name."<br />";
                    echo $row->billing_tax_number.'<br />';
					echo $row->billing_phone_1;
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
    	    <legend><?php echo JText::_('COM_TIENDA_ITEMS_IN_ORDER'); ?></legend>
    
            <table class="table table-striped table-bordered" style="clear: both;">
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

                        <?php if ($item->orderitem_recurs) : ?>
                            <?php $recurring_subtotal = $item->recurring_price; ?>
                            <?php echo JText::_('COM_TIENDA_RECURRING_PRICE'); ?>: <?php echo TiendaHelperBase::currency($item->recurring_price); ?>
                            (<?php echo $item->recurring_payments . " " . JText::_('COM_TIENDA_PAYMENTS'); ?>, <?php echo $item->recurring_period_interval." ". JText::_('COM_TIENDA_PERIOD_UNIT_'.$item->recurring_period_unit)." ".JText::_('COM_TIENDA_PERIODS'); ?>) 
                            <?php if ($item->recurring_trial) : ?>
                                <br/>
                                <?php echo JText::_('COM_TIENDA_TRIAL_PERIOD_PRICE'); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
                                (<?php echo "1 " . JText::_('COM_TIENDA_PAYMENT'); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_('COM_TIENDA_PERIOD_UNIT_'.$item->recurring_period_unit)." ".JText::_('COM_TIENDA_PERIOD'); ?>)
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
					    
					    <a href="index.php?option=com_tienda&view=orderitems&task=edit&id=<?php echo $item->orderitem_id; ?>"><?php echo JText::_('COM_TIENDA_VIEW_ORDERITEM_DETAILS'); ?></a>
					    
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
            
            <?php if (!empty($row->order_discount)) : ?>
            <tr>
                <th colspan="2" style="text-align: right;">
                    <?php echo JText::_('COM_TIENDA_DISCOUNT'); ?>
                </th>
                <td colspan="3" style="text-align: right;">
                    <?php echo TiendaHelperBase::currency($row->order_discount); ?>
                </td>
            </tr>
          <?php
          	endif;
	              $display_tax_checkout = Tienda::getInstance()->get('show_tax_checkout', '1');
	                    	
	              switch( $display_tax_checkout )
	              {
	              	case 1 : // Tax Rates in Separate Lines
		                	foreach ( $row->ordertaxrates as $taxrate)
		                  {
		                   	$tax_desc = $taxrate->ordertaxrate_description ? $taxrate->ordertaxrate_description : 'Tax';
		                   	$amount = $taxrate->ordertaxrate_amount;
		                   	if ( $amount )
		                   	{
		                  ?>
		      <tr>
            <th colspan="2" style="text-align: right;">
							<?php echo JText::_( $tax_desc ).":"; ?>
						</th>
            <th style="text-align: right;">
							<?php echo TiendaHelperBase::currency( $amount, $row->currency); ?>
						</th>
					</tr>
  	                  <?php
		                    }
		                  }
	              		break;
	              	case 2 : // Tax Classes in Separate Lines
		                	foreach ( $row->ordertaxclasses as $taxclass)
		                  {
		                   	$tax_desc = $taxclass->ordertaxclass_description ? $taxclass->ordertaxclass_description : 'Tax';
		                   	$amount = $taxclass->ordertaxclass_amount;
		                   	if ( $amount )
		                   	{
		                  ?>
		      <tr>
            <th colspan="2" style="text-align: right;">
							<?php echo JText::_( $tax_desc ).":"; ?>
						</th>
            <th style="text-align: right;">
							<?php echo TiendaHelperBase::currency( $amount , $row->currency); ?>
						</th>
					</tr>
  	                  <?php
		                    }
		                  }
	              		break;
	              	case 3 : // Tax Classes and Tax Rates in Separate Lines
		                	foreach ( $row->ordertaxclasses as $taxclass)
		                  {
		                   	$tax_desc = $taxclass->ordertaxclass_description ? $taxclass->ordertaxclass_description : 'Tax';
		                   	$amount = $taxclass->ordertaxclass_amount;
		                   	if ( $amount )
		                   	{
		                  ?>
		      <tr>
            <th colspan="2" style="text-align: right;">
							<?php echo JText::_( $tax_desc ).":"; ?>
						</th>
            <th style="text-align: right;">
							<?php echo TiendaHelperBase::currency( $amount , $row->currency); ?>
						</th>
				</tr>
  	                  <?php
		                     }
		                     foreach( $row->ordertaxrates as $taxrate )
		                     {
				                   	$tax_desc = $taxrate->ordertaxrate_description ? $taxrate->ordertaxrate_description : 'Tax';
				                   	$amount = $taxrate->ordertaxrate_amount;
				                   	if ( $amount && $taxrate->ordertaxclass_id == $taxclass->tax_class_id )
				                   	{
				                  ?>
				  <tr>
            <th colspan="2" style="text-align: right;">
							<?php echo JText::_( $tax_desc )." &nbsp;&nbsp; :"; ?></span>
						</th>
            <th style="text-align: right;">
							<?php echo TiendaHelperBase::currency( $amount, $row->currency); ?>
						</th>
					</tr>
		  	                  <?php
		                     		}
		                     }
		                  }
	              		break;
	              	case 4 : // All in One Line
	                	if( $row->order_tax )
	                    {
	                    	?>
            <th colspan="2" style="text-align: right;">
	            	<?php
                    	if (!empty($this->show_tax)) { echo JText::_('COM_TIENDA_PRODUCT_TAX_INCLUDED').":"; }
                    	else { echo JText::_('COM_TIENDA_PRODUCT_TAX').":"; }    
	            	?>
	          </th>
            <th style="text-align: right;">
							 <?php echo TiendaHelperBase::currency($row->order_tax) ?>
						</th>
							            <?php
	                    }
	              		break;
	              }
                ?>
            <tr>
                <th colspan="2" style="text-align: right;">
                <?php echo JText::_('COM_TIENDA_SHIPPING'); ?>
                </th>
                <th style="text-align: right;">
                <?php echo TiendaHelperBase::currency($row->order_shipping, $row->currency); ?>
                </th>
            </tr>
			<tr>
				<th colspan="2"style="text-align: right;">
					<?php echo JText::_('COM_TIENDA_SHIPPING_TAX'); ?>:
				</th>
				<td style="text-align: right;">
					<?php echo TiendaHelperBase::currency( @$row->order_shipping_tax, $row->currency ); ?>
				</td>
			</tr>
			<?php if ((float) $row->order_credit > (float) '0.00') : ?>
            <tr>
                <th colspan="2" style="text-align: right;">
                    <?php echo JText::_('COM_TIENDA_STORE_CREDIT'); ?>
                </th>
                <th style="text-align: right;">
                    - <?php echo TiendaHelperBase::currency($row->order_credit, $row->currency); ?>
                </th>
            </tr>
            <?php endif; ?>
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
            <legend><?php echo JText::_('COM_TIENDA_ORDER_HISTORY'); ?></legend>
    
            <table class="adminlist" style="clear: both;">
            <thead>
                <tr>
                    <th style="text-align: left;"><?php echo JText::_('COM_TIENDA_DATE'); ?></th>
                    <th style="text-align: center;"><?php echo JText::_('COM_TIENDA_STATUS'); ?></th>
                    <th style="text-align: center;"><?php echo JText::_('COM_TIENDA_NOTIFICATION_SENT'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php $i=0; $k=0; ?>
            <?php foreach (@$histories as $history) : ?>
                <tr class='row<?php echo $k; ?>'>
                    <td style="text-align: left;">
                        <?php echo JHTML::_('date', $history->date_added, Tienda::getInstance()->get('date_format')); ?>
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
    	                    <b><?php echo JText::_('COM_TIENDA_COMMENTS'); ?></b>:
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
                        <?php echo JText::_('COM_TIENDA_NO_ORDER_HISTORY_FOUND'); ?>
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
    	<legend><?php echo JText::_('COM_TIENDA_UPDATE_ORDER'); ?></legend>
    	
    	<table class="admintable" style="clear: both; width: 100%;">
    	<tr>
    	    <td style="width: 100px; text-align: right;" class="key">
    	        <?php echo JText::_('COM_TIENDA_NEW_STATUS'); ?>
    	    </td>
    	    <td>
    	        <input value="<?php echo JText::_('COM_TIENDA_UPDATE_ORDER'); ?>" onclick="document.getElementById('task').value='update_status'; this.form.submit();" style="float: right;" type="button" />
    	        <?php 
			$url = "index.php?option=com_tienda&format=raw&controller=orders&task=updateStatusTextarea&orderstate_selected=";
			$onchange = 'tiendaPutAjaxLoader( \'update_order\' );tiendaDoTask( \''.$url.'\'+document.getElementById(\'new_orderstate_id\').value, \'update_order\', \'\', \'\', false );';
			$attribs = array('class' => 'inputbox', 'size' => '1','onchange'=>$onchange);
			echo TiendaSelect::orderstate( $row->order_state_id, 'new_orderstate_id',$attribs );
		?> 
    	    </td>
    	</tr>
    	<tr>
        	<td style="width: 100px; text-align: right;" class="key">
                <?php echo JText::_('COM_TIENDA_DO_COMPLETED_ORDER_TASKS')."?"; ?>
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
    	        <?php echo JText::_('COM_TIENDA_SEND_EMAIL_TO_CUSTOMER'); ?>
    	    </td>
    	    <td>
    	        <?php echo TiendaSelect::booleans( '0', 'new_orderstate_notify', '', '', '', '', 'Yes', 'No' ); ?>
    	    </td>
    	</tr>
    	<tr>
    	    <td style="width: 100px; text-align: right;" class="key">
    	        <?php echo JText::_('COM_TIENDA_COMMENTS'); ?>
    	    </td>
    	    <td>
		<div id="update_order">
			<textarea name="new_orderstate_comments" rows="5" style="width: 100%;"></textarea>
		</div>
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