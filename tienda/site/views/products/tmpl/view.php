<?php defined( '_JEXEC' ) or die( 'Restricted access' );
JHTML::_( 'script', 'tienda.js', 'media/com_tienda/js/' );
JHTML::_( 'script', 'tienda_inventory_check.js', 'media/com_tienda/js/' );
$state = @$this->state;
$item = @$this->row;

$product_image = TiendaHelperProduct::getImage($item->product_id, '', '', 'full', true, false, array(), true );
$product_image_thumb = TiendaHelperProduct::getImage($item->product_id, '', $item->product_name, 'thumb', false, false, array(), true );
?>  

<div id="tienda" class="dsc-wrap products view product-<?php echo $item->product_id; ?> <?php echo $item->product_classes; ?>">
    
    <?php if ( $this->defines->get( 'display_tienda_pathway' ) ) : ?>
        <div id='tienda_breadcrumb'>
            <?php echo TiendaHelperCategory::getPathName( $this->cat->category_id, 'links', true ); ?>
        </div>
    <?php endif; ?>
    
    <?php if ( $this->defines->get( 'enable_product_detail_nav' ) && (!empty($this->surrounding['prev']) || !empty($this->surrounding['next'])) ) { ?>
        <div class="pagination">
            <ul id='tienda_product_navigation'>
                <?php if ( !empty( $this->surrounding['prev'] ) ) { ?>
                    <li class='prev'>
                        <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&task=view&id=" . $this->surrounding['prev'] . "&Itemid=" . $this->getModel()->getItemid( $this->surrounding['prev'] ) ); ?>">
                            <?php echo JText::_( "COM_TIENDA_PREVIOUS" ); ?>
                        </a>
                    </li>
                <?php } ?>
                <?php if ( !empty( $this->surrounding['next'] ) ) { ?>
                    <li class='next'>
                        <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&task=view&id=" . $this->surrounding['next'] . "&Itemid=" . $this->getModel()->getItemid( $this->surrounding['next'] ) ); ?>">
                            <?php echo JText::_( "COM_TIENDA_NEXT" ); ?>
                        </a>
                    </li>
                <?php } ?>            
            </ul>
        </div>
    <?php } ?>
    
    <div id="tienda_product" class="dsc-wrap">

        <?php if ( !empty( $this->onBeforeDisplayProduct ) ) : ?>
            <div id='onBeforeDisplayProduct_wrapper'>
            <?php echo $this->onBeforeDisplayProduct; ?>
            </div>
        <?php endif; ?>
                  
        <div id='tienda_product_header' class="dsc-wrap">
            <h2 class="product_name">
                <?php echo htmlspecialchars_decode( $item->product_name ); ?>
            </h2>
            
            <?php echo TiendaHelperProduct::getProductShareButtons( $this, $item->product_id ); ?>
                        
            <?php if ( $this->defines->get( 'product_review_enable', '0' ) ) { ?>
            <div class="dsc-wrap product_rating">
                <?php echo TiendaHelperProduct::getRatingImage( $item->product_rating, $this ); ?>
                <?php if ( !empty( $item->product_comments ) ) : ?>
                <span class="product_comments_count">(<?php echo $item->product_comments; ?>)</span>
                <?php endif; ?>
            </div>
            <?php } ?>
            
            <?php if ( !empty( $item->product_model ) || !empty( $item->product_sku ) ) : ?>
            <div class="dsc-wrap product_numbers">
                <?php if ( !empty( $item->product_model ) ) : ?>
                    <span class="model">
                        <span class="title"><?php echo JText::_('COM_TIENDA_MODEL'); ?>:</span> 
                        <?php echo $item->product_model; ?>
                    </span>
                <?php endif; ?>
                
                <?php if ( !empty( $item->product_sku ) ) : ?>
                    <span class="sku">
                        <span class="title"><?php echo JText::_('COM_TIENDA_SKU'); ?>:</span> 
                        <?php echo $item->product_sku; ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
                
        <div id="product_image" class="dsc-wrap product_image">
            <?php echo TiendaUrl::popup( $product_image, $product_image_thumb, array( 'update' => false, 'img' => true ) ); ?>
            <div>
	            <?php
				if ( isset( $item->product_full_image ) )
				{
					echo TiendaUrl::popup( $product_image, JText::_('COM_TIENDA_VIEW_LARGER'),
							array(
								'update' => false, 'img' => true
							) );
				}
				?>
            </div>
        </div>
        
        <?php if ( $this->defines->get( 'shop_enabled', '1' ) ) : ?>
            <div class="dsc-wrap product_buy" id="product_buy_<?php echo $item->product_id; ?>">
                <?php echo TiendaHelperProduct::getCartButton( $item->product_id ); ?>
            </div>              
        <?php endif; ?>
        
        <?php if ( $this->defines->get( 'ask_question_enable', '1' ) ) : ?>
        <div id="product_questions" class="dsc-wrap dsc-clear">
            <?php
				$uri = JFactory::getURI( );
				$return_link = base64_encode( $uri->toString( ) );
				$asklink = "index.php?option=com_tienda&view=products&task=askquestion&id={$item->product_id}&return=" . $return_link;
				
				if ( $this->defines->get( 'ask_question_modal', '1' ) )
				{
					$height = $this->defines->get( 'ask_question_showcaptcha', '1' ) ? '570' : '440';
					$asktxt = TiendaUrl::popup( "{$asklink}.&tmpl=component", JText::_('COM_TIENDA_ASK_A_QUESTION_ABOUT_THIS_PRODUCT'),
							array(
								'width' => '490', 'height' => "{$height}"
							) );
				}
				else
				{
					$asktxt = "<a href='{$asklink}'>";
					$asktxt .= JText::_('COM_TIENDA_ASK_A_QUESTION_ABOUT_THIS_PRODUCT');
					$asktxt .= "</a>";
				}
			?>
            [<?php echo $asktxt; ?>]
        </div>   
        <?php endif; ?>
        
        <?php // display this product's group ?>
        <?php echo $this->product_children; ?>
                
        <?php if ( $this->product_description ) : ?>
            <div id="product_description" class="dsc-wrap">
                <?php if ( $this->defines->get( 'display_product_description_header', '1' ) ) : ?>
                    <div id="product_description_header" class="tienda_header dsc-wrap">
                        <span><?php echo JText::_('COM_TIENDA_DESCRIPTION'); ?></span>
                    </div>
                <?php endif; ?>
                <?php echo $this->product_description; ?>
            </div>
        <?php endif; ?>

		<?php echo TiendaHelperProduct::getGalleryLayout( $this, $item->product_id, $item->product_name, $item->product_full_image ); ?>            

        <?php // display the files associated with this product ?>
        <?php echo $this->files; ?>
        
        <?php // display the products required by this product ?>
        <?php echo $this->product_requirements; ?>

        <?php // display the products associated with this product ?>
		    <?php if ( $this->defines->get( 'display_relateditems' ) ) : ?>
    	    <?php echo $this->product_relations; ?>
				<?php endif; ?>

        <?php if ( !empty( $this->onAfterDisplayProduct ) ) : ?>
            <div id='onAfterDisplayProduct_wrapper' class="dsc-wrap">
            <?php echo $this->onAfterDisplayProduct; ?>
            </div>
        <?php endif; ?>
        
        <div class="product_review dsc-wrap" id="product_review">
            <?php if ( !empty( $this->product_comments ) )
			{
				echo $this->product_comments;
			} ?>
        </div>
        
    </div>
</div>
