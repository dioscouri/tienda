<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$items = @$this->items;
$citems = @$this->citems;
?>

<div id="tienda" class="products default">

    <?php if ($this->level > 1 && TiendaConfig::getInstance()->get('display_tienda_pathway')) : ?>
        <div id='tienda_breadcrumb'>
            <?php echo TiendaHelperCategory::getPathName($this->cat->category_id, 'links'); ?>
        </div>
    <?php endif; ?>

    <div id="tienda_categories">    
        <div id='tienda_category_header'>
            <?php if (isset($state->category_name)) : ?>
                <?php if (!empty($this->cat->category_full_image) || TiendaConfig::getInstance()->get('use_default_category_image', '1')) : ?>
                    <img src="<?php echo TiendaHelperCategory::getImage($this->cat->category_id, '', '', '', true); ?>" alt="" class="category image" />
                <?php endif; ?>
            <?php endif; ?>

            <span><?php echo @$this->title; ?></span>
            <div class='category_description'><?php echo $this->cat->category_description; ?></div>
        </div>
        
        <?php if (!empty($citems)) : ?>
            <div id="tienda_subcategories">
                <?php if ($this->level > 1) { echo '<h3>'.JText::_('Subcategories').'</h3>'; } ?>
                <?php foreach ($citems as $citem) : ?>
                    <div class="subcategory">
                        <?php if (!empty($citem->category_full_image) || TiendaConfig::getInstance()->get('use_default_category_image', '1')) : ?>
                            <div class="subcategory_thumb">
                                <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$citem->category_id.$citem->slug."&Itemid=".$citem->itemid ); ?>">
                                <?php echo TiendaHelperCategory::getImage($citem->category_id); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="subcategory_name">
                            <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$citem->category_id.$citem->slug."&Itemid=".$citem->itemid ); ?>">
                            <?php echo $citem->category_name; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="reset"></div>
            </div>
        <?php endif; ?>
    
    </div>
    
    <?php if (!empty($items)) : ?>
    <form action="<?php echo JRoute::_( @$form['action']."&limitstart=".@$state->limitstart )?>" method="post" name="adminForm" enctype="multipart/form-data">
    
        <div id="tienda_products">
            <?php foreach ($items as $item) : ?>
            <div class="product_item">
                <div class="product_thumb">
                    <div class="product_buy">
                        <a href="<?php echo JRoute::_( $item->link."&filter_category=".$this->cat->category_id ); ?>">
                            <?php echo TiendaHelperProduct::getImage($item->product_id); ?>
                        </a>
                        
                        <div class="product_price">
                            <?php echo TiendaHelperBase::currency($item->price); 
                            
                            // For UE States, we should let the admin choose to show (+19% vat) and (link to the shipping rates)
                            $config = TiendaConfig::getInstance();
                            $show_tax = $config->get('display_prices_with_tax');
                            
                            $article_link = $config->get('article_shipping', '');
                            $shipping_cost_link = JRoute::_('index.php?option=com_content&view=article&id='.$article_link);
                            
					        if ($show_tax)
					        {
                                Tienda::load('TiendaHelperUser', 'helpers.user');
                                $geozones = TiendaHelperUser::getGeoZones( JFactory::getUser()->id );
                                $taxtotal = TiendaHelperProduct::getTaxTotal($item->product_id, $geozones);
                                $tax = $taxtotal->tax_total;
                                if (!empty($tax))
                                {
                                    echo sprintf( JText::_('INCLUDE_TAX'), TiendaHelperBase::currency($tax));    
                                }
                                    else 
                                {
                                    echo JText::_('PLUS_TAX');
                                }
                                
					        }

					        if (TiendaConfig::getInstance()->get( 'display_prices_with_shipping') && !empty($item->product_ships))
                            {
                                echo '<br /><a href="'.$shipping_cost_link.'" target="_blank">'.sprintf( JText::_('LINK_TO_SHIPPING_COST'), $shipping_cost_link).'</a>' ;
                            }
                            ?>
                        </div>
                        
                        <?php // TODO Make this display the "quickAdd" layout in a lightbox ?>
                        <?php // $url = "index.php?option=com_tienda&format=raw&controller=carts&task=addToCart&productid=".$item->product_id; ?>
                        <?php // $onclick = 'tiendaDoTask(\''.$url.'\', \'tiendaUserShoppingCart\', \'\');' ?>
                        <?php // <img class="addcart" src="media/com_tienda/images/addcart.png" alt="" onclick="<?php echo $onclick; " /> ?>
                    </div>
                </div>
                
                <div class="product_info">
                    <div class="product_name">
                        <span>
                            <a href="<?php echo JRoute::_($item->link."&filter_category=".$this->cat->category_id."&Itemid=".$item->itemid ); ?>">
                            <?php echo $item->product_name; ?>
                            </a>
                        </span>
                    </div>
                    
                    <?php if (!empty($item->product_model) || !empty($item->product_sku)) { ?>
                        <div class="product_numbers">
                            <span class="model">
                                <?php if (!empty($item->product_model)) : ?>
                                    <span class="title"><?php echo JText::_('Model'); ?>:</span> 
                                    <?php echo $item->product_model; ?>
                                <?php endif; ?>
                            </span>
                            <span class="sku">
                                <?php if (!empty($item->product_sku)) : ?>
                                    <span class="title"><?php echo JText::_('SKU'); ?>:</span> 
                                    <?php echo $item->product_sku; ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php } ?>

                    <div class="product_minidesc">
                    <?php
                        if (!empty($item->product_description_short))
                        {
                            echo $item->product_description_short;
                        }
                            else
                        {                  
                            $str = wordwrap($item->product_description, 200, '`|+');
                            $wrap_pos = strpos($str, '`|+');
                            if ($wrap_pos !== false) {
                                echo substr($str, 0, $wrap_pos).'...';
                            } else {
                                echo $str;
                            }    
                        }
                    ?>
                    </div>
                </div>
            </div>
            <div class="reset"></div>
            <?php endforeach; ?>
        
            <div id="products_footer">
                <div id="results_counter" class="pagination"><?php echo @$this->pagination->getResultsCounter(); ?></div>
                <?php echo @$this->pagination->getListFooter(); ?>
            </div>
        </div>
        
    <?php echo $this->form['validate']; ?>
    </form>
    <?php endif; ?>
    
</div>