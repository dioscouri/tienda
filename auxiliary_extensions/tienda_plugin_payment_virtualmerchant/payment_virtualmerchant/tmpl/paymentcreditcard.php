<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="note">
<?php echo JText::_('VIRTUALMERCHANT CREDITCARD FORM MESSAGE');?>
</div>

<script language="javascript">
function validateForm(frm)
{
	if (frm.credit_card.value == '' || isNaN(frm.credit_card.value) || frm.credit_card.value.length < 16 || frm.credit_card.value.length > 16) 
	{
		alert('<?php echo JText::_('please enter valid 16 digits card number'); ?>');
		frm.credit_card.focus();
		return false;
	}

	if (frm.card_cvv.value == ''  || isNaN(frm.card_cvv.value) || frm.card_cvv.value.length < 3 || frm.card_cvv.value.length > 4) 
	{
		alert('<?php echo JText::_('please enter valid cvv number'); ?>');
		frm.card_cvv.focus();
		return false;
	}

	return true;
}
</script>

<form action="<?php echo @$vars->action_url; ?>" method="post" name="adminForm">

	<table border=1 style="border-collapse:collapse;" cellspacing="2" cellpadding="2">
		<tr>
			<td>
				<table>
					<tr>
						<td>Creditcard Number: </td>
						<td><input type="text" name="credit_card" maxlength="18" size="20" value="" /></td>
					</tr>
					<tr>
						<td>Expiry Date: </td>
						<td>
							<select name="expire_month">
								<?php
								for ($i=1; $i<=12; $i++):
								?>
								<option value="<?php echo sprintf('%02d', $i);?>"><?php echo sprintf('%02d', $i);?></option>
								<?php endfor; ?>
							</select>
							&nbsp;
							<select name="expire_year">
								<?php
								for ($i=0; $i<20; $i++):
								?>
								<option value="<?php echo date('Y')+$i;?>"><?php echo date('Y')+$i;?></option>
								<?php endfor; ?>
							</select>
						</td>
					</tr>
					<tr>
						<td>Card CVV: </td>
						<td><input type="text" name="card_cvv" maxlength="4" size="4" value="" /></td>
					</tr>
					<tr><td colspan="2" align="right">(<?php echo JText::_('CVV: Generally a 3 digit number at the back of your card'); ?>)</td></tr>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td colspan="2">
							<input type="submit" class="button" value="<?php echo JText::_('Make Payment'); ?>" onclick="return validateForm(this.form);"/>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<input type="hidden" name="task" value="confirmPayment" />
	<input type="hidden" name="step" value="selectpayment" />
	<input type="hidden" name="order_id" value="<?php echo $vars->order_id;?>" />
	<input type="hidden" name="order_id" value="<?php echo $vars->order_id;?>" />

    <?php echo JHTML::_( 'form.token' ); ?>

</form>