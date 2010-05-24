<?php defined('_JEXEC') or die('Restricted access'); 
$rates = $this->rates;

foreach( $rates as $rate )
{
?>

<input name="shipping_rate" type="radio" value="<?php echo $rate['id'] ?>" onClick="tiendaSetShippingRate('<?php echo $rate['name']; ?>','<?php echo $rate['price']; ?>',<?php echo $rate['tax']; ?>,<?php echo $rate['extra']; ?>);" /> <?php echo $rate['name']; ?>

<?php 
}
?>

<input type="hidden" name="shipping_price" id="shipping_price" value="" />
<input type="hidden" name="shipping_tax" id="shipping_tax" value="" />
<input type="hidden" name="shipping_name" id="shipping_name" value="" />
<input type="hidden" name="shipping_extra" id="shipping_extra" value="" />
