<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $item = $vars->product; ?>
<?php $redirect = $vars->redirect; ?>
<?php $return = $vars->return; ?>
<?php 
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
?>

<div>
    <div id="validationmessage"></div>
    <form action="<?php echo JRoute::_( 'index.php?option=com_tienda&view=products' ); ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >
        <input type="hidden" name="task" id="task" value="addtocart" />
        <input type="hidden" name="redirect" value="<?php echo base64_encode( $redirect ); ?>" />
        <input type="hidden" name="return" value="<?php echo base64_encode( $return ); ?>" />
    <!--base price-->
    <span id="product_price" class="product_price">
    <?php echo TiendaHelperBase::currency($item->price); ?>
    </span>
    
    <!--attribute options-->
    <div id='product_attributeoptions'>
    <?php
    $attributes = TiendaHelperProduct::getAttributes( $item->product_id );
    foreach ($attributes as $attribute)
    {
        ?>
        <div class="pao" id='productattributeoption_<?php echo $attribute->productattribute_id; ?>'>
        <?php
        echo "<span>".$attribute->productattribute_name." : </span>";
        
        $key = 'attribute_'.$attribute->productattribute_id;
        $selected = (!empty($values[$key])) ? $values[$key] : ''; 
        
        if ($item->product_check_inventory == 1) 
        {
            // $attribs = array('class' => 'inputbox', 'size' => '1','onchange'=>"TiendaCheckStock();");
            $attribs = array('class' => 'inputbox', 'size' => '1','onchange'=>"tiendaUpdateAddToCart( 'product_buy', this.form );");
            echo TiendaSelect::productattributeoptions( $attribute->productattribute_id, $selected, $key, $attribs  );
        } 
            else 
        {
            echo TiendaSelect::productattributeoptions( $attribute->productattribute_id, $selected, $key );
        }   
        ?>
        </div>
        <?php
    }
    ?>
    </div>
    
    <?php if ($item->quantity_restriction) : ?>
        <input type="hidden" name="product_qty" value="1" size="5" />
    <?php else : ?>
    <!--quantity-->
    <div id='product_quantity_input'>
        <input type="hidden" name="product_qty" value="1" size="5" />
    </div>
    <?php endif; ?>
    
    <!-- Add to cart button ---> 
    <div id='add_to_cart' style="display: block;"> 
        <input type="hidden" name="product_id" value="<?php echo $item->product_id; ?>" />
        <input type="hidden" name="filter_category" value="1" />
        <?php echo JHTML::_( 'form.token' ); ?>
        
        <?php $onclick = "tiendaFormValidation( 'index.php?option=com_tienda&view=products&task=validate&format=raw', 'validationmessage', 'addtocart', document.adminForm );"; ?>
        
        <?php 
        if (empty($item->product_check_inventory) || (!empty($item->product_check_inventory) && empty($this->invalidQuantity)) ) :
            switch (TiendaConfig::getInstance()->get('cartbutton', 'image')) 
            {
                case "button":
                    ?>
                    <input onclick="<?php echo $onclick; ?>" value="<?php echo JText::_('Add to Cart'); ?>" type="button" class="button" />
                    <?php
                    break;
                case "image":
                default:
                    ?> 
                    <img class='addcart' src='<?php echo Tienda::getUrl('images')."addcart.png"; ?>' alt='<?php echo JText::_('Add to Cart'); ?>' onclick="<?php echo $onclick; ?>" />
                    <?php
                    break;
            }
        endif; 
        ?>
    </div>
    
    </form>
</div>