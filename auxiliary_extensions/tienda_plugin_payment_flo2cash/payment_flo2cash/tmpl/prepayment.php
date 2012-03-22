<?php 
defined('_JEXEC') or die('Restricted access'); 
?>
<form action='<?php echo $vars->post_url; ?>' method='post'>

    <div class="note">
        <?php echo JText::_('Flo2Cash secure payment'); ?>
    </div>
   
	<input type='hidden' name='cmd' value='_xclick'/>	
	<input type='hidden' name='account_id' value='<?php echo @$vars->f2cAccid; ?>' />
	<input type='hidden' name='return_url' value='<?php echo @$vars->f2cReturnURL; ?>' />
	<input type='hidden' name='notification_url' value='' />
	<input type='hidden' name='header_image' value='<?php echo @$vars->f2cHeaderImage; ?>' />
	<input type='hidden' name='header_border_bottom' value='<?php echo @$vars->f2cHeaderBorderBottom; ?>' />
	<input type='hidden' name='header_background_colour' value='<?php echo @$vars->f2cHeaderBackgroundColor; ?>' />
	<input type='hidden' name='store_card' value='<?php echo @$vars->f2cStoreCard; ?>' />
	<input type='hidden' name='csc_required' value='<?php echo @$vars->f2cCSCRequired; ?>' />
	<input type='hidden' name='display_customer_email' value='<?php echo @$vars->f2cDisplayEmail; ?>' />
	<input type='hidden' name='amount' value='<?php echo $vars->orderpayment_amount; ?>' />
	<input type='hidden' name='item_name' value='<?php print $vars->item_list; ?>' />
	<input type='hidden' name='reference' value='<?php echo @$vars->orderpayment_id; ?>' />
	<input type='hidden' name='particular' value="<?php echo 'Particular - ' . @$vars->orderpayment_id; ?>"/>
    <input type="submit" class="button" value="<?php echo JText::_('Click here to pay securely using Flo2Cash'); ?>" />    
</form>
				
