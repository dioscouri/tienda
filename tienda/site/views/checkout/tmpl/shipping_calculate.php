<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php  if (!TiendaConfig::getInstance()->get('one_page_checkout')) :?>
<h3><?php echo JText::_("Select a Shipping Method") ?></h3>
<?php endif;?>
<input type="button" class="button" onclick="tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form )" value="<?php echo JText::_("Calculate shipping rates"); ?>" />

<input type="hidden" id="shippingrequired" name="shippingrequired" value="1"  />