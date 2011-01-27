<?php
defined('_JEXEC') or die('Restricted access');
$item = @$this->item;
$form = @$this->form;
$values = @$this->values;
$formName = 'adminForm_'.$item->product_id; 
?>

<div>
    <div id="validationmessage_<?php echo $item->product_id; ?>"></div>
    
    <form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" class="adminform" name="<?php echo $formName; ?>" enctype="multipart/form-data" >
    
    <!--base price-->
    <span id="product_price_<?php echo $item->product_id; ?>" class="product_price">
    	<?php  echo TiendaHelperProduct::dispayPriceWithTax($item->price, $item->tax, $this->show_tax); ?>
        <?php            
        // For UE States, we should let the admin choose to show (+19% vat) and (link to the shipping rates)
               
        if (TiendaConfig::getInstance()->get( 'display_prices_with_shipping') && !empty($item->product_ships))
        {
            echo '<br /><a href="'.$this->shipping_cost_link.'" target="_blank">'.sprintf( JText::_('LINK_TO_SHIPPING_COST'), $this->shipping_cost_link).'</a>' ;
        }
        ?>
    </span>

    <?php if (!empty($item->product_listprice_enabled)) : ?>
        <div class="product_listprice">
        <span class="title"><?php echo JText::_( "List Price" ); ?>:</span>
        <del><?php echo TiendaHelperBase::currency($item->product_listprice); ?></del>
        </div>                                
    <?php endif; ?>
    
    <?php if (!empty($this->display_cartbutton)) : ?>
    
    <!--attribute options-->
    <div id='product_attributeoptions_<?php echo $item->product_id; ?>' class="product_attributeoptions">
    <?php
    // Selected attribute options (for child attributes)
    $selected_opts = (!empty($this->selected_opts)) ? json_decode($this->selected_opts) : 0; 
    
    if(!count($selected_opts))
    {
    	$selected_opts = 0;
    }
    
    $attributes = TiendaHelperProduct::getAttributes( $item->product_id, $selected_opts );
    foreach ($attributes as $attribute)
    {
        ?>
        <div class="pao" id='productattributeoption_<?php echo $attribute->productattribute_id; ?>'>
        <?php
        echo "<span>".$attribute->productattribute_name." : </span>";
        
        $key = 'attribute_'.$attribute->productattribute_id;
        $selected = (!empty($values[$key])) ? $values[$key] : ''; 
        
        $attribs = array('class' => 'inputbox', 'size' => '1','onchange'=>"tiendaUpdateAddToCart( 'product_buy_".$item->product_id."', document.".$formName." );");
        echo TiendaSelect::productattributeoptions( $attribute->productattribute_id, $selected, $key, $attribs  );
    
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
    
    </div>
    
    <div id='product_quantity_input_<?php echo $item->product_id; ?>' class="product_quantity_input">
        <?php if ($item->quantity_restriction && $item->quantity_min == $item->quantity_max) { ?>
            <input type="hidden" name="product_qty" value="<?php echo $item->quantity_min; ?>" />
        <?php } else { ?>
        <span class="title"><?php echo JText::_( "Quantity" ); ?>:</span>
        <input type="text" name="product_qty" value="<?php echo $item->_product_quantity; ?>" size="5" />
        <?php } ?>
    </div>
 
    <!-- Add to cart button --> 
    <div id='add_to_cart_<?php echo $item->product_id; ?>' class="add_to_cart" style="display: block;"> 
        <input type="hidden" name="product_id" value="<?php echo $item->product_id; ?>" />
        <input type="hidden" name="filter_category" value="<?php echo $this->filter_category; ?>" />
        <input type="hidden" id="task" name="task" value="" />
        <?php echo JHTML::_( 'form.token' ); ?>
        
        <?php $onclick = "tiendaFormValidation( '".@$this->validation."', 'validationmessage_".$item->product_id."', 'addtocart', document.".$formName." );"; ?>
        
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
                	// Search for localized version of the image
                	Tienda::load('TiendaHelperImage', 'helpers.image');
                	$image = TiendaHelperImage::getLocalizedName("addcart.png", Tienda::getPath('images'));
                    ?> 
                    <img class='addcart' src='<?php echo Tienda::getUrl('images').$image; ?>' alt='<?php echo JText::_('Add to Cart'); ?>' onclick="<?php echo $onclick; ?>" />
                    <?php
                    break;
            }
        endif; 
        ?>
    </div>
    
    <?php if (!empty($item->product_recurs)) : ?> 
        <div id='product_recurs_<?php echo $item->product_id; ?>' class="product_recurs"> 
            <span class="title"><?php echo JText::_("THIS PRODUCTS CHARGES RECUR"); ?></span>
            <div id="product_recurs_prices_<?php echo $item->product_id; ?>" class="product_recurs_prices"> 
            <?php echo JText::_( "RECURRING PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_price); ?>
            (<?php echo $item->recurring_payments . " " . JText::_( "PAYMENTS" ); ?>, <?php echo $item->recurring_period_interval." ". JText::_( "$item->recurring_period_unit PERIOD UNIT" )." ".JText::_( "PERIODS" ); ?>) 
            <?php if ($item->recurring_trial) : ?>
                <br/>
                <?php echo JText::_( "TRIAL PERIOD PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
                (<?php echo "1 " . JText::_( "PAYMENT" ); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_( "$item->recurring_trial_period_unit PERIOD UNIT" )." ".JText::_( "PERIOD" ); ?>)
            <?php endif; ?> 
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($item->product_check_inventory)) : ?>
        <?php if (TiendaConfig::getInstance()->get('display_product_quantity', '1')) : ?> 
        <div id='available_stock_<?php echo $item->product_id; ?>' class="available_stock"> 
          <?php echo JText::_("AVAILABLE_STOCK"); ?> <label id="stock_<?php echo $item->product_id; ?>"><?php echo (int) $this->availableQuantity->quantity; ?></label> 
        </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (!empty($item->product_check_inventory) && !empty($this->invalidQuantity) ) : ?>
        <!-- Not avilable in stock  --->  
        <div id='out_of_stock_<?php echo $item->product_id; ?>' class="out_of_stock"> 
          <?php echo JText::_("OUT_OF_STOCK"); ?> 
        </div>
    <?php endif; ?>
    
    <?php endif; ?>
    
    </form>
</div>

