<?php defined('_JEXEC') or die('Restricted access'); ?>
<fieldset class="tienda-expanded" id="customer-pane">
	<legend class="tienda-collapse-processed"><?php echo JText::_('COM_TIENDA_CUSTOMER_INFORMATION')?></legend>
	<div id="tienda_customer">
		<div class="note">
			<?php echo JText::_('COM_TIENDA_ORDER_INFORMATION_WILL_BE_SENT_TO_YOUR_ACCOUNT_E-MAIL_LISTED_BELOW.')?>	
		</div>
			<div class="tienda_checkout_method_user_email">
				<?php
					if($this->user->id)
						$email_address = $this->user->email;
					else
						$email_address = '';
				?>

				<?php echo JText::_('COM_TIENDA_E-MAIL_ADDRESS');?>: <span id="user_email_span"><?php echo $email_address; ?></span>
				<input type="text" id="email_address" name="email_address" value="<?php echo $email_address; ?>"/>
				<input type="button" id="email_address_button_edit" onclick="tiendaCheckoutToogleEditEmail( 'user_email_validation',document.adminForm, true );" value="<?php echo JText::_('COM_TIENDA_EDIT');?>" />
				<input type="button" id="email_address_button_cancel" onclick="tiendaCheckoutToogleEditEmail( 'user_email_validation',document.adminForm, false );" value="<?php echo JText::_('COM_TIENDA_CANCEL');?>" />
			</div>
			<div id="user_email_validation"></div>
	</div>
</fieldset>