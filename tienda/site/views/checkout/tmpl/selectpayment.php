<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'tienda_checkout.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form; ?>
<?php $shipping_info = @$this->shipping_info; ?>
<?php $billing_info = @$this->billing_info; ?>
<?php $items = @$this->items ? @$this->items : array();?>
<?php $values = @$this->values; ?>

<div class='componentheading'>
    <span><?php echo JText::_( "Select Payment Method" ); ?></span>
</div>

    <?php // if ($menu =& TiendaMenu::getInstance()) { $menu->display(); } ?>
    
    <!-- Progress Bar -->
	<?php echo $this->progress; ?>

<form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

    <div id='onCheckoutReview_wrapper'>
        <!--    ORDER SUMMARY   -->
        <h3><?php echo JText::_("Order Summary") ?></h3>
        <div id='onCheckoutCart_wrapper'> 
            <?php
                echo @$this->orderSummary;
            ?>
        </div>
        
        <?php if (!empty($this->onBeforeDisplaySelectPayment)) : ?>
            <div id='onBeforeDisplaySelectPayment_wrapper'>
            <?php echo $this->onBeforeDisplaySelectPayment; ?>
            </div>
        <?php endif; ?>
        
        <?php $coupons_enabled = TiendaConfig::getInstance()->get('coupons_enabled'); ?>
        <?php if ($coupons_enabled) : ?>
        <!-- COUPON CODE -->
        <div id="coupon_code_area">
            <div id="coupon_code_form">
            <h3><?php echo JText::_("Coupon Code"); ?></h3>
            <?php $mult_enabled = TiendaConfig::getInstance()->get('multiple_usercoupons_enabled'); ?>
            <?php $string = "Coupon Code Help"; if ($mult_enabled) { $string = "Coupon Code Help Multiple"; } ?>
            <div id="coupon_code_help"><?php echo JText::_($string); ?></div>
            <div id="coupon_code_message"></div>
            <input type="text" name="new_coupon_code" id="new_coupon_code" value="" />
            <input type="button" name="coupon_submit" value="<?php echo JText::_('Add Coupon to Order'); ?>"  onClick="tiendaAddCoupon( document.adminForm, '<?php if ($mult_enabled) { echo "1"; } else { echo "0"; } ?>' );"/>
            </div>
            <div id='coupon_codes' style="display: none;"></div>
        </div>
        <?php endif; ?>
        
        <div class="reset"></div>
        
	   <div id="payment_info" class="address">
		<h3><?php echo JText::_("Billing Information"); ?></h3>
		<strong><?php echo JText::_("Total Amount Due"); ?></strong>: <?php echo TiendaHelperBase::currency( $this->order->order_total ); ?><br/>
        <strong><?php echo JText::_("Billing Address"); ?></strong>:<br/> 
                    <?php
                    echo $billing_info['first_name']." ". $billing_info['last_name']."<br/>";
                    echo $billing_info['address_1'].", ";
                    echo $billing_info['address_2'] ? $billing_info['address_2'] .", " : "";
                    echo $billing_info['city'] .", ";
                    echo $billing_info['zone_name'] ." ";
                    echo $billing_info['postal_code'] ." ";
                    echo $billing_info['country_name'];
                    ?>
            <br/>
	   </div>

        <div id="shipping_info" class="address">
        <h3><?php echo JText::_("Shipping Information"); ?></h3>
        <?php if (!empty($this->showShipping)) { ?>
        <strong><?php echo JText::_("Shipping Method"); ?></strong>: <?php echo JText::_( $this->shipping_method_name ); ?><br/>
        <strong><?php echo JText::_("Shipping Address"); ?></strong>:<br/> 
                    <?php
                    echo $shipping_info['first_name']." ". $shipping_info['last_name']."<br/>";
                    echo $shipping_info['address_1'].", ";
                    echo $shipping_info['address_2'] ? $shipping_info['address_2'] .", " : "";
                    echo $shipping_info['city'] .", ";
                    echo $shipping_info['zone_name'] ." ";
                    echo $shipping_info['postal_code'] ." ";
                    echo $shipping_info['country_name'];
                    ?>
        <?php } else { ?>
        <?php echo JText::_( "No Shipping Required" ); ?>
        <?php } ?>
        </div>
    
	    <div class="reset"></div>
	    <?php 
	    	if(!empty($this->customer_note)){
	    		?>
	   			<div id="shipping_comments">
	    		<h3><?php echo JText::_("Shipping Notes"); ?></h3><br/>
	 			<?php echo $this->customer_note; ?>
	    		</div>
	    	<?php } ?>
	 	<br/>
	 	
	 	 <?php 
	    	if( TiendaConfig::getInstance()->get('require_terms', '1') )
	    	{
	    		$terms_article = TiendaConfig::getInstance()->get('article_terms');
	    		$terms_link = JRoute::_('index.php?option=com_content&view=article&id='.$terms_article);
	    		?>
        	 	<div id="shipping_terms">
            		<h3><?php echo JText::_("Terms & Conditions"); ?></h3>
         			<input type="checkbox" name="shipping_terms" value="1" /> <a href="<?php echo $terms_link; ?>" target="_blank"><?php echo JText::_('Accept Terms & Conditions');?></a>
         			<br/>
         			<br/>
            	</div>
        <?php } ?>
        
        <?php if (!empty($this->showPayment)) { ?>
            <!--    PAYMENT METHODS   -->        
            <h3><?php echo JText::_("Payment Method") ?></h3>
            <p><?php echo JText::_("Please select your preferred payment method below"); ?>:</p>
            <div id='onCheckoutPayment_wrapper'>
                <?php
                    if ($this->plugins) 
                    {                  
                        foreach ($this->plugins as $plugin) 
                        {
                            ?>
                            <input value="<?php echo $plugin->element; ?>" onclick="tiendaGetPaymentForm('<?php echo $plugin->element; ?>', 'payment_form_div'); $('validationmessage').setHTML('');" name="payment_plugin" type="radio" />
                            <?php echo JText::_( $plugin->name ); ?>
                            <br/>
                            <?php
                        }
                    }
                ?>
                
                <div id='payment_form_div' style="padding-top: 10px;"></div>
                
                <div id="validationmessage" style="padding-top: 10px;"></div>
            </div>
        <?php } ?>
    </div>

        <?php if (!empty($this->onAfterDisplaySelectPayment)) : ?>
            <div id='onAfterDisplaySelectPayment_wrapper'>
            <?php echo $this->onAfterDisplaySelectPayment; ?>
            </div>
        <?php endif; ?>

    <p>
        <input type="button" class="button" onclick="window.location = '<?php echo JRoute::_('index.php?option=com_tienda&view=carts'); ?>'" value="<?php echo JText::_('Return to Shopping Cart'); ?>" />
        <input type="button" class="button" onclick="tiendaFormValidation( '<?php echo @$form['validation']; ?>', 'validationmessage', 'preparePayment', document.adminForm )" value="<?php echo JText::_('Click Here to Review Order Before Submitting Payment'); ?>" />
    </p>
        
    <input type="hidden" id="order_total" name="order_total" value="<?php echo $this->order->order_total; ?>" />
    <input type="hidden" id="currency_id" name="currency_id" value="<?php echo $this->order->currency_id; ?>" />
    <input type="hidden" id="shipping_address_id" name="shipping_address_id" value="<?php echo @$values['shipping_address_id']; ?>" />
    <input type="hidden" id="billing_address_id" name="billing_address_id" value="<?php echo @$values['billing_address_id']; ?>" />
    <input type="hidden" id="shipping_plugin" name="shipping_plugin" value="<?php echo @$values['shipping_plugin']; ?>" />
    <input type="hidden" name="shipping_price" id="shipping_price" value="<?php echo @$values['shipping_price']; ?>" />
	<input type="hidden" name="shipping_tax" id="shipping_tax" value="<?php echo @$values['shipping_tax']; ?>" />
	<input type="hidden" name="shipping_name" id="shipping_name" value="<?php echo @$values['shipping_name']; ?>" />
	<input type="hidden" name="shipping_extra" id="shipping_extra" value="<?php echo @$values['shipping_extra']; ?>" />
    <input type="hidden" id="customer_note" name="customer_note" value="<?php echo (!empty($values['customer_note'])) ? $values['customer_note'] : ''; ?>" />
	<input type="hidden" id="task" name="task" value="" />
	<input type="hidden" id="step" name="step" value="selectpayment" />
	<input type="hidden" id="guest" name="guest" value="<?php if($this->guest)echo "1"; else echo "0"; ?>" />
	<?php
	if($this->guest){
	?>
	<input type="hidden" id="email_address" name="email_address" value="<?php echo $values['email_address']; ?>" />
	<?php 
	}
	?>

    <?php echo JHTML::_( 'form.token' ); ?>
</form>
