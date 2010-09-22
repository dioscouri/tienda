<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php echo JText::_( "Tienda Paypal Payment Stadard Preparation Message" ); ?>

<form action='<?php echo $vars->post_url; ?>' method='post'>

<!--PAYSON VARIABLES-->
	<input type="hidden" name="SellerEmail" value="<?php echo $vars->SellerEmail; ?>" />
	<input type='hidden' name='BuyerEmail' value='<?php echo @$vars->BuyerEmail; ?>'>
    <input type='hidden' name='BuyerFirstName' value='<?php echo @$vars->BuyerFirstName; ?>'>
    <input type='hidden' name='BuyerLastName' value='<?php echo @$vars->BuyerLastName; ?>'>
    <input type='hidden' name='Description' value='<?php echo JText::_( "Order Number" ).": ".$vars->order_id; ?>'>
    <input type='hidden' name='Cost' value='<?php echo  @$vars->Cost; ?>'>
    <input type="hidden" name="ExtraCost" value="<?php echo $vars->ExtraCost; ?>" />
    <input type='hidden' name='RefNr' value='<?php echo $vars->RefNr; ?>'>

	<input type='hidden' name='OkUrl' value='<?php echo JRoute::_( $vars->OkUrl ); ?>'>
	<input type='hidden' name='CancelUrl' value='<?php echo JRoute::_( $vars->CancelUrl ); ?>'>
	
	<input type='hidden' name='AgentId' value='<?php echo $vars->AgentId; ?>'>
	<input type='hidden' name='GuaranteeOffered' value='<?php echo $vars->GuaranteeOffered; ?>'>
	<input type='hidden' name='MD5' value='<?php echo $vars->MD5; ?>'>

	<!--  input type='hidden' name='PaymentMethod' value='<?php echo $vars->PaymentMethod; ?>' -->

    <?php echo JText::_('Click The Payson Button to Complete Your Order'); ?>
	
	<input type="image" src="<?php echo $vars->payson_image?>" border="0" name="submit" alt="Make payment with Payson" />
</form>
