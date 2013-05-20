<?php 
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	JHTML::_('script', 'tienda_checkout.js', 'media/com_tienda/js/');
	$form = @$this->form;
	$shipping_info = @$this->shipping_info;
	$billing_info = @$this->billing_info;
	$items = @$this->items ? @$this->items : array();
	$values = @$this->values;
	$display_credits = Tienda::getInstance()->get( 'display_credits', '0' ); 
	Tienda::load( 'TiendaHelperBase', 'helpers._base' );
	$js_strings = array( 'COM_TIENDA_UPDATING_CART', 'COM_TIENDA_CHECKING_COUPON', 'COM_TIENDA_UPDATING_BILLING' );	
	TiendaHelperBase::addJsTranslationStrings( $js_strings );
?>

<div class='componentheading'>
    <span><?php echo JText::_('COM_TIENDA_SELECT_PAYMENT_METHOD'); ?></span>
</div>
    
    <!-- Progress Bar -->
	<?php echo $this->progress; ?>

<form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

    <div id='onCheckoutReview_wrapper'>
        <!--    ORDER SUMMARY   -->
        <h3><?php echo JText::_('COM_TIENDA_ORDER_SUMMARY') ?></h3>
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
        
        <?php $coupons_enabled = Tienda::getInstance()->get('coupons_enabled'); ?>
        <?php if ($coupons_enabled && $this->coupons_present) : ?>
        <!-- COUPON CODE -->
        <div id="coupon_code_area">
            <div id="coupon_code_form">
            <h3><?php echo JText::_('COM_TIENDA_COUPON_CODE'); ?></h3>
            <?php $mult_enabled = Tienda::getInstance()->get('multiple_usercoupons_enabled'); ?>
            <?php $string = "COM_TIENDA_COUPON_CODE_HELP"; if ($mult_enabled) { $string = "COM_TIENDA_COUPON_CODE_HELP_MULTIPLE"; } ?>
            <div id="coupon_code_help"><?php echo JText::_($string); ?></div>
            <div id="coupon_code_message"></div>
            <input type="text" name="new_coupon_code" id="new_coupon_code" value="" />
            <input type="button" name="coupon_submit" value="<?php echo JText::_('COM_TIENDA_ADD_COUPON_TO_ORDER'); ?>"  onClick="tiendaAddCoupon( document.adminForm, '<?php if ($mult_enabled) { echo "1"; } else { echo "0"; } ?>' );"/>
            </div>
            <div id='coupon_codes' style="display: none;"></div>
        </div>
        <?php endif; ?>
        
        <div class="reset"></div>
        <?php if( $display_credits ): ?>
	        <?php if ($this->userinfo->credits_total > '0.00') : ?>
	            <!-- STORE CREDITS -->
	            <div id="credits_area" class="address">
	                <div id="credits_form">
	                <h3><?php echo JText::_('COM_TIENDA_STORE_CREDIT'); ?></h3>
	                <div id="credit_help"><?php echo sprintf( JText::_('COM_TIENDA_YOU_HAVE_STORE_CREDIT'), TiendaHelperBase::currency( $this->userinfo->credits_total, Tienda::getInstance()->get( 'default_currencyid', 1) ) ); ?></div>
	                <div id="credit_message"></div>
	                <input type="text" name="apply_credit_amount" id="apply_credit_amount" value="" />
	                <input type="button" name="credit_submit" value="<?php echo JText::_('COM_TIENDA_APPLY_CREDIT_TO_ORDER'); ?>"  onClick="tiendaAddCredit( document.adminForm );"/>
	                </div>
	            </div>
	        <?php endif; ?>
	        <div id='applied_credit' style="display: none;"></div>
	        <div class="reset"></div>
        <?php endif; ?>
        
	   <div id="payment_info" class="address">
		<h3><?php echo JText::_('COM_TIENDA_BILLING_INFORMATION'); ?></h3>
		<strong><?php echo JText::_('COM_TIENDA_TOTAL_AMOUNT_DUE'); ?></strong>:<span id='totalAmountDue'><?php echo TiendaHelperBase::currency( @$this->order->order_total ); ?></span><br/>
		<?php if (!empty($this->showBilling)) { ?>
        <strong><?php echo JText::_('COM_TIENDA_BILLING_ADDRESS'); ?></strong>:<br/> 
                    <?php
                    echo @$billing_info['first_name']." ". @$billing_info['last_name']."<br/>";
                    echo @$billing_info['address_1'].", ";
                    echo @$billing_info['address_2'] ? @$billing_info['address_2'] .", " : "";
                    echo @$billing_info['city'] .", ";
                    echo @$billing_info['zone_name'] ." ";
                    echo @$billing_info['postal_code'] ." ";
                    echo @$billing_info['country_name'];
                    ?>
            <br/>
        <?php } ?>
	   </div>

        <div id="shipping_info" class="address">
        <h3><?php echo JText::_('COM_TIENDA_SHIPPING_INFORMATION'); ?></h3>
        <?php if (!empty($this->showShipping)) { ?>
        <strong><?php echo JText::_('COM_TIENDA_SHIPPING_METHOD'); ?></strong>: <?php echo JText::_( $this->shipping_method_name ); ?><br/>
        <strong><?php echo JText::_('COM_TIENDA_SHIPPING_ADDRESS'); ?></strong>:<br/> 
                    <?php
                    echo @$shipping_info['first_name']." ". @$shipping_info['last_name']."<br/>";
                    echo @$shipping_info['address_1'].", ";
                    echo @$shipping_info['address_2'] ? @$shipping_info['address_2'] .", " : "";
                    echo @$shipping_info['city'] .", ";
                    echo @$shipping_info['zone_name'] ." ";
                    echo @$shipping_info['postal_code'] ." ";
                    echo @$shipping_info['country_name'];
                    ?>
        <?php } else { ?>
        <?php echo JText::_('COM_TIENDA_NO_SHIPPING_REQUIRED'); ?>
        <?php } ?>
        </div>
    
	    <div class="reset"></div>
	    <?php 
	    	if(!empty($this->customer_note)){
	    		?>
	   			<div id="shipping_comments">
	    		<h3><?php echo JText::_('COM_TIENDA_SHIPPING_NOTES'); ?></h3><br/>
	 			<?php echo $this->customer_note; ?>
	    		</div>
	    	<?php } ?>
	 	<br/>
	 	
	 	 <?php 
	    	if( Tienda::getInstance()->get('require_terms', '1') )
	    	{
	    		$terms_article = Tienda::getInstance()->get('article_terms');
	    		$terms_link = JRoute::_('index.php?option=com_content&view=article&id='.$terms_article);
	    		?>
        	 	<div id="shipping_terms">
            		<h3><?php echo JText::_('COM_TIENDA_TERMS_AND_CONDITIONS'); ?></h3>
         			<input type="checkbox" name="shipping_terms" value="1" /> <a href="<?php echo $terms_link; ?>" target="_blank"><?php echo JText::_('COM_TIENDA_ACCEPT_TERMS_AND_CONDITIONS');?></a>
         			<br/>
         			<br/>
            	</div>
        <?php } ?>
        
        <?php if (!empty($this->showPayment)) { ?>
            <!--    PAYMENT METHODS   -->        
            <h3><?php echo JText::_('COM_TIENDA_PAYMENT_METHOD') ?></h3>
            <?php echo $this->payment_options_html; ?>
        <?php } ?>
    </div>

        <?php if (!empty($this->onAfterDisplaySelectPayment)) : ?>
            <div id='onAfterDisplaySelectPayment_wrapper'>
            <?php echo $this->onAfterDisplaySelectPayment; ?>
            </div>
        <?php endif; ?>

    <p>
        <input type="button" class="btn" onclick="tiendaFormValidation( '<?php echo @$form['validation']; ?>', 'validationmessage', 'preparePayment', document.adminForm ); tiendaPutAjaxLoader( 'validationmessage', '<?php echo JText::_('COM_TIENDA_VALIDATING');?>' );" value="<?php echo JText::_('COM_TIENDA_CLICK_HERE_TO_REVIEW_ORDER_BEFORE_SUBMITTING_PAYMENT'); ?>" />
        <a href="<?php echo JRoute::_('index.php?option=com_tienda&view=carts'); ?>"><?php echo JText::_('COM_TIENDA_RETURN_TO_SHOPPING_CART'); ?></a>
    </p>
        
    <input type="hidden" id="order_total" name="order_total" value="<?php echo $this->order->order_total; ?>" />
    <input type="hidden" id="currency_id" name="currency_id" value="<?php echo $this->order->currency_id; ?>" />
    <input type="hidden" id="shipping_address_id" name="shipping_address_id" value="<?php echo @$values['shipping_address_id']; ?>" />
    <input type="hidden" id="billing_address_id" name="billing_address_id" value="<?php echo @$values['billing_address_id']; ?>" />
    <input type="hidden" id="shipping_plugin" name="shipping_plugin" value="<?php echo @$values['shipping_plugin']; ?>" />
    <input type="hidden" name="shipping_price" id="shipping_price" value="<?php echo @$values['shipping_price']; ?>" />
	<input type="hidden" name="shipping_tax" id="shipping_tax" value="<?php echo @$values['shipping_tax']; ?>" />
	<input type="hidden" name="shipping_name" id="shipping_name" value="<?php echo @$values['shipping_name']; ?>" />
	<input type="hidden" name="shipping_code" id="shipping_code" value="<?php echo @$values['shipping_code']; ?>" />
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
