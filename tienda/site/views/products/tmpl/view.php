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
            <?php $text = "<img class='addcart' src='".Tienda::getUrl('images')."addcart.png' alt='".JText::_('Add to Cart')."' onclick=\"$onclick\" />"; ?>           
            <?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=carts&task=confirmAdd&tmpl=component", $text, array('update' => false) );  ?>
            </form>
        </div>
    </div>
    
    <?php if ($this->product_description) { ?>
    <div class="reset"></div>
    <div class="productdesc">
        <?php if (TiendaConfig::getInstance()->get('display_product_description_header', '1')) { ?>
            <div class="productdesctitle"><?php echo JText::_("Description"); ?></div>
        <?php } ?>
        <?php echo $this->product_description; ?>
    </div>
    <?php } ?>
    
    <?php // display the files associated with this product ?>
    <?php echo $this->files; ?>
    
    <?php // display the galelry images associated with this product if there is one ?>
    <?php $path = TiendaHelperProduct::getGalleryPath($item->product_id); ?>
    <?php $images = TiendaHelperProduct::getGalleryImages( $path, array( 'exclude'=>$item->product_full_image ) ); ?>
    <?php
    jimport('joomla.filesystem.folder');
    if (!empty($path) && !empty($images))
    {
        ?>
        <div class="reset"></div>
        <div class="productgallery">
        <div class="productgallerytitle"><?php echo JText::_("Images"); ?></div>
        <?php            
        $uri = TiendaHelperProduct::getUriFromPath( $path );
        foreach ($images as $image)
        { 
            // dont display if same as default
            // this should already have been filtered, but just in case...
            if ($image == $item->product_full_image) { continue; }
            ?>
            <div class="subcategory">
                <p class="subcatthumb">
                    <?php echo TiendaUrl::popup( $uri.$image, '<img src="'.$uri."thumbs/".$image.'" />' , array('update' => false, 'img' => true)); ?>
                </p>
            </div>
            <?php 
        } 
        ?>
        <div class="reset"></div>
        </div>
        <?php        		
    }
    ?>
    
    
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

