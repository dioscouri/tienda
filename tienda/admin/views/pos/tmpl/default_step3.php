<?php defined('_JEXEC') or die('Restricted access');?>
<?php $display_credits = TiendaConfig::getInstance()->get( 'display_credits', '0' ); ?>
<div class="table">
	<div class="row">
		<div class="cell step_body inactive">
			<?php echo $this->step1_inactive;?>

			<div class="go_back">
				<a href="index.php?option=com_tienda&view=pos">
				<?php echo JText::_('COM_TIENDA_GO_BACK');?>
				</a>
			</div>
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_('COM_TIENDA_POS_STEP1_SELECT_USER');?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
			<div class="go_back">
				<a href="index.php?option=com_tienda&view=pos&nextstep=step2">
				<?php echo JText::_('COM_TIENDA_GO_BACK');?>
				</a>
			</div>
			<div id="orderSummary">
				<?php echo $this->orderSummary;?>				
			</div>
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_('COM_TIENDA_POS_STEP2_SELECT_PRODUCTS');?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body active">			
			            <div id="validation_message"></div>		
			  <?php $coupons_enabled = TiendaConfig::getInstance()->get('coupons_enabled');?>
        <?php if ($coupons_enabled && !empty($this->coupons_present)) : ?>
        <!-- COUPON CODE -->
        <div id="coupon_code_area" class="address">
            <div id="coupon_code_form">
            <h3><?php echo JText::_('COM_TIENDA_COUPON_CODE');?></h3>
            <?php $mult_enabled = TiendaConfig::getInstance()->get('multiple_usercoupons_enabled');?>
            <?php $string = "COM_TIENDA_COUPON_CODE_HELP";
				if($mult_enabled)
				{
					$string = "COM_TIENDA_COUPON_CODE_HELP_MULTIPLE";
				}
 ?>
            <div id="coupon_code_help"><?php echo JText::_($string);?></div>
            <div id="coupon_code_message"></div>
            <input type="text" name="new_coupon_code" id="new_coupon_code" value="" />
            <input type="button" name="coupon_submit" value="<?php echo JText::_('COM_TIENDA_ADD_COUPON_TO_ORDER');?>"  onClick="tiendaAddCoupon( document.adminForm, '<?php
			if($mult_enabled)
			{
				echo "1";
			}
			else
			{
				echo "0";
			}
 ?>' );"/>
            </div>
            <div id='coupon_codes' style="display: none;"></div>
        </div>
        <?php endif;?>
        
        <div class="reset"></div>
        <?php if( $display_credits && isset($this->userinfo)):?>        
        <?php if ($this->userinfo->credits_total > '0.00') : ?>
            	<!-- STORE CREDITS -->
		<div id="credits_area" class="address">
			<div id="credits_form">
		        <h3><?php echo JText::_('COM_TIENDA_STORE_CREDIT'); ?></h3>
		        <div id="credit_help"><?php echo sprintf( JText::_('COM_TIENDA_YOU_HAVE_STORE_CREDIT'), TiendaHelperBase::currency( $this->userinfo->credits_total ) ); ?></div>
		       	<div id="credit_message"></div>
		        <input type="text" name="apply_credit_amount" id="apply_credit_amount" value="" />
		    	<input type="button" name="credit_submit" value="<?php echo JText::_('COM_TIENDA_APPLY_CREDIT_TO_ORDER'); ?>"  onClick="tiendaAddCredit( document.adminForm );"/>
			</div>
		</div>
		<?php endif; ?>
		<div id='applied_credit' style="display: none;"></div>	
        <div class="reset"></div>
        <?php endif;?>
        
        
			<div id="addresses">
				<h3>
            		<?php echo JText::_('COM_TIENDA_SELECT_SHIPPING_AND_BILLING_ADDRESS') ?>
        		</h3>
        		 <div class='note'>
	                <?php $text = JText::_('COM_TIENDA_MANAGE_USERS_STORED_ADDRESSES')."."; ?>
	                <?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=pos&task=addresses&tmpl=component", $text, array('update' => true) );  ?>
                    <?php echo JText::_('COM_TIENDA_USERS_STORED_ADDRESSES_NOTE'); ?>
                </div>
				<div class="reset"></div>
				<div style="float: left;">
					<h4 id='billing_address_header' class="address_header">
					<?php echo JText::_('COM_TIENDA_BILLING_ADDRESS') ?>
					</h4>
					<?php if (!empty($this->billingAddress)): ?>
					<p>
						<?php
						echo $this->billingAddress->title . " " . $this->billingAddress->first_name . " " . $this->billingAddress->last_name . "<br>";
						echo $this->billingAddress->company . "<br>";
						echo $this->billingAddress->address_1 . " " . $this->billingAddress->address_2 . "<br>";
						echo $this->billingAddress->city . ", " . $this->billingAddress->zone_name . " " . $this->billingAddress->postal_code . "<br>";
						echo $this->billingAddress->country_name . "<br>";
						?>
					</p>
					<input type="hidden" id="billing_input_address_id" name="billing_input_address_id" value="<?php echo $this->billingAddress->address_id;?>" />
					<?php else:?>
					<?php echo $this->billingForm;?>
					<?php endif;?>
				</div>
				<?php if($this->showShipping):?>
				<div style="float: left; margin-left: 30px;">
					<h4 id='shipping_address_header' class="address_header">
					<?php echo JText::_('COM_TIENDA_SHIPPING_ADDRESS') ?>
					</h4>
					<?php if(!empty($this->shippingAddress)):?>

					<p>
						<?php
						echo $this->shippingAddress->title . " " . $this->shippingAddress->first_name . " " . $this->shippingAddress->last_name . "<br>";
						echo $this->shippingAddress->company . "<br>";
						echo $this->shippingAddress->address_1 . " " . $this->shippingAddress->address_2 . "<br>";
						echo $this->shippingAddress->city . ", " . $this->shippingAddress->zone_name . " " . $this->shippingAddress->postal_code . "<br>";
						echo $this->shippingAddress->country_name . "<br>";
						?>
					</p>
					<input type="hidden" id="shipping_input_address_id" name="shipping_input_address_id" value="<?php echo $this->shippingAddress->address_id;?>" />
					<?php else:?>
						<?php if($this->showShipping):?>
							<input type="checkbox" name="sameasbilling" id="sameasbilling">
							<?php echo JText::_('COM_TIENDA_SAME_AS_BILLING_ADDRESS')?>
							<?php echo $this->shippingForm;?>
						<?php endif;?>
					<?php endif;?>
				</div>
				<div class="reset"></div>
				
				<?php if(!empty($this->shippingRates)):?>
				<div id="onCheckoutShipping_wrapper">
					<?php echo $this->shippingRates;?>
				</div>
				<?php endif;?>
				<?php endif;?>
			</div>
			<div class="reset"></div>
			<?php if(!empty($this->paymentOptions)):?>
				<div id="paymentOptions">
					<?php echo $this->paymentOptions;?>
				</div>
				<?php endif;?>
			<div class="continue">
				<?php if (empty($this->billingAddress)): ?>
					<?php $onclick = "tiendaValidation( '" . $this->validation_url . "', 'validation_message', 'saveAddress', document.adminForm, true, '" . JText::_('COM_TIENDA_VALIDATING') . "' );";?> 
					<input onclick="<?php echo $onclick;?>" value="<?php echo JText::_('COM_TIENDA_CONTINUE');?>" type="button" class="button" />
				<?php else:?>
					<?php $subtask = $this->subtask == 'shipping' ? 'saveShipping' : 'display';?>
                	<?php $onclick = "tiendaValidation( '" . $this->validation_url . "', 'validation_message', '" . $subtask . "', document.adminForm, true, '" . JText::_('COM_TIENDA_VALIDATING') . "' );";?> 
                	<input onclick="<?php echo $onclick;?>" value="<?php echo JText::_('COM_TIENDA_CONTINUE');?>" type="button" class="button" />
				<?php endif;?>				
            </div>
		</div>
		<div class="cell step_title active">
			<h2>
			<?php echo JText::_('COM_TIENDA_POS_STEP3_SELECT_PAYMENT_SHIPPING_METHODS');?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_('COM_TIENDA_POS_STEP4_REVIEW_SUBMIT_ORDER');?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_('COM_TIENDA_POS_STEP5_PAYMENT_CONFIRMATION');?>
			</h2>
		</div>
	</div>
</div>

<input type="hidden" id="order_total" name="order_total" value="<?php echo $this->order->order_total;?>" />
<input type="hidden" id="currency_id" name="currency_id" value="<?php echo $this->order->currency_id;?>" />
<?php if($this->subtask != 'shipping'):?>
<input type="hidden" id="shipping_plugin" name="shipping_plugin" value="<?php echo $this->session->get('shipping_plugin', '', 'tienda_pos');?>" />
<input type="hidden" name="shipping_price" id="shipping_price" value="<?php echo $this->session->get('shipping_price', '', 'tienda_pos');?>" />
<input type="hidden" name="shipping_tax" id="shipping_tax" value="<?php echo $this->session->get('shipping_price', '', 'tienda_pos');?>" />
<input type="hidden" name="shipping_name" id="shipping_name" value="<?php echo $this->session->get('shipping_name', '', 'tienda_pos');?>" />
<input type="hidden" name="shipping_code" id="shipping_code" value="<?php echo $this->session->get('shipping_code', '', 'tienda_pos');?>" />
<input type="hidden" name="shipping_extra" id="shipping_extra" value="<?php echo $this->session->get('shipping_extra', '', 'tienda_pos');?>" />
<input type="hidden" id="customer_note" name="customer_note" value="<?php echo $this->session->get('customer_note', '', 'tienda_pos');?>" />
<?php endif;?>

<input type="hidden" name="nextstep" id="nextstep" value="step4" />
<input type="hidden" id="shippingrequired" name="shippingrequired" value="<?php echo $this->showShipping ? 1 : 0;?>" />