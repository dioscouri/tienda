<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_( "index.php?option=com_tienda&view=checkout" ); ?>" method="post" name="adminForm" enctype="multipart/form-data">
	<div class="note">
		<?php echo JText::_( "TIENDA CARDPAY PAYMENT PREPARATION MESSAGE" ); ?>
	</div>
	<input type="submit" class="button" value="<?php echo JText::_('Click Here to Complete Order'); ?>" />
	<input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>' />
	<input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>' />
	<input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>' />
	<input type='hidden' name='task' value='confirmPayment' />
	<input type='hidden' name='paction' value='redirect' />    
	<?php echo JHTML::_( 'form.token' ); ?>
</form>