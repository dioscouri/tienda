<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'pos.css', 'media/com_tienda/css/'); ?>
<?php $row = @$this->product; ?>

<div id="product_buy">
    <?php
    Tienda::load( 'TiendaHelperBase', 'helpers._base' );
    $helper_product = TiendaHelperBase::getInstance( 'Product' );
    $attributes = $helper_product->getAttributes( $row->product_id, 0 );
    $default = $helper_product->getDefaultAttributeOptions($attributes);
    $selected_opts = array();
    foreach ($attributes as $attribute)
    {
        ?>
        <div class="pao" id='productattributeoption_<?php echo $attribute->productattribute_id; ?>'>
            <?php
            echo "<span>".$attribute->productattribute_name." : </span>";
            
            $key = 'attribute_'.$attribute->productattribute_id;
            $selected = (!empty($values[$key])) ? $values[$key] : $default[$attribute->productattribute_id]; 
            $attribs = array();
            //$attribs = array('class' => 'inputbox', 'size' => '1', 'onchange'=>"tiendaUpdateAddToCart(  'products', 'product_buy', document.adminForm );");
            echo TiendaSelect::productattributeoptions( $attribute->productattribute_id, $selected, $key, $attribs, null, $selected_opts  );
            ?>
        </div>
        <?php
    }
    ?>
    
    <?php if (!empty($this->onDisplayProductAttributeOptions)) : ?>
        <div class='onDisplayProductAttributeOptions_wrapper'>
        <?php echo $this->onDisplayProductAttributeOptions; ?>
        </div>
    <?php endif; ?>
    
    <?php echo JText::_( "Quantity" ); ?>
    <input type="text" name="quantity" value="1" size="10" />
    <br/>
    <?php echo JText::_( "Base Price" ); ?>: <?php echo TiendaHelperBase::currency( $row->price ); ?>
    <br/>
    
    <input type="submit" name="add_to_cart" value="<?php echo JText::_( "ADD_TO_ORDER" ); ?>" class="button" />
    <input type="hidden" name="task" id="task" value="addtocart" />
    <input type="hidden" name="product_id" value="<?php echo $row->product_id; ?>" />
</div>