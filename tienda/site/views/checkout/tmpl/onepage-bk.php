<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda_checkout_onepage.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'tienda_checkout.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('script', 'tienda_checkout_onepage.js', 'media/com_tienda/js/'); ?>
<a name="tienda-method"></a> 
 <!--    ORDER SUMMARY   -->
<h3><?php echo JText::_("Order Summary") ?></h3>
<div id='onCheckoutCart_wrapper'> 
	<?php echo @$this->orderSummary; 	?> 
</div>
<div id="tienda_checkout_pane">
	<a name="tiendaRegistration" id="tiendaRegistration"></a>
	<?php if(!$this->user->id):?>
	<fieldset class="tienda-expanded" id="checkoutmethod-pane">
		<legend class="tienda-collapse-processed" id="tienda-method-pane"><?php echo JText::_('Checkout Method')?></legend>
		<div id="tienda_checkout_method">
			<?php echo @$this->checkoutMethod; ?>
		</div>
	</fieldset>  
	<?php endif;?>
<form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" name="adminForm" enctype="multipart/form-data">
	
	<?php if($this->user->id){?>
	<div class="reset marginbot"></div>	
	<fieldset class="tienda-expanded" id="customer-pane">
		<legend class="tienda-collapse-processed"><?php echo JText::_('Customer Information')?></legend>
		<div id="tienda_customer">
			<div class="note">
				<?php echo JText::_('Order information will be sent to your account e-mail listed below.')?>	
			</div>
			<?php echo JText::_('E-mail address');?>: <?php echo $this->user->email;?> ( <?php echo TiendaUrl::popup( "index.php?option=com_user&view=user&task=edit&tmpl=component", JText::_('edit'), array('update' => true) );  ?>)
		</div>		
	</fieldset>  
	<?php }else{?>
	<div id="onShowCustomerInfo"></div>	
	<?php }?>
	<input type="hidden" id="tiendaGuest" name="guest" value="0">
	<div class="reset marginbot"></div>
	
	<fieldset class="tienda-expanded" id="billing-shipping-pane">
		<legend class="tienda-collapse-processed"><?php echo $this->showShipping ? JText::_('Billing and Shipping Information') : JText::_('Billing Information'); ?></legend>
		<div id="tienda_billing-shipping">
        	<?php 
					 	$baseurl = "index.php?option=com_tienda&format=raw&controller=addresses&task=getAddress&address_id=";                   
          	if (!empty($this->billing_address))
            {
            	$billattribs = array(
                	'class' => 'inputbox',    
                    'size' => '1',
                    'onchange' => "tiendaDoTask('$baseurl'+this.options[this.selectedIndex].value, 'billingDefaultAddress', ''); tiendaGetCheckoutTotals();"
                );
                        
                // display select list of stored addresses
                echo TiendaSelect::address( $this->user->id, @$this->billing_address->address_id, 'billing_address_id', 1, $billattribs, 'billing_address_id', false );
                        
                if (count($this->billing_address) == 1)
                {
                	echo "<input type=\"hidden\" id=\"billing_address_id\" name=\"billing_address_id\" value=\"" . @$this->billing_address->address_id . "\" />";
                }
            }
           	?>
           	<!--    BILLING ADDRESS FORM  -->          
           	<?php if (empty($this->billing_address)): ?>
           	<span id="billingDefaultAddress">
            	<?php echo @$this->billing_address_form; ?>
                </span>	
			<?php else : ?>
            <fieldset>
				<legend><?php echo JText::_('Billing Address')?></legend>
				<span id="billingDefaultAddress">
				<?php 
			        echo $this->billing_address->title . " ". $this->billing_address->first_name . " ". $this->billing_address->last_name . "<br>";
					echo $this->billing_address->company . "<br>";
					echo $this->billing_address->address_1 . " " . $this->billing_address->address_2 . "<br>";
					echo $this->billing_address->city . ", " . $this->billing_address->zone_name ." " . $this->billing_address->postal_code . "<br>";
					echo $this->billing_address->country_name . "<br>";
				?>
				</span>
			</fieldset>
        	<?php endif; ?>
          
           
           <?php if($this->showShipping):?>
           <div class="reset marginbot"></div>
            <?php
                if (!empty($this->shipping_address))
                {
                    $shipping_rates_text = JText::_( "Getting Shipping Rates" ); 
	                $shipattribs = array(
	                   'class' => 'inputbox',    
	                   'size' => '1',
	                   'onchange' => "tiendaDoTask('$baseurl'+this.options[this.selectedIndex].value, 'shippingDefaultAddress', '', '', false); tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form, '$shipping_rates_text' ); "
	                ); // tiendaGetCheckoutTotals();
	                
	                // display select list of stored addresses
	                echo TiendaSelect::address( JFactory::getUser()->id, @$this->shipping_address->address_id, 'shipping_address_id', 2, $shipattribs, 'shipping_address_id', false );
	                
	               	if (count($this->shipping_address) == 1)
	               	{
	               		echo "<input type=\"hidden\" id=\"shipping_address_id\" name=\"shipping_address_id\" value=\"" . @$this->shipping_address->address_id . "\" />";
	               	}
				}
				?>

                <?php if (empty($this->shipping_address)) : ?>
                    <div>
                        <input id="sameasbilling" name="sameasbilling" type="checkbox" onclick="copyBillingAdToShippingAd(this,this.form); " />&nbsp;
                        <?php echo JText::_( 'Same As Billing Address' ); ?>:
                    </div>
				<?php endif; ?>
				
				<!--    SHIPPING ADDRESS FORM  -->	         
	            <?php if (empty($this->shipping_address)) : ?>
	            <span id="shippingDefaultAddress">
	            	<?php echo @$this->shipping_address_form; ?>
	            </span>
	            <?php else : ?>
	            <fieldset>
					<legend><?php echo JText::_('Shipping Address')?></legend>
					<span id="shippingDefaultAddress">
					<?php 
				        echo $this->shipping_address->title . " ". $this->shipping_address->first_name . " ". $this->shipping_address->last_name . "<br>";
						echo $this->shipping_address->company . "<br>";
						echo $this->shipping_address->address_1 . " " . $this->shipping_address->address_2 . "<br>";
						echo $this->shipping_address->city . ", " . $this->shipping_address->zone_name ." " . $this->shipping_address->postal_code . "<br>";
						echo $this->shipping_address->country_name . "<br>";
					?>
					 </span>
				</fieldset>
	            <?php endif; ?>
           <?php else :?>
           <input type="hidden" id="shippingrequired" name="shippingrequired" value="0"  />
           <?php endif;?>
           
		</div>		
	</fieldset> 
	
	<?php if($this->showShipping):?>
	<div class="reset marginbot"></div>
	
	<fieldset class="tienda-expanded" id="shippingcost-pane">
		<legend class="tienda-collapse-processed"><?php echo JText::_('Select a Shipping Method')?></legend>
		<div id="onCheckoutShipping_wrapper">
		<?php echo $this->shipping_method_form;?>
		</div>		
	</fieldset>  
	<?php endif;?>
	
	<div class="reset marginbot"></div>
	<?php $coupons_enabled = TiendaConfig::getInstance()->get('coupons_enabled'); ?>
	 <?php if ($coupons_enabled && $this->coupons_present) : ?>
	<fieldset class="tienda-expanded" id="coupon-pane">
		<legend class="tienda-collapse-processed"><?php echo JText::_('Coupon Code')?></legend>
		 <div id="coupon_code_area">
            <div id="coupon_code_form">          
            <?php $mult_enabled = TiendaConfig::getInstance()->get('multiple_usercoupons_enabled'); ?>
            <?php $string = "Coupon Code Help"; if ($mult_enabled) { $string = "Coupon Code Help Multiple"; } ?>
            <div id="coupon_code_help" class="note"><?php echo JText::_($string); ?></div>
            <div id="coupon_code_message"></div>
            <input type="text" name="new_coupon_code" id="new_coupon_code" value="" />
            <input type="button" name="coupon_submit" value="<?php echo JText::_('Add Coupon to Order'); ?>"  onClick="tiendaAddCoupon( document.adminForm, '<?php if ($mult_enabled) { echo "1"; } else { echo "0"; } ?>' );"/>
            </div>
            <div id='coupon_codes' style="display: none;"></div>
        </div>	
	</fieldset>  
	<?php endif;?>
	
	<div class="reset marginbot"></div>
		
	<fieldset class="tienda-expanded" id="paymentmethod-pane">
		<legend class="tienda-collapse-processed"><?php echo JText::_('Select a Payment Method')?></legend>		
		<div id="onCheckoutPayment_wrapper">
		<?php echo $this->payment_options_html;?>                   
		</div>		
	</fieldset>  
 	
	<div class="reset marginbot"></div>	
	<fieldset class="tienda-expanded" id="comments-pane">
		<legend class="tienda-collapse-processed"><?php echo JText::_('Order Comments')?></legend>
		<div class='note'>
	    	<?php echo JText::_('Use this area for special instructions or questions regarding your order.');?>
        </div>
		
		<div id="tienda_comments">	
		<textarea id="customer_note" name="customer_note" rows="5" cols="70"></textarea>		
		</div>		
	</fieldset>  
	
	<div class="reset marginbot"></div>	
	<fieldset class="tienda-expanded" id="shipping_terms-pane">
		 <?php 
	    	if( TiendaConfig::getInstance()->get('require_terms', '1') )
	    	{
	    		$terms_article = TiendaConfig::getInstance()->get('article_terms');
	    		$terms_link = JRoute::_('index.php?option=com_content&view=article&id='.$terms_article);
	    		?>
				
        	 	
            	<legend class="tienda-collapse-processed"><?php echo JText::_("Terms & Conditions"); ?></legend>
				<div id="shipping_terms" >
					<br/>
					<input type="checkbox" name="shipping_terms" value="1" /> <a href="<?php echo $terms_link; ?>" target="_blank"><?php echo JText::_('Accept Terms & Conditions');?></a>
         			<br/>
            	</div>
				
        <?php } ?>
		</fieldset>
		
	
</div>  

<div id="validationmessage" style="padding-top: 10px;"></div> 
<div id="tienda_btns">
<input type="button" class="button" onclick="tiendaSaveOnepageOrder('tienda_checkout_pane', 'validationmessage', this.form)" value="<?php echo JText::_('Click Here to Continue'); ?>" />
<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=carts'); ?>"><?php echo JText::_('Return to Shopping Cart'); ?></a> 
</div>
<input type="hidden" id="currency_id" name="currency_id" value="<?php echo $this->order->currency_id; ?>" />
<input type="hidden" id="order_total" name="order_total" value="<?php echo $this->order->order_total; ?>" />
<input type="hidden" id="task" name="task" value="onepageSaveOrder" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<div id="refreshpage" style="display: none; text-align: right;"><a href="<?php echo JRoute::_('index.php?option=com_tienda&view=checkout')?>"><?php echo JText::_('Back')?></a></div>


