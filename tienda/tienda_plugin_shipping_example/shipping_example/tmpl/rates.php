<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php Tienda::load( 'TiendaHelperBase', 'helpers._base' ); ?>
<?php jimport('joomla.html') ?>

<?php 
$rates = array();
foreach($vars->rates as $rate){
	$r = new JObject;
	$r->value = $rate->shipping_rate_price;
	$r->text = TiendaHelperBase::currency($rate->shipping_rate_price, $vars->order->currency_id);
	$rates[] = &$r;
}
?>
<div class="shipping_rates">
<?php 
echo JHTML::_( 'select.radiolist', $rates, 'shipping_rate', array() );
?>
</div>