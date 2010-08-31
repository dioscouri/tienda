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
                    <?php echo JText::_( "Tienda Google Check out Standard Preparation Message Recurring Only" ); ?>
                </div>
                <div class="prepayment_action">
                    <div style="float: left; padding: 10px;"><input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but20.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" /></div>
                    <?php echo @$vars->cart->CheckoutButtonCode("SMALL"); ?>
                    <div style="float: left; padding: 10px;"><?php echo "<b>".JText::_( "Checkout Amount").":</b> ".TiendaHelperBase::currency( @$vars->orderpayment_amount ); ?></div>
                    <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
                    <div style="clear: both;"></div>
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

            <?php if ($vars->mixed_cart) { ?>
                <div class="note_green">
                    <span class="alert"><?php echo JText::_( "Please Note" ) ?></span>
                    <?php echo JText::_( "Mixed Cart Message" ); ?>
                </div>
                
                <div id="payment_paypal">
                    <div class="prepayment_message">
                        <?php echo JText::_( "Tienda Paypal Payment Standard Preparation Message Mixed Cart" ); ?>
                    </div>
                    <div class="prepayment_action">
                        <div style="float: left; padding: 10px;"><input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but02.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" /></div>
                        <div style="float: left; padding: 10px;"><?php echo "<b>".JText::_( "First Checkout Amount").":</b> ".TiendaHelperBase::currency( @$vars->orderpayment_amount ); ?></div>
                        <div style="float: left; padding: 10px;"><?php echo "<b>".JText::_( "Second Checkout Amount").":</b> ".TiendaHelperBase::currency( $vars->amount ); ?></div>
                        <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
                        <div style="clear: both;"></div>
                    </div>
                </div>
            <?php } else { ?>
                <div id="payment_paypal">
                    <div class="prepayment_message">
                        <?php echo JText::_( "Tienda Google Check Out Preparation Message" ); ?>
                    </div>
                    <div class="prepayment_action">
                   	   <div style="float: left; padding: 10px;"><input type="image" src="<?php echo plg_tienda_escape($vars->button_url) ?>/buttons/checkout.gif?merchant_id=<?php echo plg_tienda_escape($vars->merchant_id) ?>&w=160&h=43&style=trans&variant=text&loc=en_US" border="0" name="submit" alt="Pay with Google Checkout" /></div>
                       <?php echo @$vars->cart->CheckoutButtonCode("SMALL"); ?>
                        <div style="float: left; padding: 10px;"><?php echo "<b>".JText::_( "Checkout Amount").":</b> ".TiendaHelperBase::currency( @$vars->orderpayment_amount ); ?></div>
                        <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
                        <div style="clear: both;"></div>
                    </div>
                </div>
            <?php } ?>
            <?php
            break;            
    }
    ?>

<!--GOOGLE CHECKOUT VARIABLES-->
	<input type='hidden' name='cmd' value='<?php echo $vars->cmd; ?>'>
	<input type="hidden" name="business" value="<?php echo $vars->merchant_email; ?>" />
	<input type='hidden' name='return' value='<?php echo JRoute::_( $vars->return_url ); ?>'>
	<input type='hidden' name='cancel_return' value='<?php echo JRoute::_( $vars->cancel_url ); ?>'>
	<input type="hidden" name="notify_url" value="<?php echo JRoute::_( $vars->notify_url ); ?>" />
	<input type='hidden' name='currency_code' value='<?php echo $vars->currency_code; ?>'>
	
	<input type="hidden" name="type_id" value="<?php echo plg_tienda_escape($vars->type_id) ?>" />
    <input type="hidden" name="r" value="<?php echo plg_tienda_escape($vars->r) ?>" />
				
	<input type='hidden' name='no_note' value='1'>
</form>
