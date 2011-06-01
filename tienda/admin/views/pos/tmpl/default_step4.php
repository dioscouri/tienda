<?php defined('_JEXEC') or die('Restricted access');?>
<?php JHTML::_( 'behavior.modal' ); ?> 
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
		<div class="cell step_body inactive">			
		</div>
		<div class="cell step_title inactive">
			<h2>
			<?php echo JText::_("POS_STEP3_SELECT_PAYMENT_SHIPPING_METHODS");?>
			</h2>
		</div>
	</div>
	<div class="row">
		<div class="cell step_body active">
			<?php if (!empty($this->showBilling)) { ?>
			<div id="payment_info" class="address">
				<h3>
				<?php echo JText::_("Billing Information");?>
				</h3>
				<strong>
				<?php echo JText::_("Billing Address");?>
				</strong>:
				<br/>
				<?php
				echo $this->billing_info->first_name . " " . $this->billing_info->last_name . "<br/>";
				echo $this->billing_info->address_1 . ", ";
				echo $this->billing_info->address_2 ? $this->billing_info->address_2 . ", " : "";
				echo $this->billing_info->city . ", ";
				echo $this->billing_info->zone_name . " ";
				echo $this->billing_info->postal_code . " ";
				echo $this->billing_info->country_name;
				?>
			</div>
			<?php }?>

			<?php if (!empty($this->showShipping)) { ?>
			<div id="shipping_info" class="address">
				<h3>
				<?php echo JText::_("Shipping Information");?>
				</h3>
				<strong>
				<?php echo JText::_("Shipping Method");?>
				</strong>: <?php echo JText::_($this->shipping_method_name);?>
				<br/>
				<strong>
				<?php echo JText::_("Shipping Address");?>
				</strong>:
				<br/>
				<?php
				echo $this->shipping_info->first_name . " " . $this->shipping_info->last_name . "<br/>";
				echo $this->shipping_info->address_1 . ", ";
				echo $this->shipping_info->address_2 ? $this->shipping_info->address_2 . ", " : "";
				echo $this->shipping_info->city . ", ";
				echo $this->shipping_info->zone_name . " ";
				echo $this->shipping_info->postal_code . " ";
				echo $this->shipping_info->country_name;
				?>
			</div>
			<div class="reset">
			</div>
			
		
			<?php if(!empty($this->order->customer_note)):?>
			<div id="shipping_comments">
				<h3>
				<?php echo JText::_("Shipping Notes");?>
				</h3>
				<?php echo $this->order->customer_note;?>
			</div>
			<?php endif; ?>
			<?php }?>
		    <div class="reset"></div>
		    
		    
			<div class="continue">
				<?php 
				$link = JURI::root();
				$link .= 'index.php?option=com_tienda&amp;controller=checkout&amp;task=poscheckout';
				$link .= '&amp;orderid=' . $this->order->order_id;
				$link .= '&amp;userid='.$this->session->get('user_id', '', 'tienda_pos');			
				$link .= '&amp;data='.base64_encode(@json_encode($this->values));
				$link .= '&amp;tmpl=component';
				?>
				<a id="modalWindowPayment" rel="{handler:'iframe',size:{x: window.innerWidth-400, y: window.innerHeight-200}, onShow:$('sbox-window').setStyles({'padding': 0})}" href="<?php echo $link;?>" class="modal">
					<button><?php echo JText::_('Click Here to Complete Order');?></button>					
				</a>
			</div>
		</div>
		<div class="cell step_title active">
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
<input type="hidden" name="nextstep" id="nextstep" value="step5" />

<script type="text/javascript" >
	 window.addEvent('domready', function() {	 	
	 	window.addEvent('load', function(){
	 		 SqueezeBox.fromElement($('modalWindowPayment'));
	 	});	 
	 });
</script>