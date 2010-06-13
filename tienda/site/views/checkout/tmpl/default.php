<?php 
    defined('_JEXEC') or die('Restricted access'); 
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); 
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); 
	JHTML::_('script', 'tienda_checkout.js', 'media/com_tienda/js/');
	$form = @$this->form; 
	$row = @$this->row;
	$baseurl = "index.php?option=com_tienda&format=raw&controller=addresses&task=getAddress&address_id="; 
?>
<div class='componentheading'>
    <span><?php echo JText::_( "Select Addresses and Shipping Method" ); ?></span>
</div>

    <?php // if ($menu =& TiendaMenu::getInstance()) { $menu->display(); } ?>
    
<div id='onCheckout_wrapper'>

	<!-- Progress Bar -->
	<?php echo $this->progress; ?>

    <form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" name="adminForm" enctype="multipart/form-data">
        
        <!--    ORDER SUMMARY   -->
        <h3><?php echo JText::_("Order Summary") ?></h3>
        <div id='onCheckoutCart_wrapper'> 
			<?php
                echo @$this->orderSummary;
 		    ?>
        </div>

        <h3>
            <?php echo JText::_("Select Shipping and Billing Addresses") ?>
        </h3>

        <table style="clear: both;">
        <tr>
            <td colspan="2">
                <div class='note'>
	                <?php $text = JText::_( "Click Here to Manage Your Stored Addresses" )."."; ?>
	                <?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=addresses&tmpl=component", $text, array('update' => true) );  ?>
                    <?php echo JText::_( "CHECKOUT MANAGE ADDRESSES INSTRUCTIONS" ); ?>
                </div>
            </td>
        </tr>
        
        <tr>
            <td style="text-align: left;">
                <!--    BILLING ADDRESS   -->             
                <h4 id='billing_address_header' class="address_header">
                    <?php echo JText::_("Billing Address") ?>
                </h4>
                <?php 
                    if (!empty($this->addresses))
                    {
                        $billattribs = array(
                           'class' => 'inputbox',    
                           'size' => '1',
                           'onchange' => "tiendaDoTask('$baseurl'+this.options[this.selectedIndex].value, 'billingDefaultAddress', ''); tiendaGetCheckoutTotals();"
                        );
                        
                        // display select list of stored addresses
                        echo TiendaSelect::address( JFactory::getUser()->id, @$this->billing_address->address_id, 'billing_address_id', 1, $billattribs, 'billing_address_id', false );
                        
                        if (count($this->addresses) == 1)
                        {
                            echo "<input type=\"hidden\" id=\"billing_address_id\" name=\"billing_address_id\" value=\"" . @$this->billing_address->address_id . "\" />";
                        }
                    }
                ?>
                
                <!--    BILLING ADDRESS FORM  -->
                <span id="billingDefaultAddress">
                   <?php if (empty($this->addresses)) : ?>
                       <?php echo @$this->billing_address_form; ?>
                   <?php else : ?>
                   <?php echo @$this->default_billing_address; ?>
                   <?php endif; ?>
                </span>
            </td>
        </tr>
        <tr>
            <td style="text-align: left;">
                <!-- SHIPPING ADDRESS   -->
                <?php if($this->showShipping) { ?>
	            <h4 id='shipping_address_header' class="address_header">
	               <?php echo JText::_("Shipping Address") ?>
	            </h4>
                
	            <?php
                if (!empty($this->addresses))
                {
	                $shipattribs = array(
	                   'class' => 'inputbox',    
	                   'size' => '1',
	                   'onchange' => "tiendaDoTask('$baseurl'+this.options[this.selectedIndex].value, 'shippingDefaultAddress', ''); tiendaGetCheckoutTotals();"
	                );
	                
	                // display select list of stored addresses
	                echo TiendaSelect::address( JFactory::getUser()->id, @$this->shipping_address->address_id, 'shipping_address_id', 2, $shipattribs, 'shipping_address_id', false );
	                
	               	if (count($this->addresses) == 1)
	               	{
	               		echo "<input type=\"hidden\" id=\"shipping_address_id\" name=\"shipping_address_id\" value=\"" . @$this->shipping_address->address_id . "\" />";
	               	}
				}
				?>

                <?php if (empty($this->addresses)) : ?>
                    <div>
                        <input id="sameasbilling" name="sameasbilling" type="checkbox" onclick="tiendaDisableShippingAddressControls(this);" />&nbsp;
                        <?php echo JText::_( 'Same As Billing Address' ); ?>:
                    </div>
				<?php endif; ?>
				
				<!--    SHIPPING ADDRESS FORM  -->
	            <span id="shippingDefaultAddress">
	               <?php if (empty($this->addresses)) : ?>
	                   <?php echo @$this->shipping_address_form; ?>
	               <?php else : ?>
	               <?php echo @$this->default_shipping_address; ?>
	               <?php endif; ?>
	            </span>
	            
            </td>
        </tr>
         <?php } ?>
        </table>
       
        <!-- SHIPPING METHODS -->
        <div id='onCheckoutShipping_wrapper'>
            <?php echo $this->shipping_method_form; ?>
        </div>         
            
        <h3><?php echo JText::_("Continue Checkout") ?></h3>
        
        <div id="validationmessage"></div>
        
        <!--    SUBMIT   -->    
            <input type="button" class="button" onclick="window.location = '<?php echo JRoute::_('index.php?option=com_tienda&view=carts'); ?>'" value="<?php echo JText::_('Return to Shopping Cart'); ?>" />
            <input type="button" class="button" onclick="tiendaFormValidation( '<?php echo @$form['validation']; ?>', 'validationmessage', 'selectpayment', document.adminForm )" value="<?php echo JText::_('Select Payment Method'); ?>" />	
    		<input type="hidden" id="currency_id" name="currency_id" value="<?php echo $this->order->currency_id; ?>" />
    		<input type="hidden" id="step" name="step" value="selectshipping" />
    		<input type="hidden" id="task" name="task" value="" />
        
        <?php echo $this->form['validate']; ?>
    </form>
</div>
