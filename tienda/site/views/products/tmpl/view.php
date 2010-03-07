<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$item = @$this->row;
?>

<div class='catcrumbs'><?php echo TiendaHelperCategory::getPathName($this->cat->category_id, 'links', true); ?></div>

<div class="productheading">
    <span class="producttitle"><?php echo $item->product_name; ?></span>
    <span class="productmeta">
    <?php
        $sep = '';
        if (!empty($item->product_model)) {
            echo '<b>'.JText::_('Model').":</b> $item->product_model";
            $sep = "&nbsp;&nbsp;";
        }
        if (!empty($item->product_sku)) {
            echo "$sep <b>".JText::_('SKU').":</b> $item->product_sku";
        }
    ?>
    </span>
</div>

<div class="indproduct">
    <div class="productimage">
        <?php echo TiendaUrl::popup( TiendaHelperProduct::getImage($item->product_id, '', '', 'full', true), TiendaHelperProduct::getImage($item->product_id), array('update' => false, 'img' => true)); ?>
        <br />
        <?php
            if (isset($item->product_full_image))
            {
                echo TiendaUrl::popup( TiendaHelperProduct::getImage($item->product_id, '', '', 'full', true), "View Larger", array('update' => false, 'img' => true ));
            }
        ?>
    </div>
    
    <div class="productbuy">
        <div>
            <form action="" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >
            <!--base price-->
            <span class="price"><?php echo TiendaHelperBase::currency($item->price); ?></span>
            
            <!--attribute options-->
            <div id='productattributeoptions' style='text-align: left;'>
            <?php
            $attributes = TiendaHelperProduct::getAttributes( $item->product_id );
            foreach ($attributes as $attribute)
            {
                ?>
                <div id='productattributeoption_<?php echo $attribute->productattribute_id; ?>' style="padding-bottom: 2px;">
                <?php
                echo TiendaSelect::productattributeoptions( $attribute->productattribute_id, '', 'attribute_'.$attribute->productattribute_id );
                ?>
                </div>
                <?php
            }
            ?>
            </div>
            
            <!--quantity-->
            <div id='product_quantity_input'>
                <span style="vertical-align: middle; font-weight: bold;"><?php echo JText::_( "Quantity" ); ?>:</span>
                <input type="text" name="product_qty" value="1" size="5" />    
            </div>
            
            <input type="hidden" name="product_id" value="<?php echo $item->product_id; ?>" size="5" />
            <?php $url = "index.php?option=com_tienda&format=raw&controller=carts&task=addToCart&productid=".$item->product_id; ?>
            <?php $onclick = 'tiendaDoTask(\''.$url.'\', \'tiendaUserShoppingCart\', document.adminForm);'; ?>
            <?php $text = "<img class='addcart' src='media/com_tienda/images/addcart.png' alt='' onclick=\"$onclick\" />"; ?>           
            <?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=carts&task=confirmAdd&tmpl=component", $text, array('update' => false) );  ?>
            </form>
        </div>
    </div>
    
    <div class="reset"></div>
    <div class="productdesc">
       <div class="productdesctitle"><?php echo JText::_("Description"); ?></div>
        <?php echo $this->product_description; ?>
    </div>
    
    <!--
    NOT ENABLED YET
    <div class="reset"></div>
    <div class="productgallery">
       <div class="productgallerytitle"><?php echo JText::_("Images"); ?></div>
        <?php $path = TiendaHelperProduct::getGalleryPath($item->product_id); ?>
        <?php
        jimport('joomla.filesystem.folder');
        if (empty($path))
        {
            echo JText::_( "None" ); 
        }
        else
        {
        	$images = TiendaHelperProduct::getGalleryImages( $path );
        	if (empty($images))
        	{
                echo JText::_( "No Images" );
        	}
        	else
        	{
	            // TODO Fix this - just a quick test 
	            $size = "style='max-width: 100px; max-height: 100px;'";
	            
	            $uri = TiendaHelperProduct::getUriFromPath( $path );
	            foreach ($images as $image)
	            { 
	            ?>
	            <div class="subcategory">
	                <p class="subcatthumb">
	                    <img src="<?php echo $uri.$image; ?>" <?php echo $size; ?>/>
	                </p>
	            </div>
	            
	            <?php } ?>
	            <div class="reset"></div>
	            <?php        		
        	}
        }
        ?>
    </div>
    -->
    
    <!--
    NOT ENABLED YET
    <div class="reset"></div>
    <div class="productdesc">
       <div class="productdesctitle"><?php echo JText::_("Related Products"); ?></div>
       <?php echo JText::_("None"); ?>
    </div>
    -->
    
    <!--
    NOT ENABLED YET
    <div class="reset"></div>
    <div class="productdesc">
       <div class="productdesctitle"><?php echo JText::_("Reviews"); ?></div>
       <?php echo JText::_("None"); ?>
    </div>
    -->
</div>

<div class="reset"></div>

