<?php defined('_JEXEC') or die('Restricted access'); ?>

<table style="clear: both;">
	<tr>
		<td>
		<h4><?php echo JText::_("Email") ?></h4>
		</td>
		<td style="text-align: left;" id="tienda_email_address">
		<!--    Email Address   --> 
		<input onchange="tiendaRegistrationValidate(this, this.form, '<?php echo JText::_('Checking email availability.');?>' );"
			name="email_address" id="email_address" type="text" size="30"
			maxlength="250" class="inputbox required" value="" /> *</td>
	</tr>
	<tr>
		<td> <h4> <?php echo JText::_( 'Name' ); ?>: </h4>
		</td>
		<td><input onchange="tiendaRegistrationValidate(this, this.form, '' );" type="text" name="name" id="name" size="30" value=""
			class="inputbox required" maxlength="250" /> *</td>
	</tr>
	<tr>
		<td><h4><?php echo JText::_( 'Username' ); ?>:
		</h4></td>
		<td><input onchange="tiendaRegistrationValidate(this, this.form, '<?php echo JText::_('Checking username availability.');?>' );" type="text" id="username" name="username" size="30"
			value="" class="inputbox required validate-username" maxlength="25" />
		*</td>
	</tr>
	<tr>
		<td><h4><?php echo JText::_( 'Password' ); ?>:
		</h4></td>
		<td><input class="inputbox required validate-password" type="password"
			id="password" name="password" size="30" value="" /> 
		*</td>
	</tr>
	<tr>
		<td><h4><?php echo JText::_( 'Verify Password' ); ?>:
		</h4></td>
		<td><input onchange="tiendaRegistrationValidate(this, this.form, '' );" class="inputbox required validate-passverify"
			type="password" id="password2" name="password2" size="30" value="" />
		*</td>
	</tr>
</table>
<input type="hidden" id="tienda_target" name="target" value="" />

<?php  if (TiendaConfig::getInstance()->get('one_page_checkout')) :?>
<input id="tienda_btn_register" type="button" class="button" onclick="tiendaRegistrationValidate(this, this.form, '<?php echo JText::_('User registration is in progress.')?>' );" value="<?php echo JText::_( "REGISTER" ); ?>" />
or 
<a href="#" onclick="tiendaCheckoutMethodForm( 'tienda_checkout_method', '', '' ); ">
<?php echo JText::_('Cancel')?> 
</a>           
<?php endif;?>
