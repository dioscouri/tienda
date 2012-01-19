<?php
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('stylesheet', 'tienda_checkout_onepage.css', 'media/com_tienda/css/');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	JHTML::_('script', 'tienda_checkout.js', 'media/com_tienda/js/');
	JHTML::_('script', 'tienda_checkout_onepage.js', 'media/com_tienda/js/');
	JHTML::_('behavior.mootools' );
	Tienda::load('TiendaHelperImage', 'helpers.image');
	$image = TiendaHelperImage::getLocalizedName("help_tooltip.png", Tienda::getPath('images'));
	$enable_tooltips = TiendaConfig::getInstance()->get('one_page_checkout_tooltips_enabled', 0);
	$display_credits = TiendaConfig::getInstance()->get( 'display_credits', '0' );
	$guest_enabled = TiendaConfig::getInstance()->get('guest_checkout_enabled', 0);
?>
<a name="tienda-method"></a> 

<div id="tienda_checkout_pane">
<a name="tiendaRegistration" id="tiendaRegistration"></a>

<?php // login link ?>
<?php if(!$this->user->id ) : ?>
	<div class="tienda_checkout_method">
		<?php
			$uri = JFactory::getURI( );
			$return_link = base64_encode( $uri->toString( ) );
			$asklink = "index.php?option=com_tienda&view=checkout&task=registrationLink&tmpl=component&return=" . $return_link;
				
				$asktxt = TiendaUrl::popup( "{$asklink}.&tmpl=component", JText::_( "COM_TIENDA_CLICK_HERE_TO_LOGIN" ),
						array(
							'width' => '490', 'height' => '320'
						) );
				$asktxt = "<a class=\"modal\" href='{$asklink}'>";
				$asktxt .= JText::_( "COM_TIENDA_CLICK_HERE_TO_LOGIN" );
				$asktxt .= "</a>";
		?>
		[<?php echo $asktxt; ?>]
	</div>
<?php endif; ?>

<form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

<div class="floatbox">

	<!-- CUSTOMER, BILLING & SHIPPING ADDRESS FORMS -->
	<div class="opc-customer-billship-address">
		<div class="inner col3">
			
			<div class="contentheading">
				1. <?php echo JText::_("COM_TIENDA_CUSTOMER_INFORMATION")?>
				<?php if( $enable_tooltips ): ?>
				<a class="img_tooltip" href="" > 
					<img src="<?php echo Tienda::getUrl('images').$image; ?>" alt='<?php echo JText::_("COM_TIENDA_HELP"); ?>' />
					<span>
						<?php echo JText::_("COM_TIENDA_ORDER_INFORMATION_WILL_BE_SENT_TO_YOUR_ACCOUNT_EMAIL_LISTED_BELOW"); ?>												
					</span>
				</a>
				<?php endif; ?>
			</div>
				
			<!-- ID-CUSTOMER PANE -->
			<div id="tienda_customer">
				<div class="tienda_checkout_method_user_email">
					<?php
						if($this->user->id)
							$email_address = $this->user->email;
						else
							$email_address = '';
					?>

					<?php echo JText::_("COM_TIENDA_EMAIL_ADDRESS");?>:<br/>
						<input type="text" id="email_address" class="inputbox" name="email_address" value="<?php echo $email_address; ?>" onblur="tiendaCheckoutCheckEmail( 'user_email_validation',document.adminForm, '<?php echo JText::_( 'VALIDATING' ); ?>' )"/> *
				</div>
				<div id="user_email_validation"></div>
			</div>
			<!-- ID-CUSTOMER PANE END -->
			
			<!-- BILLING-SHIPPING PANE -->
			<div class="tienda-expanded" id="billing-shipping-pane">
				
				<div class="contentheading">
					<?php echo $this->showShipping ? JText::_("COM_TIENDA_BILLING_AND_SHIPPING_INFORMATION") : JText::_("COM_TIENDA_BILLING_INFORMATION"); ?>
				</div>
				
				<div id="tienda_billing-shipping">
	        <div id="billingAddress">						
						<div>
							<?php echo JText::_("COM_TIENDA_BILLING_ADDRESS")?>
						</div>
        			<?php 
						$baseurl = "index.php?option=com_tienda&format=raw&controller=addresses&task=getAddress&address_id=";                   
	            		$billattribs = array(
	                		'class' => 'inputbox',    
	                    	'size' => '1',
	                    	'onchange' => "tiendaCheckoutSetBillingAddress('$baseurl'+this.options[this.selectedIndex].value, 'billingDefaultAddress', this.options[this.selectedIndex].value, this.form, '".JText::_( "COM_TIENDA_UPDATING_SHIPPING_RATES" )."', '".JText::_( "COM_TIENDA_UPDATING_CART" )."', '".JText::_( "COM_TIENDA_UPDATING_ADDRESS" )."' );"
	                	);
	                        
	                	// display select list of stored addresses
	                	echo TiendaSelect::address( $this->user->id, @$this->billing_address->address_id, 'billing_address_id', 1, $billattribs, 'billing_address_id', false, true );
	           		?>
						
						<div id="billingDefaultAddress">
							<?php 
								if ( !empty( $this->billing_address ) ):
									echo $this->billing_address->title . " ". $this->billing_address->first_name . " ". $this->billing_address->last_name . "<br>";
									echo $this->billing_address->company . "<br>";
									echo $this->billing_address->address_1 . " " . $this->billing_address->address_2 . "<br>";
									echo $this->billing_address->city . ", " . $this->billing_address->zone_name ." " . $this->billing_address->postal_code . "<br>";
									echo $this->billing_address->country_name . "<br>";
								endif;
							?>
						</div>
						
						<?php echo @$this->billing_address_form; ?>
					</div>
     			<div class="reset marginbot"></div>
					<?php if(!$this->user->id ) : ?>
					<div class="tienda_checkout_method">
						<input type="checkbox" id="create_account" name="create_account" <?php if( !$guest_enabled ) echo 'checked disabled'; ?> value="on" />
						<label for="field-create-account"><?php echo JText::_( "COM_TIENDA_CREATE_A_NEW_ACCOUNT" );?></label>
						<div id="tienda_user_additional_info" <?php if( $guest_enabled ) echo 'class="hidden"'; ?>>
                <?php echo $this->form_user_register;?>
             </div>
     			</div>
     			<?php endif; ?>
           		
     			<?php if($this->showShipping):?>				
     			<div class="reset marginbot"></div>
							<div>
								<?php echo JText::_("COM_TIENDA_SHIPPING_ADDRESS"); ?>
							</div>
          			<div class="reset marginbot"></div>
		            <div id="shippingAddress">
					<!--    SHIPPING ADDRESS  -->	         
	                <?php if (empty($this->shipping_address)) : ?>
	                    <div>
	                        <input id="sameasbilling" name="sameasbilling" type="checkbox" checked="checked" onclick="tiendaShowHideDiv( 'shipping_input_addressForm' );"/>&nbsp;
	                        <?php echo JText::_( "COM_TIENDA_SAME_AS_BILLING_ADDRESS" ); ?>
	                    </div>
					<?php endif; ?>
            		<?php
		                $shipattribs = array(
		                   'class' => 'inputbox',    
		                   'size' => '1',
		                   'onchange' => "tiendaCheckoutSetShippingAddress('$baseurl'+this.options[this.selectedIndex].value, 'shippingDefaultAddress', '".JText::_( "COM_TIENDA_UPDATING_SHIPPING_RATES" )."', '".JText::_( "COM_TIENDA_UPDATING_CART" )."', '".JText::_( "COM_TIENDA_UPDATING_ADDRESS" )."', this.form, this.options[this.selectedIndex].value ); "
		                );
		                
		                // display select list of stored addresses
		                echo TiendaSelect::address( JFactory::getUser()->id, @$this->shipping_address->address_id, 'shipping_address_id', 2, $shipattribs, 'shipping_address_id', false, true );
					?>
						<div id="shippingDefaultAddress">
							<?php 
								if ( !empty( $this->shipping_address ) )
								{
					        		echo $this->shipping_address->title . " ". $this->shipping_address->first_name . " ". $this->shipping_address->last_name . "<br>";
									echo $this->shipping_address->company . "<br>";
									echo $this->shipping_address->address_1 . " " . $this->shipping_address->address_2 . "<br>";
									echo $this->shipping_address->city . ", " . $this->shipping_address->zone_name ." " . $this->shipping_address->postal_code . "<br>";
									echo $this->shipping_address->country_name . "<br>";
								}
							?>
						 </div>
							  <?php echo @$this->shipping_address_form; ?>
					</div>
	           		<?php else :?>
			             <input type="hidden" id="shippingrequired" name="shippingrequired" value="0"  />
			        <?php endif;?>           
				</div>		
			</div> 
			<!-- BILLING-SHIPPING PANE END -->
		</div>
	</div>
	<!-- CUSTOMER, BILLING & SHIPPING ADDRESS FORMS -->
	
	<!-- RIGHT SIDE OF THE LAYOUT -->
	<div class="right-side">
	
		<!-- SHIPPING METHOD -->
		<div class="opc-method">	
			<div class="inner col3">	 
				<?php if($this->showShipping):?>	
				<div class="tienda-expanded" id="shippingcost-pane">
					<div class="contentheading">
						2. <?php echo JText::_("COM_TIENDA_SELECT_A_SHIPPING_METHOD")?>
					</div>
					<div id="onCheckoutShipping_wrapper">
						<?php echo $this->shipping_method_form;?>
					</div>		
				</div>  
				<?php endif;?>
			</div> 
		</div>
		<!-- SHIPPING METHOD END -->

        <?php if (!empty($this->onBeforeDisplaySelectPayment)) : ?>
            <div id='onBeforeDisplaySelectPayment_wrapper'>
            <?php echo $this->onBeforeDisplaySelectPayment; ?>
            </div>
        <?php endif; ?>			
		<!-- PAYMENT METHOD -->
		<div class="opc-method">
			<div class="inner col3">	
				<div class="tienda-expanded" id="paymentmethod-pane">
					<div class="contentheading">
						3. <?php echo JText::_("COM_TIENDA_SELECT_A_PAYMENT_METHOD")?>
								<?php if( $enable_tooltips ) : ?>
								<a class="img_tooltip" href="" > 
									<img src="<?php echo Tienda::getUrl('images').$image; ?>" alt='<?php echo JText::_("COM_TIENDA_HELP"); ?>' />
									<span class="img_tooltip_left">
										<?php echo JText::_("COM_TIENDA_PLEASE_SELECT_YOUR_PREFERRED_PAYMENT_METHOD_BELOW"); ?>												
									</span>
								</a>
								<?php endif; ?>
					</div>		
					<div id="onCheckoutPayment_wrapper">
						<?php if(!count($this->payment_plugins)):?>
								<div class="note">
										<?php echo JText::_( "COM_TIENDA_NO_PAYMENT_METHOD_ARE_AVAILABLE_FOR_YOUR_ADDRESS" ); ?>
								</div>
						<?php endif;?>
						<?php echo $this->payment_options_html;?>                   
					</div>		
				</div> 
			</div>
		</div>
		<!-- PAYMENT METHOD END -->
        <?php if (!empty($this->onAfterDisplaySelectPayment)) : ?>
            <div id='onAfterDisplaySelectPayment_wrapper'>
            <?php echo $this->onAfterDisplaySelectPayment; ?>
            </div>
        <?php endif; ?>			
		
		<!-- REVIEW & PLACE ORDER -->
		<div class="opc-review-place-order">
			<div class="inner col3">
				
				<!--    ORDER SUMMARY   -->
				<h3 class="contentheading">
					4. <?php echo JText::_("COM_TIENDA_REVIEW_PLACE_ORDER") ?>
				</h3>
				<div id='onCheckoutCart_wrapper'> 
					<?php echo @$this->orderSummary; 	?> 
				</div>
				<!--    ORDER SUMMARY END  -->
				
				<div class="reset marginbot"></div>
				
				<?php $coupons_enabled = TiendaConfig::getInstance()->get('coupons_enabled'); ?>
		 		<?php if ($coupons_enabled && $this->coupons_present) : ?>
					<div class="tienda-expanded" id="coupon-pane">						
						<div id="coupon_code_area">
		            	 	<div id="coupon_code_form">  
		            	 		<div class="contentheading">
									<?php echo JText::_("COM_TIENDA_COUPON_CODE")?>
									<?php $mult_enabled = TiendaConfig::getInstance()->get('multiple_usercoupons_enabled'); ?>
			            			<?php $string = "Coupon Code Help"; if ($mult_enabled) { $string = "Coupon Code Help Multiple"; } ?>
			            	<?php if( $enable_tooltips ) : ?>
			            			<a class="img_tooltip" href="" > 
										<img src="<?php echo Tienda::getUrl('images').$image; ?>" alt='<?php echo JText::_("COM_TIENDA_HELP"); ?>' />
										<span>
											<?php echo JText::_($string); ?>												
										</span>
									</a>
									<?php endif; ?>
								</div>    	           	 			
		            			<div id="coupon_code_message"></div>
		            			<input type="text" name="new_coupon_code" id="new_coupon_code" value="" />
		            			<input type="button" name="coupon_submit" value="<?php echo JText::_("COM_TIENDA_ADD_COUPON_TO_ORDER"); ?>"  onClick="tiendaAddCoupon( document.adminForm, '<?php if ($mult_enabled) { echo "1"; } else { echo "0"; } ?>' );"/>
		            		</div>
		            		<div id='coupon_codes' style="display: none;"></div>
		        		</div>	
					</div>  
				<?php endif;?>
								
				<?php if( $display_credits ): ?>
				<div class="reset marginbot"></div>
				<?php if ($this->userinfo->credits_total > '0.00') : ?>
            	<!-- STORE CREDITS -->
		            <div id="credits_area" class="address">
		                <div id="credits_form">
		                <h3><?php echo JText::_("COM_TIENDA_STORE_CREDIT"); ?></h3>
		                <div id="credit_help"><?php echo sprintf( JText::_( "COM_TIENDA_YOU_HAVE_X_STORE_CREDIT" ), TiendaHelperBase::currency( $this->userinfo->credits_total ) ); ?></div>
		                <div id="credit_message"></div>
		                <input type="text" name="apply_credit_amount" id="apply_credit_amount" value="" />
		                <input type="button" name="credit_submit" value="<?php echo JText::_("COM_TIENDA_APPLY_CREDIT_TO_ORDER"); ?>"  onClick="tiendaAddCredit( document.adminForm );"/>
		                </div>
		            </div>
		        <?php endif; ?>
		        <div id='applied_credit' style="display: none;"></div>				
				<?php endif; ?>				
				<div class="reset marginbot"></div>
							
				<div class="tienda-expanded" id="comments-pane">
				<div class="contentheading">
					<?php echo JText::_("COM_TIENDA_ORDER_COMMENTS")?>
					<?php if( $enable_tooltips ): ?>
					<a class="img_tooltip" href="" > 
						<img src="<?php echo Tienda::getUrl('images').$image; ?>" alt='<?php echo JText::_("COM_TIENDA_HELP"); ?>' />
						<span>
							<?php echo JText::_("COM_TIENDA_USE_THIS_AREA");?>												
						</span>
					</a>
					<?php endif; ?>
				</div>
			
				<div id="tienda_comments">	
					<textarea id="customer_note" name="customer_note" rows="5" cols="41"></textarea>		
				</div>		
				</div>  
				
				<div class="reset marginbot"></div>	
				<div class="tienda-expanded" id="shipping_terms-pane">
				 <?php 
		    		if( TiendaConfig::getInstance()->get('require_terms', '1') )
		    		{
		    			$terms_article = TiendaConfig::getInstance()->get('article_terms');
		    			$terms_link = JRoute::_('index.php?option=com_content&view=article&id='.$terms_article);
		    		?>
	            	<div><?php echo JText::_("COM_TIENDA_TERMS_CONDITIONS"); ?></div>
					<div id="shipping_terms" >
						<br/>
						<input type="checkbox" name="shipping_terms" value="1" /> <a href="<?php echo $terms_link; ?>" target="_blank"><?php echo JText::_("COM_TIENDA_ACCEPT_TERMS_CONDITIONS");?></a>
	         			<br/>
	            	</div>
					
	        	<?php } ?>
				</div>
				<div id="validationmessage" style="padding-top: 10px;"></div> 
				<div id="tienda_btns">
					<input type="button" class="button" onclick="tiendaSaveOnepageOrder('tienda_checkout_pane', 'validationmessage', this.form, '<?php echo JText::_( "COM_TIENDA_VALIDATING" ); ?>')" value="<?php echo JText::_("COM_TIENDA_CLICK_HERE_TO_CONTINUE"); ?>" />
					<div class="reset marginbot"></div>	
					<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=carts'); ?>"><?php echo JText::_("COM_TIENDA_RETURN_TO_SHOPPING_CART"); ?></a> 
				</div>
			</div>
		</div> 
		<!-- REVIEW & PLACE ORDER END -->
	
	</div>
	<!-- RIGHT SIDE OF THE LAYOUT -->

</div>
<!-- END FLOATBOX -->

</div>

<input type="hidden" id="currency_id" name="currency_id" value="<?php echo $this->order->currency_id; ?>" />
<input type="hidden" id="order_total" name="order_total" value="<?php echo $this->order->order_total; ?>" />
<input type="hidden" id="task" name="task" value="onepageSaveOrder" />
<?php echo JHTML::_( 'form.token' ); ?>

</form>
<div id="refreshpage" style="display: none; text-align: right;"><a href="<?php echo JRoute::_('index.php?option=com_tienda&view=checkout')?>"><?php echo JText::_("COM_TIENDA_BACK")?></a></div>

<script type="text/javascript">
window.addEvent('domready', function() {
<?php if( @$this->billing_address->address_id ): ?>
	tiendaShowHideDiv( 'billing_input_addressForm' );
<?php endif; ?>

<?php if( $this->showShipping  ):?>	
	tiendaShowHideDiv( 'shipping_input_addressForm' );
	<?php if( !@$this->shipping_address->address_id ): ?>
		$( 'sameasbilling' ).addEvent( 'change', function() { copyBillingAdToShippingAd( document.getElementById( 'sameasbilling' ), document.adminForm, '<?php echo JText::_( "COM_TIENDA_UPDATING_SHIPPING_RATES" )?>', '<?php echo JText::_( "COM_TIENDA_UPDATING_CART" )?>', '<?php echo JText::_( "COM_TIENDA_UPDATING_ADDRESS" )?>', '<?php echo JText::_( "COM_TIENDA_UPDATING_PAYMENT_METHODS" )?>' ) } );
	<?php endif; ?>
<?php endif; ?>

<?php if( !$this->user->id ) : ?>
	tiendaHideInfoCreateAccount();
<?php endif; ?>
});
</script>