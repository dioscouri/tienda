<?php
defined('_JEXEC') or die('Restricted access');
$item = @$this->item;
$form = @$this->form;
$values = @$this->values; 
?>

<div>
    <div id="validationmessage"></div>
    
    <form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >
    <!--base price-->
    <span id="product_price" class="product_price">
        <?php 
        echo TiendaHelperBase::currency($item->price); 
        ?>
    </span>
    <span id="product_price_extra" class="product_price_extra">
        <?php            
        // For UE States, we should let the admin choose to show (+19% vat) and (link to the shipping rates)
        if ($this->show_tax)
        {
            if (!empty($this->tax))
            {
                echo sprintf( JText::_('INCLUDE_TAX'), TiendaHelperBase::currency($this->tax));
            }
                else
            {
                echo JText::_('PLUS_TAX');
            }
        }
        if (TiendaConfig::getInstance()->get( 'display_prices_with_shipping') && !empty($item->product_ships))
        {
            echo '<br /><a href="'.$this->shipping_cost_link.'" target="_blank">'.sprintf( JText::_('LINK_TO_SHIPPING_COST'), $this->shipping_cost_link).'</a>' ;
        }
        ?>
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
    
    <?php if (!empty($this->onDisplayProductAttributeOptions)) : ?>
        <div id='onDisplayProductAttributeOptions_wrapper'>
        <?php echo $this->onDisplayProductAttributeOptions; ?>
        </div>
    <?php endif; ?>
    
    </div>
    
    <!--quantity-->
    <div id='product_quantity_input'>
        <span class="title"><?php echo JText::_( "Quantity" ); ?>:</span>
    <?php if($item->product_check_inventory==1) {  ?>   
        <input type="text" name="product_qty" value="1" size="5" />  
   <?php } else {?>
        <input type="text" name="product_qty" value="1" size="5" />    
   <?php } ?>
   
    </div>
    
    <!-- Add to cart button ---> 
    <div id='add_to_cart' style="display: block;"> 
        <input type="hidden" name="product_id" value="<?php echo $item->product_id; ?>" />
        <input type="hidden" name="filter_category" value="<?php echo $this->filter_category; ?>" />
        <input type="hidden" id="task" name="task" value="" />
        <?php echo JHTML::_( 'form.token' ); ?>
        
        <?php $onclick = "tiendaFormValidation( '".@$this->validation."', 'validationmessage', 'addtocart', document.adminForm );"; ?>
        
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
    
    <?php if (!empty($item->product_check_inventory)) : ?> 
        <div id='available_stock'> 
          <?php echo JText::_("AVAILABLE_STOCK"); ?> <label id="stock"><?php echo (int) $this->availableQuantity->quantity; ?></label> 
        </div>
    <?php endif; ?>
    
    <?php if (!empty($item->product_check_inventory) && !empty($this->invalidQuantity) ) : ?>
        <!-- Not avilable in stock  --->  
        <div id='out_of_stock'> 
          <?php echo JText::_("OUT_OF_STOCK"); ?> 
        </div>
    <?php endif; ?>
    
    </form>
</div>

