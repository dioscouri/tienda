<?php defined('_JEXEC') or die('Restricted access'); ?>

<div style="clear: both;width:100%;">
	<div class="form_item">
		<div class="form_key">
			<?php echo JText::_( "COM_TIENDA_PASSWORD" ).': '.TiendaGrid::required(); ?>
		</div>
		<div class="form_input">
			<!--   Password 1st   -->
			<input id="password" name="password" type="password" onblur="tiendaCheckPassword( 'message-password', this.form, '<?php echo JText::_( 'VALIDATING' ); ?>' );" class="inputbox_required" size="30" value="" />
		</div>
		<div class="form_message" id="message-password"></div>
	</div>
	<div class="form_item">
		<div class="form_key">
			<?php echo JText::_( "COM_TIENDA_VERIFY_PASSWORD" ).': '.TiendaGrid::required(); ?>
		</div>
		<div class="form_input">
			<!--   Password 2nd   -->
			<input id="password2" name="password2" type="password" onblur="tiendaCheckPassword2( 'message-password2', this.form, '<?php echo JText::_( 'VALIDATING' ); ?>' );" class="inputbox_required" size="30" value="" />			
		</div>
		<div class="form_message" id="message-password2"></div>
	</div>
</div>