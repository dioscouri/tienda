<?php defined('_JEXEC') or die('Restricted access'); ?>

<h3><?php echo JText::_("Select a Shipping Method") ?></h3>
<input type="button" onclick="tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form )" value="<?php echo JText::_("Click here to determine your shipping rates "); ?>" />

<input type="hidden" id="shippingrequired" name="shippingrequired" value="1"  />