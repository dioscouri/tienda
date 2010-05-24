<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$item = @$this->row;
$inventoryEnabled=@$this->inventoryEnabled;

$str = '';
if($item->product_check_inventory==1){
$inventryArray = @$this->invetoryList;
foreach($inventryArray as $k=>$v)
{
	$str .= "$k=>$v&&";
	
}
JHTML::_('script', 'tienda_inventory_check.js', 'media/com_tienda/js/');

}
?>
<script>
// seting the java script variables with inventry array from php variables
strignOfOptions = "<?php echo $str ?>";
</script>

<div id="tienda" class="products view">
    
    <div id='tienda_breadcrumb'>
        <?php echo TiendaHelperCategory::getPathName($this->cat->category_id, 'links', true); ?>
    </div>
    
    <div id="tienda_product">
        <div id='tienda_product_header'>
            <span class="product_name">
                <?php echo $item->product_name; ?>
            </span>
      
            <div class="product_numbers">
                <?php if (!empty($item->product_model)) : ?>
                    <span class="model">
                        <span class="title"><?php echo JText::_('Model'); ?>:</span> 
                        <?php echo $item->product_model; ?>
                    </span>
                <?php endif; ?>
                
                <?php if (!empty($item->product_sku)) : ?>
                    <span class="sku">
                        <span class="title"><?php echo JText::_('SKU'); ?>:</span> 
                        <?php echo $item->product_sku; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="product_image">
            <?php echo TiendaUrl::popup( TiendaHelperProduct::getImage($item->product_id, '', '', 'full', true), TiendaHelperProduct::getImage($item->product_id), array('update' => false, 'img' => true)); ?>
            <div>
            <?php
                if (isset($item->product_full_image))
                {
                    echo TiendaUrl::popup( TiendaHelperProduct::getImage($item->product_id, '', '', 'full', true), "View Larger", array('update' => false, 'img' => true ));
                }
            ?>
            </div>
        </div>
        
        <div class="product_buy">
            <div>
                <form action="" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >
                <!--base price-->
                <span class="product_price"><?php echo TiendaHelperBase::currency($item->price); ?></span>
                
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
                    
                    if($item->product_check_inventory==1) {
                    	$event="ONCHANGE";
                        $action="checkStock();";
                        $attribs = array('class' => 'inputbox', 'size' => '1','ONCHANGE'=>$action);
                          echo TiendaSelect::productattributeoptions( $attribute->productattribute_id, '', 'attribute_'.$attribute->productattribute_id, $attribs  );
                    }else {
  						echo TiendaSelect::productattributeoptions( $attribute->productattribute_id, '', 'attribute_'.$attribute->productattribute_id);
                    }	
                    
                  
                    ?>
                    </div>
                    <?php
                }
                ?>
                </div>
                
                <!--quantity-->
                <div id='product_quantity_input'>
                    <span class="title"><?php echo JText::_( "Quantity" ); ?>:</span>
                <?php if($item->product_check_inventory==1) {  ?>   
                    <input type="text" name="product_qty" value="1" size="5" onkeyup="checkStock()" />  
               <?php } else {?>
                    <input type="text" name="product_qty" value="1" size="5"  />    
               <?php } ?>
               
                </div>
                
                
                <!-- Add to cart button ---> 
               <div id='add_to_cart' style="display: block";> 
                <input type="hidden" name="product_id" value="<?php echo $item->product_id; ?>" size="5" />
                <?php $url = "index.php?option=com_tienda&format=raw&view=carts&task=addToCart&product_id=".$item->product_id; ?>
                <?php $onclick = 'tiendaDoTask(\''.$url.'\', \'tiendaUserShoppingCart\', document.adminForm); tiendaPause(500);'; ?>
                <?php $text = "<img class='addcart' src='".Tienda::getUrl('images')."addcart.png' alt='".JText::_('Add to Cart')."' onclick=\"$onclick\" />"; ?>          
                <?php $lightbox_attribs = array(); $lightbox['update'] = false; if ($lightbox_width = TiendaConfig::getInstance()->get( 'lightbox_width' )) { $lightbox_attribs['width'] = $lightbox_width; }; ?>
                <?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=carts&task=confirmAdd&tmpl=component", $text, $lightbox_attribs );  ?>
               </div>
               
               
          
             </form>
            
            </div>
        </div>
         <!-- Not avilable in stock  --->  
               <div id='add_to_cart_deactive' class="add_to_cart_deactive" style="display: none;"> 
                  <span><?php echo JText::_("out_of_stock"); ?></span>
                  <span><?php echo JText::_("available_stock"); ?><label id="stock"></label></span> 
               </div>
               
               
        <!-- Not valid quantity  --->  
               <div id='invalid_quantity' class="add_to_cart_deactive" style="display: none;"> 
                  <span><?php echo JText::_("invalid_quantity"); ?></span>
               </div>
                
                <?php if ($this->product_description) : ?>
            <div class="reset"></div>
            
            <div id="product_description">
                <?php if (TiendaConfig::getInstance()->get('display_product_description_header', '1')) : ?>
                    <div id="product_description_header" class="tienda_header">
                        <span><?php echo JText::_("Description"); ?></span>
                    </div>
                <?php endif; ?>
                <?php echo $this->product_description; ?>
            </div>
        <?php endif; ?>
        
        <?php // display the files associated with this product ?>
        <?php echo $this->files; ?>
        
        <?php // display the gallery images associated with this product if there is one ?>
        <?php $path = TiendaHelperProduct::getGalleryPath($item->product_id); ?>
        <?php $images = TiendaHelperProduct::getGalleryImages( $path, array( 'exclude'=>$item->product_full_image ) ); ?>
        <?php
        jimport('joomla.filesystem.folder');
        if (!empty($path) && !empty($images))
        {
            ?>
            
            <div class="reset"></div>
            <div class="product_gallery">
                <div id="product_gallery_header" class="tienda_header">
                    <span><?php echo JText::_("Images"); ?></span>
                </div>
                <?php            
                $uri = TiendaHelperProduct::getUriFromPath( $path );
                foreach ($images as $image)
                {
                    ?>
                    <div class="subcategory">
                        <div class="subcategory_thumb">
                            <?php echo TiendaUrl::popup( $uri.$image, '<img src="'.$uri."thumbs/".$image.'" />' , array('update' => false, 'img' => true)); ?>
                        </div>
                    </div>
                    <?php 
                } 
                ?>
                <div class="reset"></div>
            </div>
            <?php        		
        }
        ?>
        
        <div class="reset"></div>
    </div>
</div>