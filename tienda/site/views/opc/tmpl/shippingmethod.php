<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $currency = Tienda::getInstance()->get( 'default_currencyid', 1); ?>

<form id="opc-shipping-method-form" name="opc-shipping-method-form" action="" method="post">
    
    <ul>
    <?php
    if (!empty($this->rates)) 
    {
        ?>
        <li>
        <?php      
        foreach ($this->rates as $key=>$rate) 
        {
            $checked = "";
    
            if ( !empty($this->default_rate) && $this->default_rate['name'] == $rate['name'] )
            {
            	$checked = "checked";                        
            }        	        		
            ?>
            <input id="shipping_<?php echo $rate['element'] . "_" . $key; ?>" name="shipping_plugin" rel="<?php echo $rate['name']; ?>" type="radio" value="<?php echo $rate['element'] . "." . $key ?>" <?php echo $checked; ?> />
            <label for="shipping_<?php echo $rate['element'] . "_" . $key; ?>"><?php echo $rate['name']; ?> ( <?php echo TiendaHelperBase::currency( $rate['total'], $currency ); ?> )</label>
            <?php
        }
        ?>
        </li>
        <?php
    }
        else
    {
        ?>
        <li id="opc-no-shipping-rates">
            <p class="text-error">
            <?php echo JText::_('COM_TIENDA_NO_SHIPPING_RATES_FOUND'); ?>
            </p>
        </li>
        <?php
    }
    ?>
    </ul>
    
    <div>
        <a id="opc-shipping-method-button" class="btn btn-primary" onclick="Opc.setShippingMethod();"><?php echo JText::_('COM_TIENDA_CONTINUE') ?></a>
    </div>

</form>