<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('script', 'tienda_inventory_check.js', 'media/com_tienda/js/');
$state = @$state;
$item = @$row;

Tienda::load('TiendaHelperCategory', 'helpers.category');
Tienda::load('TiendaUrl', 'library.url');
?>  

<div id="tienda" class="products view">
    
    <?php if (TiendaConfig::getInstance()->get('display_tienda_pathway')) : ?>
        <div id='tienda_breadcrumb'>
            <?php echo TiendaHelperCategory::getPathName($cat->category_id, 'links', true); ?>
        </div>
    <?php endif; ?>
    
    <div id="tienda_product">

        <?php if (!empty($onBeforeDisplayProduct)) : ?>
            <div id='onBeforeDisplayProduct_wrapper'>
            <?php echo $onBeforeDisplayProduct; ?>
            </div>
        <?php endif; ?>
    
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
        
        <?php if (TiendaConfig::getInstance()->get('shop_enabled', '1')) : ?>
            <div class="product_buy" id="product_buy">
                <?php if (!empty($product_buy)) { echo $product_buy; } ?>
            </div>
        <?php endif; ?>
        
        <?php // display this product's group ?>
        <?php echo $product_children; ?>
                
        <?php if ($product_description) : ?>
            <div class="reset"></div>
            
            <div id="product_description">
                <?php if (TiendaConfig::getInstance()->get('display_product_description_header', '1')) : ?>
                    <div id="product_description_header" class="tienda_header">
                        <span><?php echo JText::_("Description"); ?></span>
                    </div>
                <?php endif; ?>
                <?php echo $product_description; ?>
            </div>
        <?php endif; ?>
        
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

        <?php // display the files associated with this product ?>
        <?php echo $files; ?>
        
        <?php // display the products required by this product ?>
        <?php echo $product_requirements; ?>

        <?php // display the products associated with this product ?>
        <?php echo $product_relations; ?>

        <?php if (!empty($onAfterDisplayProduct)) : ?>
            <div id='onAfterDisplayProduct_wrapper'>
            <?php echo $onAfterDisplayProduct; ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>
