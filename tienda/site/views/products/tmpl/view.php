<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('script', 'tienda_inventory_check.js', 'media/com_tienda/js/');
$state = @$this->state;
$item = @$this->row;
?>  

<div id="tienda" class="products view">
    
    <?php if (TiendaConfig::getInstance()->get('display_tienda_pathway')) : ?>
        <div id='tienda_breadcrumb'>
            <?php echo TiendaHelperCategory::getPathName($this->cat->category_id, 'links', true); ?>
        </div>
    <?php endif; ?>
    
    <div id="tienda_product">

        <?php if (!empty($this->onBeforeDisplayProduct)) : ?>
            <div id='onBeforeDisplayProduct_wrapper'>
            <?php echo $this->onBeforeDisplayProduct; ?>
            </div>
        <?php endif; ?>
                  
        <div id='tienda_product_header'>
            <?php if (TiendaConfig::getInstance()->get('display_facebook_like', '1') || TiendaConfig::getInstance()->get('display_tweet', '1')) : ?>       
            <div class="product_like">
                <?php if (TiendaConfig::getInstance()->get('display_facebook_like', '1')) : ?>
                <div class="product_facebook_like">
                    <script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
                    <fb:like show_faces="false" width="375"></fb:like> 
                </div>  
                <?php endif;?>
            
                <?php if (TiendaConfig::getInstance()->get('display_tweet', '1')) : ?>
                <div class="product_tweet">
                      <a href="http://twitter.com/share" class="twitter-share-button" data-text="<?php echo TiendaConfig::getInstance()->get('display_tweet_message', 'Check this out!'); ?>" data-count="horizontal">Tweet</a>
                      <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
                </div>
                <?php endif;?>
                <div class="reset"></div>
            </div> 
            <?php endif;?>
        
            <span class="product_name">
                <?php echo htmlspecialchars_decode( $item->product_name ); ?>
            </span>
            <?php if ( TiendaConfig::getInstance()->get('product_review_enable', '0') ) { ?>
            <div class="product_rating">
                <?php echo TiendaHelperProduct::getRatingImage( $item->product_rating ); ?>
                <?php if (!empty($item->product_comments)) : ?>
                <span class="product_comments_count">(<?php echo $item->product_comments; ?>)</span>
                <?php endif; ?>
            </div>
            <?php } ?>
            
            <?php if (!empty($item->product_model) || !empty($item->product_sku)) : ?>
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
            <?php endif; ?>
            
            <div class="reset"></div>                        
        </div>
        
        <div class="reset"></div>
        
        <div class="product_image">
            <?php echo TiendaUrl::popup( TiendaHelperProduct::getImage($item->product_id, '', '', 'full', true), TiendaHelperProduct::getImage($item->product_id, '', $item->product_name), array('update' => false, 'img' => true)); ?>
            <div>
	            <?php
	                if (isset($item->product_full_image))
	                {
	                    echo TiendaUrl::popup( TiendaHelperProduct::getImage($item->product_id, '', '', 'full', true), JText::_( 'View Larger' ), array('update' => false, 'img' => true ));
	                }
	            ?>
            </div>
        </div>
        
        <?php if (TiendaConfig::getInstance()->get('shop_enabled', '1')) : ?>
            <div class="product_buy" id="product_buy_<?php echo $item->product_id; ?>">
                <?php if (!empty($this->product_buy)) { echo $this->product_buy; } ?>
                 <?php if (TiendaConfig::getInstance()->get('ask_question_enable', '1')) : ?>
	            <span style="font-size: 12px;">
	            	<?php 
	            		$uri = JFactory::getURI();	 
	            		$return_link = base64_encode($uri->toString());
	            		$asklink = "index.php?option=com_tienda&view=products&task=askquestion&id={$item->product_id}&return=".$return_link;
	            		
	            		if(TiendaConfig::getInstance()->get('ask_question_modal', '1'))
	            		{
	            			$height = TiendaConfig::getInstance()->get('ask_question_showcaptcha', '1') ? '570' : '440';
	            			$asktxt = TiendaUrl::popup( "{$asklink}.&tmpl=component", JText::_("Ask a question about this product"), array('width' => '490', 'height' => "{$height}") ); 
	            		}
	            		else 
	            		{
	            			$asktxt = "<a href='{$asklink}'>";
	            			$asktxt .= JText::_("Ask a question about this product");
	            			$asktxt .= "</a>";	            			
	            		}
	            	?>
	            	 [<?php echo $asktxt;?>]
	            </span>   
            <?php endif;?> 
            </div>                   
        <?php endif; ?>
        
        <?php // display this product's group ?>
        <?php echo $this->product_children; ?>
                
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
                    	<div class="product_gallery_thumb">
                            <?php echo TiendaUrl::popup( $uri.$image, '<img src="'.$uri."thumbs/".$image.'" alt="'.$item->product_name.'" />' , array('update' => false, 'img' => true)); ?>
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
        <?php echo $this->files; ?>
        
        <?php // display the products required by this product ?>
        <?php echo $this->product_requirements; ?>

        <?php // display the products associated with this product ?>
        <?php echo $this->product_relations; ?>

        <?php if (!empty($this->onAfterDisplayProduct)) : ?>
            <div id='onAfterDisplayProduct_wrapper'>
            <?php echo $this->onAfterDisplayProduct; ?>
            </div>
        <?php endif; ?>
        
        <div class="product_review" id="product_review">
            <?php if (!empty($this->product_comments)) { echo $this->product_comments; } ?>
        </div>
        
    </div>
</div>
