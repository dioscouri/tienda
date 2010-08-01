<?php defined('_JEXEC') or die('Restricted access'); ?>

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

    <?php 
    switch($vars->cmd) 
    { 
        case "_xclick-subscriptions":
            ?>
            <!--ORDER INFO-->
            <?php // for cart Paypal payments, custom is the product_id? ?>
            <input type='hidden' name='custom' value='<?php echo @$vars->orderpayment_id; ?>'>
            <input type='hidden' name='invoice' value='<?php echo @$vars->orderpayment_id; ?>'>
            <input type='hidden' name='item_name' value='<?php echo JText::_( "Order Number" ).": ".$vars->order_id; ?>'>
            <input type='hidden' name='item_number' value='<?php echo $vars->order_id; ?>'>
            
            <!--SUB INFO-->
            <input type="hidden" name="sra" value="1" />
            <input type="hidden" name="src" value="1" />
            <input type="hidden" name="no_shipping" value="1" />
            <input type="hidden" name="srt" value="<?php echo $vars->order->recurring_payments; ?>" />
            <input type="hidden" name="a3" value="<?php echo TiendaHelperBase::number( $vars->order->recurring_amount, array( 'thousands' =>'', 'decimal'=> '.' ) ); ?>" />
            <input type="hidden" name="p3" value="<?php echo $vars->order->recurring_period_interval; ?>" />                         
            <input type="hidden" name="t3" value="<?php echo $vars->order->recurring_period_unit; ?>" />
            
            <?php if ($vars->order->recurring_trial): ?>
                <input type="hidden" name="a1" value="<?php echo TiendaHelperBase::number( $vars->order->recurring_trial_price, array( 'thousands' =>'', 'decimal'=> '.' ) ); ?>" />
                <input type="hidden" name="p1" value="<?php echo $vars->order->recurring_trial_period_interval; ?>" />                         
                <input type="hidden" name="t1" value="<?php echo $vars->order->recurring_trial_period_unit; ?>" />
            <?php endif; ?>
            
            <div id="payment_paypal">
                <div class="prepayment_message">
                    <?php echo JText::_( "Tienda Paypal Payment Standard Preparation Message Recurring Only" ); ?>
                </div>
                <div class="prepayment_action">
                    <span><?php echo JText::_('Click The Paypal Button to Complete Your Order'); ?></span>
                    <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but20.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
                    <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
                </div>
            </div>
            
            <?php
            break;
        case "_cart":
        default:
            ?>
            <!--CART INFO AGGREGATED-->
            <!--ORDER INFO-->
            <?php // for cart Paypal payments, custom is the orderpayment_id ?>
            <input type='hidden' name='custom' value='<?php echo @$vars->orderpayment_id; ?>'>
            <input type='hidden' name='amount_1' value='<?php echo TiendaHelperBase::number( @$vars->orderpayment_amount, array( 'thousands' =>'', 'decimal'=> '.' ) ); ?>'>
            <input type='hidden' name='item_name_1' value='<?php echo JText::_( "Order Number" ).": ".$vars->order_id; ?>'>
            <input type='hidden' name='item_number_1' value='<?php echo $vars->order_id; ?>'>
            <input type="hidden" name="upload" value="1">
            <input type='hidden' name='invoice' value='<?php echo @$vars->orderpayment_id; ?>'>

            <div id="payment_paypal">
                <div class="prepayment_message">
                    <?php echo JText::_( "Tienda Paypal Payment Standard Preparation Message" ); ?>
                </div>
                <div class="prepayment_action">
                    <span><?php echo JText::_('Click The Paypal Button to Complete Your Order'); ?></span>
                    <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but02.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
                    <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
                </div>
            </div>
            <?php
            break;            
    }
    ?>

<!--PAYPAL VARIABLES-->
	<input type='hidden' name='cmd' value='<?php echo $vars->cmd; ?>'>
	<input type="hidden" name="business" value="<?php echo $vars->merchant_email; ?>" />
	<input type='hidden' name='return' value='<?php echo JRoute::_( $vars->return_url ); ?>'>
	<input type='hidden' name='cancel_return' value='<?php echo JRoute::_( $vars->cancel_url ); ?>'>
	<input type="hidden" name="notify_url" value="<?php echo JRoute::_( $vars->notify_url ); ?>" />
	<input type='hidden' name='currency_code' value='<?php echo $vars->currency_code; ?>'>
	<input type='hidden' name='no_note' value='1'>
</form>
