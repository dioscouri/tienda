<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php if (empty($this->showPayment)) { ?>
    <p>
        <?php echo JText::_( "COM_TIENDA_NO_PAYMENT_NECESSARY" ); ?>
    </p>
    
    <div id="validationmessage" style="padding-top: 10px;"></div>
    
<?php } else { ?>	
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
<?php } ?>