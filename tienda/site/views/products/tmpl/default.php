<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$items = @$this->items;
$citems = @$this->citems;
Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
$config = Tienda::getInstance();
$product_compare = $config->get('enable_product_compare', '1');
$plugins_short_desc = $config->get( 'content_plugins_product_desc', '0' );

$js_strings = array( 'COM_TIENDA_ADDING_PRODUCT_FOR_COMPARISON', 'COM_TIENDA_REMOVING_PRODUCT' );
TiendaHelperBase::addJsTranslationStrings( $js_strings );
?>
<div id="tienda" class="products default">

    <?php if ($this->level > 1 && $config->get('display_tienda_pathway')) : ?>
        <div id='tienda_breadcrumb'>
            <?php echo TiendaHelperCategory::getPathName($this->cat->category_id, 'links'); ?>
        </div>
    <?php endif; ?>
    <?php if( $product_compare ):?>
    <?php $compareitems = @$this->compareitems;?>
	<div id="validationmessage"></div>
	<?php endif;?>
    <?php if (!empty($this->pricefilter_applied)) : ?>
        <div id='tienda_pricefilter'>
            <b><?php echo JText::_('COM_TIENDA_DISPLAYING_PRICE_RANGE') . ": "; ?></b>
            <?php echo $this->filterprice_from .  " - " . $this->filterprice_to; ?>
            <a href="<?php echo JRoute::_( $this->remove_pricefilter_url ); ?>"><?php echo JText::_('COM_TIENDA_REMOVE_FILTER') ?></a>
        </div>
    <?php endif; ?>

    <div id="tienda_categories">    
        <div id='tienda_category_header'>
            <?php if (isset($state->category_name)) : ?>
                <?php if (!empty($this->cat->category_full_image) || $config->get('use_default_category_image', '1')) : ?>
                    <img src="<?php echo TiendaHelperCategory::getImage($this->cat->category_id, '', '', '', true); ?>" alt="" class="category image" />
                <?php endif; ?>
            <?php endif; ?>

         		<?php if( $this->cat->display_name_category ) : ?>
            <span><?php echo @$this->title; ?></span>
            <?php endif; ?>
            <div class='category_description'><?php echo $this->cat->category_description; ?></div>
        </div>
        
        <?php if (!empty($citems)) : ?>
            <div id="tienda_subcategories">
                <?php if ($this->level > 1) { echo '<h3>'.JText::_('COM_TIENDA_SUBCATEGORIES').'</h3>'; } ?>
                <?php
                $i = 0;
                $subcategories_per_line = $config->get('subcategories_per_line', '5'); 
                foreach ($citems as $citem) : 
                ?>
                    <div class="subcategory">
                    		<?php if( $citem->display_name_subcategory ) : ?>
                        <div class="subcategory_name">
                            <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$citem->category_id.$citem->slug."&Itemid=".$citem->itemid ); ?>">
                            <?php echo $citem->category_name; ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($citem->category_full_image) || $config->get('use_default_category_image', '1')) : ?>
                            <div class="subcategory_thumb">
                                <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$citem->category_id.$citem->slug."&Itemid=".$citem->itemid ); ?>">
                                <?php echo TiendaHelperCategory::getImage($citem->category_id); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    if ( ($i+1) >= $subcategories_per_line) 
                    { 
                        ?>
                        <div class="reset"></div>
                        <?php $i = 0; 
                    }
                        else 
                    {
                        $i++;
                    }
                endforeach; 
                ?>
                <div class="reset"></div>
            </div>
        <?php endif; ?>
    
    </div>
    <?php if (!empty($items)) : ?>
    
     <?php if($config->get('display_sort_by', '1')) :?>
      <form action="<?php echo JRoute::_("&limitstart=".@$state->limitstart )?>" method="post" name="adminForm_sort" enctype="multipart/form-data">        
     	<div class="tienda_sortby" style="margin: 20px 0; text-align:right;">
    	<?php Tienda::load('TiendaSelect', 'libray.select');?>
    	<span class="sort_by_label" style="font-size: 1.15em;">
    	<?php echo JText::_('COM_TIENDA_SORT_BY');?>
    	</span>
    	<?php echo TiendaSelect::productsortby($state->filter_sortby, 'filter_sortby', array('onchange' => 'document.adminForm_sort.submit();'), 'filter_sortby', true, JText::_('COM_TIENDA_DEFAULT_ORDER'));?>
    	<span>
    		<?php 
    			if(strtolower($state->filter_dir) == 'asc')
    			{
    				$dir = 'desc';
    				$img_dir = 'arrow_down.png';
    			}
    			else
    			{
    				$dir = 'asc';
    				$img_dir = 'arrow_up.png';
    			}
    		?>    		
    		<a href="<?php echo JRoute::_("&limitstart=".$state->limitstart."&filter_sortby=".$state->filter_sortby."&filter_dir=".$dir);?>">
    			<img src="<?php echo Tienda::getURL('images').$img_dir?>" alt="filter_direction"/>
    		</a>
    	</span>
    	</div>
        <?php echo $this->form['validate']; ?>
    </form>   
    <?php endif;?>    
      
        <div id="tienda_products">
            <?php foreach ($items as $item) : ?>
            <div class="product_item">
                <?php if (!empty($item->product_full_image) || ($product_compare && $item->product_parameters->get('show_product_compare', '1') ) ) : ?>
                <div class="product_thumb">
                		<?php if( !empty($item->product_full_image) ): ?>
                    <div class="product_listimage">
                        <a href="<?php echo JRoute::_( $item->link."&filter_category=".$this->cat->category_id ."&Itemid=".$item->itemid ); ?>">
                            <?php echo TiendaHelperProduct::getImage($item->product_id, '', $item->product_name); ?>
                        </a>
                    </div>
                    <?php 
                    	endif;
                    	if( $product_compare && $item->product_parameters->get('show_product_compare', '1')):?>
                    <div id="tiendaProductCompare">
	                	<input <?php echo in_array($item->product_id,$compareitems) ? 'checked' : '';?> type="checkbox" onclick="tiendaAddProductToCompare(<?php echo $item->product_id;?>, 'tiendaComparedProducts', this, true);">
	               	 	<a href="<?php echo JRoute::_('index.php?option=com_tienda&view=productcompare');?>">
	               	 		<?php echo JText::_('COM_TIENDA_COMPARE')?>
	               	 		<span class="arrow" >»</span>  
	               	 	</a>             	 	              
                	</div>
                	<?php endif;?>
                    <div class="reset"></div>
                </div>
                <?php endif; ?>

                <div id="product_buy_<?php echo $item->product_id; ?>" class="product_buy">
                    <?php echo TiendaHelperProduct::getCartButton( $item->product_id ); ?>
                </div>
               
                <div class="product_info">
                    <div class="product_name">
                        <span>
                            <a href="<?php echo JRoute::_($item->link."&filter_category=".$this->cat->category_id."&Itemid=".$item->itemid ); ?>">
                            <?php echo htmlspecialchars_decode( $item->product_name ); ?>
                            </a>
                        </span>
                    </div>
                     <?php if ( $config->get('product_review_enable', '0') ) { ?>
                    <div class="product_rating">
                       <?php echo TiendaHelperProduct::getRatingImage( $this, $item->product_rating ); ?>
                       <?php if (!empty($item->product_comments)) : ?>
                       <span class="product_comments_count">(<?php echo $item->product_comments; ?>)</span>
                       <?php endif; ?>
                    </div>
                    <?php } ?>
                    <?php if (!empty($item->product_model) || !empty($item->product_sku)) { ?>
                        <div class="product_numbers">
                            <span class="model">
                                <?php if (!empty($item->product_model)) : ?>
                                    <span class="title"><?php echo JText::_('COM_TIENDA_MODEL'); ?>:</span> 
                                    <?php echo $item->product_model; ?>
                                <?php endif; ?>
                            </span>
                            <span class="sku">
                                <?php if (!empty($item->product_sku)) : ?>
                                    <span class="title"><?php echo JText::_('COM_TIENDA_SKU'); ?>:</span> 
                                    <?php echo $item->product_sku; ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php } ?>

                    <div class="product_minidesc">
                    <?php
                        if (!empty($item->product_description_short))
                        {
                        	$product_desc = $item->product_description_short;
                        }
                            else
                        {                  
                            $str = wordwrap($item->product_description, 200, '`|+');
                            $wrap_pos = strpos($str, '`|+');
                            if ($wrap_pos !== false) {
                                $product_desc = substr($str, 0, $wrap_pos).'...';
                            } else {
                                $product_desc = $str;
                            }    
                        }
                        
                        if( $plugins_short_desc )
                        	echo JHTML::_('content.prepare', $product_desc);
                        else
                           echo $product_desc;
                    ?>
                    </div>
                    <div class="reset"></div>
                </div>
                <div class="reset"></div>
            </div>
            <div class="reset"></div>
            <?php endforeach; ?>
        </div>
        
        <form action="<?php echo JRoute::_( @$form['action']."&limitstart=".@$state->limitstart )?>" method="post" name="adminForm" enctype="multipart/form-data">        
        <div id="products_footer">
            <div id="results_counter" class="pagination"><?php echo @$this->pagination->getResultsCounter(); ?></div>
            <?php echo @$this->pagination->getListFooter(); ?>
        </div>
        <?php echo $this->form['validate']; ?>
        </form>

    <?php endif; ?>
    
</div>
