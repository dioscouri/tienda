<?php 
defined('_JEXEC') or die('Restricted access'); 
?>

<form action="<?php echo @$vars->url; ?>" method="post" name="adminForm" enctype="multipart/form-data">

	<div class="prepayment_message">
    	<?php echo JText::_( "Tienda 2Checkout Preparation Message" ); ?>
    </div>
    <div class="prepayment_action">
    	<div style="float: left; padding: 10px;"><input type="submit" class="button" value="<?php echo JText::_('Click Here to Pay using 2Checkout'); ?>" /></div>
        <div style="float: left; padding: 10px;"><?php echo "<b>".JText::_( "Checkout Amount").":</b> ".TiendaHelperBase::currency( @$vars->total ); ?></div>
          <div style="clear: both;"></div>
    </div>    
    <input type='hidden' name='cart_order_id' value='<?php echo @$vars->cart_order_id; ?>'>
    <input type='hidden' name='merchant_order_id' value='<?php echo @$vars->merchant_order_id; ?>'>
    <input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>'>
    <input type='hidden' name='total' value='<?php echo @$vars->total; ?>'>
    <input type='hidden' name='x_Receipt_Link_URL' value='<?php echo JRoute::_( @$vars->x_Receipt_Link_URL ); ?>'>
    <input type='hidden' name='pay_method' value='<?php echo @$vars->pay_method; ?>'>
    <input type='hidden' name='sid' value='<?php echo @$vars->sid; ?>'>
    <?php if(@$vars->demo) { ?>
    <input type='hidden' name='demo' value='Y'>
    <?php } ?>
    <?php if(@$vars->skip_landing) { ?>
    <input type='hidden' name='skip_landing' value='1'>
    <?php } ?>
    <input type='hidden' name='lang' value='<?php echo @$vars->lang; ?>'>
    
    <input type='hidden' name='first_name' value='<?php echo @$vars->first_name; ?>'>
    <input type='hidden' name='last_name' value='<?php echo @$vars->last_name; ?>'>
    <input type='hidden' name='email' value='<?php echo @$vars->email; ?>'>
    <input type='hidden' name='street_address' value='<?php echo @$vars->street_address; ?>'>
    <input type='hidden' name='street_address2' value='<?php echo @$vars->street_address2; ?>'>
    <input type='hidden' name='city' value='<?php echo @$vars->city; ?>'>
    <input type='hidden' name='country' value='<?php echo @$vars->country; ?>'>
    <input type='hidden' name='state' value='<?php echo @$vars->state; ?>'>
    <input type='hidden' name='zip' value='<?php echo @$vars->zip; ?>'>
    
    <input type='hidden' name='ship_name' value='<?php echo @$vars->ship_name; ?>'>
    <input type='hidden' name='ship_street_address' value='<?php echo @$vars->ship_street_address; ?>'>
    <input type='hidden' name='ship_street_address2' value='<?php echo @$vars->ship_street_address2; ?>'>
    <input type='hidden' name='ship_city' value='<?php echo @$vars->ship_city; ?>'>
    <input type='hidden' name='ship_country' value='<?php echo @$vars->ship_country; ?>'>
    <input type='hidden' name='ship_state' value='<?php echo @$vars->ship_state; ?>'>
    <input type='hidden' name='ship_zip' value='<?php echo @$vars->ship_zip; ?>'>
</form>