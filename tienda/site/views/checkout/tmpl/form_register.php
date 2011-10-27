<?php defined('_JEXEC') or die('Restricted access'); ?>

<div style="clear: both;width:100%;">
	<div class="form_item">
		<div class="form_key">
			<?php echo JText::_("COM_TIENDA_EMAIL").': '.TiendaGrid::required(); ?>
		</div>
		<div class="form_input">
			<!--   Email Address   --> 
			<input id="email_address" name="email_address" type="text" onchange="tiendaCheckoutCheckEmail( 'message-email', this.form );" class="inputbox_required" size="30" maxlength="250" value="" />			
		</div>
		<div class="form_message" id="message-email"></div>
	</div>
	<div class="form_item">
		<div class="form_key"> 
			<?php echo JText::_( "COM_TIENDA_NAME" ).': '.TiendaGrid::required(); ?>
		</div>
		<div class="form_input">
			<!--   Name   -->
			<input id="name"  name="name" type="text" size="30" value="" class="inputbox_required" maxlength="250" />			
		</div>
	</div>
	<div class="form_item">
		<div class="form_key">
			<?php echo JText::_( "COM_TIENDA_USER_NAME" ).': '.TiendaGrid::required(); ?>
		</div>
		<div class="form_input">
			<!--   Username   -->
			<input id="username" name="username" type="text" class="inputbox_required" size="30"	value="" maxlength="25" />			
		</div>
		<div class="form_message" id="message-username"></div>
	</div>
	<div class="form_item">
		<div class="form_key">
			<?php echo JText::_( "COM_TIENDA_PASSWORD" ).': '.TiendaGrid::required(); ?>
		</div>
		<div class="form_input">
			<!--   Password 1st   -->
			<input id="password" name="password" type="password" onchange="tiendaCheckPassword( 'message-password', this.form );" class="inputbox_required" size="30" value="" />			
		</div>
		<div class="form_message" id="message-password"></div>
	</div>
	<div class="form_item">
		<div class="form_key">
			<?php echo JText::_( "COM_TIENDA_VERIFY_PASSWORD" ).': '.TiendaGrid::required(); ?>
		</div>
		<div class="form_input">
			<!--   Password 2nd   -->
			<input id="password2" name="password2" type="password" onchange="tiendaCheckPassword2( 'message-password2', this.form );" class="inputbox_required" size="30" value="" />			
		</div>
		<div class="form_message" id="message-password2"></div>
	</div>
</div>
<input type="hidden" id="tienda_target" name="target" value="" />

<?php  if (TiendaConfig::getInstance()->get('one_page_checkout')) :?>
<input id="tienda_btn_register" type="button" class="button" onclick="tiendaRegistrationValidate(this, this.form, '<?php echo JText::_('User registration is in progress.')?>' );" value="<?php echo JText::_( "REGISTER" ); ?>" />
or 
<a href="#" onclick="tiendaCheckoutMethodForm( 'tienda_checkout_method', '', '' ); ">
<?php echo JText::_("COM_TIENDA_CANCEL")?> 
</a>           
<?php endif;?>