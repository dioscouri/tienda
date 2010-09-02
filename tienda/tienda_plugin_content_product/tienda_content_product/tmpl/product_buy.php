<?php
defined('_JEXEC') or die('Restricted access');
$item = @$vars->item;
$form = @$vars->form;
$values = @$vars->values; 
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
?>

<div>
    <div id="validationmessage"></div>
    
    <form action="<?php echo JRoute::_( 'index.php?option=com_tienda&controller=products&view=products&id="'.$vars->product_id ); ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

	<?php if(@$vars->params['show_price'] == '1'): ?>
    <!--base price-->
    <span id="product_price" class="product_price">
        <?php            
        // For UE States, we should let the admin choose to show (+19% vat) and (link to the shipping rates)
        if (!empty($vars->show_tax))
        {
            if (!empty($vars->tax))
            {
                if ($vars->show_tax == '2')
                {
                    echo TiendaHelperBase::currency($item->price + $vars->tax);
                }
                    else
                {
                    echo TiendaHelperBase::currency($item->price);
                    echo sprintf( JText::_('INCLUDE_TAX'), TiendaHelperBase::currency($vars->tax));
                }
            }
                else
            {
                echo TiendaHelperBase::currency($item->price);
            }
        }
            else
        {
            echo TiendaHelperBase::currency($item->price);
        }
        
        if (TiendaConfig::getInstance()->get( 'display_prices_with_shipping') && !empty($item->product_ships))
        {
            echo '<br /><a href="'.$vars->shipping_cost_link.'" target="_blank">'.sprintf( JText::_('LINK_TO_SHIPPING_COST'), $vars->shipping_cost_link).'</a>' ;
        }
        ?>
    </span>
    <?php endif; ?>
    
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
        	Tienda::load('TiendaSelect', 'library.select');
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
    
    <?php if (!empty($vars->onDisplayProductAttributeOptions)) : ?>
        <div id='onDisplayProductAttributeOptions_wrapper'>
        <?php echo $vars->onDisplayProductAttributeOptions; ?>
        </div>
    <?php endif; ?>
    
    </div>
    
    <?php if ($vars->params['quantity_restriction'] == '1' ) : ?>
        <input type="hidden" name="product_qty" value="1" size="5" />
    <?php else : ?>
    <!--quantity-->
    <div id='product_quantity_input'>
        <span class="title"><?php echo JText::_( "Quantity" ); ?>:</span>
        <input type="text" name="product_qty" value="1" size="5" />
    </div>
    <?php endif; ?>
    
    <!-- Add to cart button ---> 
    <div id='add_to_cart' style="display: block;"> 
        <input type="hidden" name="product_id" value="<?php echo $item->product_id; ?>" />
        <input type="hidden" name="filter_category" value="<?php echo $vars->filter_category; ?>" />
        <input type="hidden" id="task" name="task" value="" />
        <?php echo JHTML::_( 'form.token' ); ?>
        
        <?php $onclick = "tiendaFormValidation( '".@$vars->validation."', 'validationmessage', 'addtocart', document.adminForm );"; ?>
        
        <?php 
        if (empty($item->product_check_inventory) || (!empty($item->product_check_inventory) && empty($vars->invalidQuantity)) ) :
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
    
    <?php if (!empty($item->product_recurs)) : ?> 
        <div id='product_recurs'> 
            <span class="title"><?php echo JText::_("THIS PRODUCTS CHARGES RECUR"); ?></span>
            <div id="product_recurs_prices"> 
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
        <div id='available_stock'> 
          <?php echo JText::_("AVAILABLE_STOCK"); ?> <label id="stock"><?php echo (int) $vars->availableQuantity->quantity; ?></label> 
        </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (!empty($item->product_check_inventory) && !empty($vars->invalidQuantity) ) : ?>
        <!-- Not avilable in stock  --->  
        <div id='out_of_stock'> 
          <?php echo JText::_("OUT_OF_STOCK"); ?> 
        </div>
    <?php endif; ?>
    
    </form>
</div>

