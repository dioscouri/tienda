<?php defined('_JEXEC') or die('Restricted access'); ?>	
<?php $one_page = TiendaConfig::getInstance()->get('one_page_checkout', 0); ?>

<?php if( $one_page ): ?>	
	<?php if(count($this->payment_plugins)):?>
		<?php foreach($this->payment_plugins as $payment_plugin):?>
		<input value="<?php echo $payment_plugin->element; ?>" onclick="tiendaGetPaymentForm('<?php echo $payment_plugin->element; ?>', 'payment_form_div', '<?php echo JText::_( 'Getting Payment Method' ); ?>'); $('validationmessage').setHTML(''); $('payment_form_div').addClass('note');" name="payment_plugin" type="radio" <?php echo (!empty($payment_plugin->checked)) ? "checked" : ""; ?> />
		<?php echo JText::_( $payment_plugin->name ); ?>
		<br />
		<?php endforeach;?>
		
		 <div id='payment_form_div' <?php if(!empty($this->payment_form_div)) echo 'class="note"';?> style="padding-top: 5px;">
		 <?php if(!empty($this->payment_form_div)):?>
		 	<?php echo $this->payment_form_div;?>
		 <?php endif;?>
		 </div>
	<?php endif;?>
<?php else: ?>
	<div class="note">
		<?php echo count($this->payment_plugins) ? JText::_("Please select your preferred payment method below").':' : JText::_( "No payment method are available for your address.  Please select a different address or contact the administrator." );?>
	</div>
  <div id='onCheckoutPayment_wrapper'>
      <?php        
          if ($this->payment_plugins) 
          {                          	                  	
              foreach ($this->payment_plugins as $plugin) 
              {
                  ?>
                  <input value="<?php echo $plugin->element; ?>" onclick="tiendaGetPaymentForm('<?php echo $plugin->element; ?>', 'payment_form_div', '<?php echo JText::_( 'Getting Payment Method' ); ?>'); $('validationmessage').setHTML('');" name="payment_plugin" type="radio" <?php echo (!empty($plugin->checked)) ? "checked" : ""; ?> />
                  <?php echo JText::_( $plugin->name ); ?>
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
<?php endif; ?>