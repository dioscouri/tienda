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
        <?php if ($coupons_enabled && $this->coupons_present) : ?>
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
					
					<?php else:?>
						<input type="checkbox" onclick="tiendaSameBillingAddress(this,this.form);" name="sameasbilling" id="sameasbilling">
						<?php echo JText::_('SAME AS BILLING ADDRESS')?>
					<?php echo $this->shippingForm;?>
					<?php endif;?>
				</div>
				<div class="reset"></div>
				
				<?php if(!empty($this->shippingRates)):?>
				<div id="shippingRatedWrapper">
					<?php echo $this->shippingRates;?>
				</div>
				<?php endif;?>
				
				<?php if(!empty($this->paymentOptions)):?>
				<div id="paymentOptions">
					<?php echo $this->paymentOptions;?>
				</div>
				<?php endif;?>
				
				<?php endif;?>
			</div>
			<div class="continue">
				<?php $subtask = $this->subtask == 'shipping' ? 'saveShipping' : 'saveStep3';?>
                <?php $onclick = "tiendaValidation( '" . $this->validation_url . "', 'validationmessage', '" . $subtask . "', document.adminForm, true, '" . JText::_('Validating') . "' );";?> 
                <input onclick="<?php echo $onclick;?>" value="<?php echo JText::_('Continue');?>" type="button" class="button" />
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
<input type="hidden" name="nextstep" id="nextstep" value="step4" />
<input type="hidden" id="shippingrequired" name="shippingrequired" value="<?php echo $this->showShipping ? 1 : 0;?>" />