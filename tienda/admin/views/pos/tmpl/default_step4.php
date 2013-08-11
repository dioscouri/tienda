<?php defined('_JEXEC') or die('Restricted access');?>
<?php JHTML::_( 'behavior.modal' ); ?> 


<?php $display_credits = Tienda::getInstance()->get( 'display_credits', '0' ); ?>

<ul class="nav nav-tabs" id="myTab">
  <li ><a href="index.php?option=com_tienda&view=pos"><?php echo JText::_('COM_TIENDA_POS_STEP1_SELECT_USER'); ?></a></li>
  <li ><a href="index.php?option=com_tienda&view=pos&nextstep=step2"><?php echo JText::_('COM_TIENDA_POS_STEP2_SELECT_PRODUCTS'); ?></a></li>
  <li class=""><a href=""><?php echo JText::_('COM_TIENDA_POS_STEP3_SELECT_PAYMENT_SHIPPING_METHODS'); ?></a></li>
  <li  class="active"><a href=""><?php echo JText::_('COM_TIENDA_POS_STEP4_REVIEW_SUBMIT_ORDER'); ?></a></li>
    <li  class="disabled"><a href=""><?php echo JText::_('COM_TIENDA_POS_STEP5_PAYMENT_CONFIRMATION'); ?></a></li>
</ul>
<div class="progress">
  <div class="bar" style="width: 75%;"></div>
</div>

  <div id="validation_message"></div>
<div class="accordion" id="accordion2">
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
       <?php echo JText::_('COM_TIENDA_POS_STEP1_SELECT_USER'); ?>
      </a>
    </div>
    <div id="collapseOne" class="accordion-body collapse">
      <div class="accordion-inner">
       <?php  echo $this->step1_inactive; ?>
      </div>
    </div>
  </div>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
       <?php echo JText::_('COM_TIENDA_POS_STEP2_SELECT_PRODUCTS'); ?>
      </a>
    </div>
    <div id="collapseTwo" class="accordion-body collapse">
      <div class="accordion-inner">
      <div id="orderSummary">
				<?php echo $this->orderSummary;?>				
			</div>
            
      </div>
    </div>
  </div>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseThree">
        <?php echo JText::_('COM_TIENDA_POS_STEP3_SELECT_PAYMENT_SHIPPING_METHODS'); ?>
      </a>
    </div>
    <div id="collapseThree" class="accordion-body collapse">
      <div class="accordion-inner">
       
		
            
      </div>
    </div>
  </div>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseFour">
        <?php echo JText::_('COM_TIENDA_POS_STEP4_REVIEW_SUBMIT_ORDER'); ?>
      </a>
    </div>
    <div id="collapseFour" class="accordion-body collapse in">
      <div class="accordion-inner">
       	<?php if (!empty($this->showBilling)) { ?>
			<div id="payment_info" class="address">
				<h3>
				<?php echo JText::_('COM_TIENDA_BILLING_INFO');?>
				</h3>
				<strong>
				<?php echo JText::_('COM_TIENDA_BILLING_ADDRESS');?>
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
				<?php echo JText::_('COM_TIENDA_SHIPPING_INFO');?>
				</h3>
				<strong>
				<?php echo JText::_('COM_TIENDA_SHIPPING_METHOD');?>
				</strong>: <?php echo JText::_($this->shipping_method_name);?>
				<br/>
				<strong>
				<?php echo JText::_('COM_TIENDA_SHIPPING_ADDRESS');?>
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
				<?php echo JText::_('COM_TIENDA_SHIPPING_NOTES');?>
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
				$link .= '&amp;posid='.$this->values['pos_id'];
				$link .= '&amp;token='.$this->values['pos_token'];
				$link .= '&amp;tmpl=component';
				?>
				<a id="modalWindowPayment" rel="{handler:'iframe',size:{x: window.innerWidth-400, y: window.innerHeight-200}, onShow:$('sbox-window').setStyles({'padding': 0})}" href="<?php echo $link;?>" class="modal">
					<button class="btn btn-primary" style="margin-bottom: 15px;"><?php echo JText::_('COM_TIENDA_CLICK_TO_COMPLETE_ORDER');?></button>					
				</a>
			</div>
		
            
      </div>
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