<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="<?php echo plg_tienda_escape($vars->action_url) ?>" method="post">
	<input type="hidden" name="item_number" value="<?php echo plg_tienda_escape($vars->row['order_id']) ?>" />
	<?php echo $vars->token_input ?>			
	<input type="image" id="express_checkout_button" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />			
</form>