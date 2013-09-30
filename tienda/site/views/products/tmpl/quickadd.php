<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$item = @$this->row;

 // TODO This tmpl will eventually be used for quickly adding an item to your cart
 // directly from the product list -- this tmpl will load in a lightbox
?>

<div class="productheading">
    <span class="producttitle"><?php echo $item->product_name; ?></span>
    <span class="productmeta">
    <?php
        $sep = '';
        if (!empty($item->product_model)) {
            echo '<b>'.JText::_('COM_TIENDA_MODEL').":</b> $item->product_model";
            $sep = "&nbsp;&nbsp;";
        }
        if (!empty($item->product_sku)) {
            echo "$sep <b>".JText::_('COM_TIENDA_SKU').":</b> $item->product_sku";
        }
    ?>
    </span>
</div>

<div class="indproduct">
    <div class="productimage">
        <?php echo TiendaHelperProduct::getImage($item->product_id); ?>
    </div>
    
    <div class="productbuy">
        <div>
            <span class="price"><?php echo TiendaHelperBase::currency($item->price); ?></span><br />
            <?php $url = "index.php?option=com_tienda&format=raw&controller=carts&task=addToCart&productid=".$item->product_id; ?>
            <?php $onclick = 'tiendaDoTask(\''.$url.'\', \'tiendaUserShoppingCart\', \'\');' ?>
            <img class="addcart" src="media/com_tienda/images/addcart.png" alt="" onclick="<?php echo $onclick; ?>" />
        </div>
    </div>
    
    <div class="reset"></div>
    <div class="productdesc">
       <div class="productdesctitle"><?php echo JText::_('COM_TIENDA_DESCRIPTION'); ?></div>
        <?php echo $item->product_description; ?>
    </div>
</div>

<div class="reset"></div>
