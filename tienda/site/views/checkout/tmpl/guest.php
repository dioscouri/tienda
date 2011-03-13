<?php 
    defined('_JEXEC') or die('Restricted access'); 
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); 
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); 
	JHTML::_('script', 'tienda_checkout.js', 'media/com_tienda/js/');
	$form = @$this->form; 
	$row = @$this->row;
	$register = @$this->register;
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
        
        <?php if (!empty($this->onBeforeDisplaySelectShipping)) : ?>
            <div id='onBeforeDisplaySelectShipping_wrapper'>
            <?php echo $this->onBeforeDisplaySelectShipping; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($register) : ?>
        <h3>
            <?php echo JText::_("Registration") ?>
        </h3>
        <?php echo $this->form_register; ?>	
        <?php endif; ?>
       
        <h3>
            <?php echo JText::_("Set Shipping and Billing Addresses") ?>
        </h3>
         <?php if (!$register) :?>
        <h4>
            <?php echo JText::_("Your Email Address") ?>
        </h4>
       	
       	<table style="clear: both;">
        	<tr>
            <td style="text-align: left;">
                <!--    Email Address   -->             
                <input name="email_address" id="email_address" type="text" size="48" maxlength="250" /> *
            </td>
        </tr>
		</table>
		<?php endif;?>	
			
        <table style="clear: both;">
        <tr>
            <td style="text-align: left;">
                <!--    BILLING ADDRESS   -->             
                <h4 id='billing_address_header' class="address_header">
                    <?php echo JText::_("Billing Address") ?>
                </h4>                
                <!--    BILLING ADDRESS FORM  -->
                <div id="billingDefaultAddress">
                   <?php echo @$this->billing_address_form; ?>
                </div>
            </td>
        </tr>
        
        <?php if ($this->showShipping) 
        {
            ?>
            <tr>
                <td style="text-align: left;">
                    <!--    SHIPPING ADDRESS   -->
    	            <h4 id='shipping_address_header' class="address_header">
    	               <?php echo JText::_("Shipping Address") ?>
    	            </h4>
    	           
                        <div>
                            <input id="sameasbilling" name="sameasbilling" type="checkbox" onclick="tiendaDisableShippingAddressControls(this,this.form);" />&nbsp;
                            <?php echo JText::_( 'Same As Billing Address' ); ?>:
                        </div>
    				
    				<!--    SHIPPING ADDRESS FORM  -->
    	            <div id="shippingDefaultAddress">
    	                   <?php echo @$this->shipping_address_form; ?>
    	            </div>
    	            
                </td>
            </tr>
            <?php 
        } 
        ?>
        </table>
        
        <!-- SHIPPING METHODS -->
        <div id='onCheckoutShipping_wrapper'>
            <?php echo $this->shipping_method_form; ?>
        </div>
        
        <?php if (!empty($this->onAfterDisplaySelectShipping)) : ?>
            <div id='onAfterDisplaySelectShipping_wrapper'>
            <?php echo $this->onAfterDisplaySelectShipping; ?>
            </div>
        <?php endif; ?>
            
        <h3><?php echo JText::_("Continue Checkout") ?></h3>
        
        <div id="validationmessage"></div>
        
        <!--    SUBMIT   -->
            <input type="button" class="button" onclick="tiendaFormValidation( '<?php echo @$form['validation']; ?>', 'validationmessage', 'selectpayment', document.adminForm )" value="<?php echo JText::_('Select Payment Method'); ?>" />
            <a href="<?php echo JRoute::_('index.php?option=com_tienda&view=carts'); ?>"><?php echo JText::_('Return to Shopping Cart'); ?></a>
            	
    		<input type="hidden" id="currency_id" name="currency_id" value="<?php echo $this->order->currency_id; ?>" />
    		<input type="hidden" id="step" name="step" value="selectshipping" />
    		<input type="hidden" id="task" name="task" value="" />
    		
    		<?php  if (!$register) {?>
    		<input type="hidden" id="guest" name="guest" value="1" />
    		<?php } else {?>
    		<input type="hidden" id="register" name="register" value="1" />
    		<?php } ?>
        
        <?php echo $this->form['validate']; ?>
    </form>
</div>
