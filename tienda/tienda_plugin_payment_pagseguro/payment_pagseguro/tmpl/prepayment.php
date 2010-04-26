<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php echo JText::_( "Tienda Paypal Payment Stadard Preparation Message" ); ?>

<form action='<?php echo $vars->post_url; ?>' method='post'>

<!--USER INFO-->
    <input type='hidden' name='first_name' value='<?php echo @$vars->first_name; ?>'>
    <input type='hidden' name='last_name' value='<?php echo @$vars->last_name; ?>'>
    <input type='hidden' name='email' value='<?php echo @$vars->email; ?>'>

<!--SHIPPING ADDRESS PROVIDED-->
    <input type='hidden' name='address1' value='<?php echo @$vars->address_1; ?>'>
    <input type='hidden' name='address2' value='<?php echo @$vars->address_2; ?>'>
    <input type='hidden' name='city' value='<?php echo @$vars->city; ?>'>
    <input type='hidden' name='country' value='<?php echo @$vars->country; ?>'>
    <input type='hidden' name='state' value='<?php echo @$vars->region; ?>'>
    <input type='hidden' name='zip' value='<?php echo @$vars->postal_code; ?>'>

<!--CART INFO AGGREGATED-->
    <input type='hidden' name='amount_1' value='<?php echo TiendaHelperBase::number( @$vars->orderpayment_amount, array( 'thousands' =>'' ) ); ?>'>
    <input type='hidden' name='item_name_1' value='<?php echo JText::_( "Order Number" ).": ".$vars->order_id; ?>'>
    <input type='hidden' name='item_number_1' value='<?php echo $vars->order_id; ?>'>
    <input type='hidden' name='custom' value='<?php echo @$vars->orderpayment_id; ?>'>
    <!-- IPN-PDT  ONLY -->
    <input type='hidden' name='invoice' value='<?php echo @$vars->orderpayment_id; ?>'>

<!--PAYPAL VARIABLES-->
	<input type='hidden' name='cmd' value='_cart'>
	<input type="hidden" name="business" value="<?php echo $vars->merchant_email; ?>" />
	<input type='hidden' name='return' value='<?php echo JRoute::_( $vars->return_url ); ?>'>
	<input type='hidden' name='cancel_return' value='<?php echo JRoute::_( $vars->cancel_url ); ?>'>
	<input type="hidden" name="notify_url" value="<?php echo JRoute::_( $vars->notify_url ); ?>" />
	<input type='hidden' name='currency_code' value='<?php echo $vars->currency_code; ?>'>
	<input type='hidden' name='no_note' value='1'>
	<input type="hidden" name="upload" value="1">

    <?php echo JText::_('Click The Paypal Button to Complete Your Order'); ?>
	
	<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but02.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />

</form>
