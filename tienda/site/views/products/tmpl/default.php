<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$items = @$this->items;
$citems = @$this->citems;
?>

<div id="tienda" class="product default">
    <div id="categories">
        <?php if ($this->level > 1 ) : ?>
            <div id='breadcrumb'>
                <?php echo TiendaHelperCategory::getPathName($this->cat->category_id, 'links'); ?>
            </div>
        <?php endif; ?>
        
        <div id='heading'>
            <?php if (isset($state->category_name)) : ?>
                <img src="<?php echo TiendaHelperCategory::getImage($this->cat->category_id, '', '', '', true); ?>" alt="" class="category image" />
            <?php endif; ?>

            <span><?php echo @$this->title; ?></span>
            <div class='description'><?php echo $this->cat->category_description; ?></div>
        </div>
        
        <?php if (!empty($citems)) : ?>
            <div id="subcategories">
                <?php if ($this->level > 1) { echo '<h3>'.JText::_('Subcategories').'</h3>'; } ?>
                <?php foreach ($citems as $citem) : ?>
                    <div class="subcategory">
                        <div class="subcatthumb">
                            <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$citem->category_id ); ?>">
                            <?php echo TiendaHelperCategory::getImage($citem->category_id); ?>
                            </a>
                        </div>
                        <div class="subcatname">
                            <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$citem->category_id ); ?>">
                            <?php echo $citem->category_name; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="reset"></div>
            </div>
        <?php endif; ?>
    
    </div>
    
    <form action="<?php echo JRoute::_( @$form['action']."&limitstart=".@$state->limitstart )?>" method="post" name="adminForm" enctype="multipart/form-data">
    
        <div id="products">
            <?php foreach ($items as $item) : ?>
            <div class="productitem">
                <div class="productthumb">
                    <div class="productbuy">
                        <a href="<?php echo JRoute::_( $item->link."&filter_category=".$this->cat->category_id ); ?>">
                            <?php echo TiendaHelperProduct::getImage($item->product_id); ?>
                        </a>
                        
                        <div class="price">
                            <?php echo TiendaHelperBase::currency($item->price); ?>
                        </div>
                        
                        <?php // TODO Make this display the "quickAdd" layout in a lightbox ?>
                        <?php // $url = "index.php?option=com_tienda&format=raw&controller=carts&task=addToCart&productid=".$item->product_id; ?>
                        <?php // $onclick = 'tiendaDoTask(\''.$url.'\', \'tiendaUserShoppingCart\', \'\');' ?>
                        <?php // <img class="addcart" src="media/com_tienda/images/addcart.png" alt="" onclick="<?php echo $onclick; " /> ?>
                    </div>
                </div>
                
                <div class="productinfo">
                    <div class="productname">
                        <span>
                            <a href="<?php echo JRoute::_($item->link."&filter_category=".$this->cat->category_id ); ?>">
                            <?php echo $item->product_name; ?>
                            </a>
                        </span>
                    </div>
                    
                    <?php if (!empty($item->product_model) || !empty($item->product_sku)) { ?>
                        <div class="productnumbers">
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

                    <div class="productminidesc">
                    <?php 
                        $str = wordwrap($item->product_description, 200, '`|+'); 
                        echo substr($str, 0, stripos($str, '`|+'));
                        if (!empty($str)) { echo '...'; }
                    ?>
                    </div>
                </div>
            </div>
            <div class="reset"></div>
            <?php endforeach; ?>
        
            <div id="productsfooter">
                <div id="resultscounter"><?php echo @$this->pagination->getResultsCounter(); ?></div>
                <?php echo @$this->pagination->getListFooter(); ?>
            </div>
        </div>
        
    <?php echo $this->form['validate']; ?>
    </form>

</div>