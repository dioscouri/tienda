<?php defined('_JEXEC') or die('Restricted access');?>
<div class="table">
	<div class="row">
		<div class="cell step_body inactive">
			<?php echo $this->step1_inactive;?>

			<div class="go_back">
				<a href="index.php?option=com_tienda&view=pos">
				<?php echo JText::_("Go Back");?>
				</a>
			</div>
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_("POS_STEP1_SELECT_USER");?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
			<div class="go_back">
				<a href="index.php?option=com_tienda&view=pos&nextstep=step2">
				<?php echo JText::_("Go Back");?>
				</a>
			</div>
			<div id="orderSummary">
				<?php echo $this->orderSummary;?>				
			</div>
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_("POS_STEP2_SELECT_PRODUCTS");?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body active">			
			            <div id="validation_message"></div>		
			  <?php $coupons_enabled = TiendaConfig::getInstance()->get('coupons_enabled');?>
        <?php if ($coupons_enabled && !empty($this->coupons_present)) : ?>
        <!-- COUPON CODE -->
        <div id="coupon_code_area">
            <div id="coupon_code_form">
            <h3><?php echo JText::_("Coupon Code");?></h3>
            <?php $mult_enabled = TiendaConfig::getInstance()->get('multiple_usercoupons_enabled');?>
            <?php $string = "Coupon Code Help";
				if($mult_enabled)
				{
					$string = "Coupon Code Help Multiple";
				}
 ?>
            <div id="coupon_code_help"><?php echo JText::_($string);?></div>
            <div id="coupon_code_message"></div>
            <input type="text" name="new_coupon_code" id="new_coupon_code" value="" />
            <input type="button" name="coupon_submit" value="<?php echo JText::_('Add Coupon to Order');?>"  onClick="tiendaAddCoupon( document.adminForm, '<?php
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
        
			<div id="addresses">
				<h3>
            		<?php echo JText::_("Select Shipping and Billing Addresses") ?>
        		</h3>
        		 <div class='note'>
	                <?php $text = JText::_( "Click Here to Manage User's Stored Addresses" )."."; ?>
	                <?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=pos&task=addresses&tmpl=component", $text, array('update' => true) );  ?>
                    <?php echo JText::_( "This opens a window where you may modify user's existing stored addresses or create a new one. When you're finished, close the window to continue your checkout process. " ); ?>
                </div>
				<div class="reset"></div>
				<div style="float: left;">
					<h4 id='billing_address_header' class="address_header">
					<?php echo JText::_("Billing Address") ?>
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
					<?php echo JText::_("Shipping Address") ?>
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
							<?php echo JText::_('SAME AS BILLING ADDRESS')?>
							<?php echo $this->shippingForm;?>
						<?php endif;?>
					<?php endif;?>
				</div>
				<div class="reset"></div>
				
				<?php if(!empty($this->shippingRates)):?>
				<div id="shippingRatedWrapper">
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
					<?php $onclick = "tiendaValidation( '" . $this->validation_url . "', 'validation_message', 'saveAddress', document.adminForm, true, '" . JText::_('Validating') . "' );";?> 
					<input onclick="<?php echo $onclick;?>" value="<?php echo JText::_('Continue');?>" type="button" class="button" />
				<?php else:?>
					<?php $subtask = $this->subtask == 'shipping' ? 'saveShipping' : 'display';?>
                	<?php $onclick = "tiendaValidation( '" . $this->validation_url . "', 'validation_message', '" . $subtask . "', document.adminForm, true, '" . JText::_('Validating') . "' );";?> 
                	<input onclick="<?php echo $onclick;?>" value="<?php echo JText::_('Continue');?>" type="button" class="button" />
				<?php endif;?>				
            </div>
		</div>
		<div class="cell step_title active">
			<h2>
			<?php echo JText::_("POS_STEP3_SELECT_PAYMENT_SHIPPING_METHODS");?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_("POS_STEP4_REVIEW_SUBMIT_ORDER");?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body inactive">
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_("POS_STEP5_PAYMENT_CONFIRMATION");?>
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