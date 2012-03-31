<?php defined('_JEXEC') or die('Restricted access'); ?>
<table class="userlist" id="direct_payment_form">
	<tbody>
	<tr>
		<td class="title">		
			<form action="<?php echo plg_tienda_escape($vars->action_url) ?>" method="post">			
			<table>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_FIRST_NAME') ?> <span class="required">*</span></td>
					<td><input type="text" name="first_name" size="35" value="<?php echo plg_tienda_escape(@$vars->prepop['first_name']) ?>" maxlength="25" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_LAST_NAME') ?> <span class="required">*</span></td>
					<td><input type="text" name="last_name" size="35" value="<?php echo plg_tienda_escape(@$vars->prepop['last_name']) ?>" maxlength="25" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_STREET_ADDRESS') ?> <span class="required">*</span></td>
					<td><input type="text" name="address1" size="35" value="<?php echo plg_tienda_escape(@$vars->prepop['address1']) ?>" maxlength="100" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_STREET_ADDRESS_CONTINUED') ?></td>
					<td><input type="text" name="address2" size="35" value="<?php echo plg_tienda_escape(@$vars->prepop['address2']) ?>" maxlength="100" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_CITY') ?> <span class="required">*</span></td>
					<td><input type="text" name="city" size="35" value="<?php echo plg_tienda_escape(@$vars->prepop['city']) ?>" maxlength="40" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_STATE') ?> <span class="required">*</span></td>
					<td><input type="text" name="state" size="10" value="<?php echo plg_tienda_escape(@$vars->prepop['state']) ?>" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_POSTAL_CODE') ?> <span class="required">*</span></td>
					<td><input type="text" name="zip" size="10" value="<?php echo plg_tienda_escape(@$vars->prepop['zip']) ?>" maxlength="40" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_COUNTRY') ?> <span class="required">*</span></td>
					<td><?php echo $vars->country_input ?></td>
				</tr>
				<?php if (!$vars->user->get('id')): ?>			
					<tr>
						<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_EMAIL_ADDRESS') ?> <span class="required">*</span></td>
						<td><input type="text" name="email" size="35" value="<?php echo plg_tienda_escape(@$vars->prepop['email']) ?>" maxlength="127" /></td>
					</tr>				
				<?php endif; ?>				
				<tr>
					<td colspan="2"><hr/></td>
				</tr>				
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_CREDIT_CARD_TYPE') ?> <span class="required">*</span></td>
					<td><?php echo $vars->cctype_input ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_CARD_NUMBER') ?> <span class="required">*</span></td>
					<td><input type="text" name="cardnum" size="35" value="<?php echo plg_tienda_escape(@$vars->prepop['cardnum']) ?>" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_EXPIRATION_DATE') ?> <span class="required">*</span></td>
					<td>
						<input type="text" name="cardexp_month" size="2" maxlength="2" value="<?php echo plg_tienda_escape(@$vars->prepop['cardexp_month']) ?>" /> / 
						<input type="text" name="cardexp_year" size="4" maxlength="4" value="<?php echo plg_tienda_escape(@$vars->prepop['cardexp_year']) ?>" />
					</td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_TIENDA_PAYPALPRO_CARD_CVV_NUMBER') ?> <span class="required">*</span></td>
					<td><input type="text" name="cardcvv" size="10" value="" /></td>
				</tr>
			</table>
			<input type="hidden" name="order_id" value="<?php echo plg_tienda_escape($vars->row['order_id']) ?>" />	
			
			<?php 
				if(!isset($vars->row['orderpayment_id']))
				{
					$vars->row['orderpayment_id'] = $vars->row['item_number'];
				}			
			?>
			<input type="hidden" name="item_number" value="<?php echo plg_tienda_escape($vars->row['orderpayment_id']) ?>" />						
			<input type="submit" name="submit" value="<?php echo JText::_('COM_TIENDA_PAYPALPRO_COMPLETE_PURCHASE') ?>" />
			<?php echo $vars->token_input ?>
			</form>
			
			<p><?php echo JText::_('COM_TIENDA_PAYPALPRO_INDICATES_A_REQUIRED_FIELD')?></p>
		</td>
		  
	</tr>
	</tbody>
</table>
