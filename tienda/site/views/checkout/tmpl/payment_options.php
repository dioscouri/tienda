<?php defined('_JEXEC') or die('Restricted access'); ?>	
<?php $one_page = Tienda::getInstance()->get('one_page_checkout', 0); ?>

<?php if (empty($this->showPayment)) { ?>
    <p>
        <?php echo JText::_( "COM_TIENDA_NO_PAYMENT_NECESSARY" ); ?>
    </p>
    
    <div id="validationmessage" style="padding-top: 10px;"></div>
    
<?php } elseif( $one_page ) { ?>	
	<?php if(count($this->payment_plugins)):?>
		<?php foreach($this->payment_plugins as $payment_plugin):?>
		<input value="<?php echo $payment_plugin->element; ?>" onclick="tiendaGetPaymentForm('<?php echo $payment_plugin->element; ?>', 'payment_form_div', '<?php echo JText::_('COM_TIENDA_GETTING_PAYMENT_METHOD'); ?>'); $('validationmessage').set('html', ''); $('payment_form_div').addClass('note');" name="payment_plugin" type="radio" <?php echo (!empty($payment_plugin->checked)) ? "checked" : ""; ?> />
		<?php echo JText::_( $payment_plugin->name ); ?>
		<br />
		<?php endforeach;?>
		
		 <div id='payment_form_div' <?php if(!empty($this->payment_form_div)) echo 'class="note"';?> style="padding-top: 5px;">
		 <?php if(!empty($this->payment_form_div)):?>
		 	<?php echo $this->payment_form_div;?>
		 <?php endif;?>
		 </div>
	<?php endif;?>
<?php } else { ?>
	<div class="note">
		<?php echo count($this->payment_plugins) ? JText::_('COM_TIENDA_PLEASE_SELECT_YOUR_PREFERRED_PAYMENT_METHOD_BELOW').':' : JText::_('COM_TIENDA_NO_PAYMENT_METHOD_AVAILABLE_FOR_YOUR_ADDRESS');?>
	</div>
	
    <div id='onCheckoutPayment_wrapper'>
      <?php        
          if ($this->payment_plugins) 
          {                          	                  	
              foreach ($this->payment_plugins as $plugin) 
              {
                  ?>
                  <input id="paymeny_<?php echo $plugin->element?>" value="<?php echo $plugin->element; ?>" onclick="tiendaGetPaymentForm('<?php echo $plugin->element; ?>', 'payment_form_div', '<?php echo JText::_('COM_TIENDA_GETTING_PAYMENT_METHOD'); ?>'); $('validationmessage').set('html', '');" name="payment_plugin" type="radio" <?php echo (!empty($plugin->checked)) ? "checked" : ""; ?> />
                  <label for="paymeny_<?php echo $plugin->element?>" onclick="tiendaGetPaymentForm('<?php echo $plugin->element; ?>', 'payment_form_div', '<?php echo JText::_('COM_TIENDA_GETTING_PAYMENT_METHOD'); ?>'); $('validationmessage').set('html', '');"><?php echo JText::_( $plugin->name ); ?></label>
                  <br/>
                  <?php
              }                   
              ?>
                            
          <div id='payment_form_div' style="padding-top: 10px;">
          <?php
          if (!empty($this->payment_form_div))
          {
          	echo $this->payment_form_div;
          }
          ?>
          </div>
        <?php 
           }
      ?>
      <div id="validationmessage" style="padding-top: 10px;"></div>
    </div>	
<?php } ?>