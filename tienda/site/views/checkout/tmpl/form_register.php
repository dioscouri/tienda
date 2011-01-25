<?php defined('_JEXEC') or die('Restricted access'); ?>

<table style="clear: both;">
	<tr>
		<td>
		<h4><?php echo JText::_("Email") ?></h4>
		</td>
		<td style="text-align: left;">
		<!--    Email Address   --> 
		<input
			name="email_address" id="email_address" type="text" size="48"
			maxlength="250" class="inputbox required" /> *</td>
	</tr>
	<tr>
		<td> <h4> <?php echo JText::_( 'Name' ); ?>: </h4>
		</td>
		<td><input type="text" name="name" id="name" size="48" value=""
			class="inputbox required" maxlength="250" /> *</td>
	</tr>
	<tr>
		<td><h4><?php echo JText::_( 'Username' ); ?>:
		</h4></td>
		<td><input type="text" id="username" name="username" size="48"
			value="" class="inputbox required validate-username" maxlength="25" />
		*</td>
	</tr>
	<tr>
		<td><h4><?php echo JText::_( 'Password' ); ?>:
		</h4></td>
		<td><input class="inputbox required validate-password" type="password"
			id="password" name="password" size="48" value="" /> 
		*</td>
	</tr>
	<tr>
		<td><h4><?php echo JText::_( 'Verify Password' ); ?>:
		</h4></td>
		<td><input class="inputbox required validate-passverify"
			type="password" id="password2" name="password2" size="48" value="" />
		*</td>
	</tr>
</table>

