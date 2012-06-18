<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php  if (!Tienda::getInstance()->get('one_page_checkout')) :?>
	<h3><?php echo JText::_('COM_TIENDA_SELECT_A_SHIPPING_METHOD') ?></h3>
	<input type="button" class="button" onclick="tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form, '<?php echo JText::_('COM_TIENDA_UPDATING_SHIPPING_RATES')?>', '<?php echo JText::_('COM_TIENDA_UPDATING_CART')?>' )" value="<?php echo JText::_('COM_TIENDA_CALCULATE_SHIPPING_RATES'); ?>" />
<?php endif; ?>
<input type="hidden" id="shippingrequired" name="shippingrequired" value="1"  />
<div class="note">
	<?php echo JText::_('COM_TIENDA_NO_SHIPPING_RATES_FOUND'); ?>
</div>
