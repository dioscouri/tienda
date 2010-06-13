<?php defined('_JEXEC') or die('Restricted access'); ?>

<h3><?php echo JText::_("Select a Shipping Method") ?></h3>
<input type="button" onclick="tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form )" value="<?php echo JText::_("Click here to update your shipping rates "); ?>" />
<p><?php echo JText::_("Please select your preferred shipping method below"); ?>:</p>

<input type="hidden" id="shippingrequired" name="shippingrequired" value="1" />

<?php
    if (!empty($this->rates)) 
    {                  
        foreach ($this->rates as $rate) 
        {
            ?>
            <input name="shipping_plugin" type="radio" value="<?php echo $rate['element'] ?>" onClick="tiendaSetShippingRate('<?php echo $rate['name']; ?>','<?php echo $rate['price']; ?>',<?php echo $rate['tax']; ?>,<?php echo $rate['extra']; ?>);" /> <?php echo $rate['name']; ?> ( <?php echo TiendaHelperBase::currency( $rate['total'] ); ?> )<br />
            <br/>
            <?php
        }
    }
        else
    {
        echo JText::_( "There was an error collecting shipping rates" );
    }
?>
<input type="hidden" name="shipping_price" id="shipping_price" value="" />
<input type="hidden" name="shipping_tax" id="shipping_tax" value="" />
<input type="hidden" name="shipping_name" id="shipping_name" value="" />
<input type="hidden" name="shipping_extra" id="shipping_extra" value="" />

<div id='shipping_form_div' style="padding-top: 10px;"></div>
    
<!--    COMMENTS   -->     
<h3><?php echo JText::_("Shipping Notes") ?></h3>
<?php echo JText::_( "Add optional notes for shipment here" ); ?>:
<br/>
<textarea id="customer_note" name="customer_note" rows="5" cols="70"></textarea>


