<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $shipping_rates_text = JText::_( "Getting Shipping Rates" ); ?>

<h3><?php echo JText::_("Select a Shipping Method") ?></h3>
<input type="button" onclick="tiendaGetShippingRates( 'onCheckoutShipping_wrapper', this.form, '<?php echo $shipping_rates_text; ?>' )" value="<?php echo JText::_("Click here to update your shipping rates"); ?>" />
<p><?php echo JText::_("Please select your preferred shipping method below"); ?>:</p>

<input type="hidden" id="shippingrequired" name="shippingrequired" value="1" />

<?php
    if (!empty($this->rates)) 
    {                 
        //$dfltShippingMethod = TiendaConfig::getInstance()->get('defaultShippingMethod', '2');
        //$checkAlready = false;
		
        foreach ($this->rates as $rate) 
        {
            $checked = "";
            if (!empty($this->default_rate) && $this->default_rate['name'] == $rate['name'] )
            {
                $checked = "checked";
            }        	        		
            ?>
            <input name="shipping_plugin" type="radio" value="<?php echo $rate['element'] ?>" onClick="tiendaSetShippingRate('<?php echo $rate['name']; ?>','<?php echo $rate['price']; ?>',<?php echo $rate['tax']; ?>,<?php echo $rate['extra']; ?>, '<?php echo $rate['code']; ?>');" <?php echo $checked; ?> /> <?php echo $rate['name']; ?> ( <?php echo TiendaHelperBase::currency( $rate['total'] ); ?> )<br />
            <br/>
            <?php
        }
    }
        else
    {
        ?>
        <div class="note">
        <?php echo JText::_( "NO SHIPPING RATES FOUND" ); ?>
        </div>
        <?php
    }
?>
<input type="hidden" name="shipping_price" id="shipping_price" value="" />
<input type="hidden" name="shipping_tax" id="shipping_tax" value="" />
<input type="hidden" name="shipping_name" id="shipping_name" value="" />
<input type="hidden" name="shipping_code" id="shipping_code" value="" />
<input type="hidden" name="shipping_extra" id="shipping_extra" value="" />

<div id='shipping_form_div' style="padding-top: 10px;"></div>
    
<!--    COMMENTS   -->     
<h3><?php echo JText::_("Shipping Notes") ?></h3>
<?php echo JText::_( "Add optional notes for shipment here" ); ?>:
<br/>
<textarea id="customer_note" name="customer_note" rows="5" cols="70"></textarea>

<?php if (!empty($this->default_rate)) : ?>
    <?php $default_rate = $this->default_rate; ?>
    <script type="text/javascript">
        window.onload = tiendaSetShippingRate('<?php echo $default_rate['name']; ?>','<?php echo $default_rate['price']; ?>',<?php echo $default_rate['tax']; ?>,<?php echo $default_rate['extra']; ?>, '<?php echo $default_rate['code']; ?>');
    </script>
<?php endif; ?>
