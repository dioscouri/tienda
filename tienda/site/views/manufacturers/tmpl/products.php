<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$items = @$this->items;
$title = @$this->title;
$citems = @$this->citems;
?>

<div id="tienda" class="products default">

	<div id='tienda_category_header'>
        <span><?php echo @$this->title; ?></span>
        <div class='category_description'><?php echo $this->cat->manufacturer_description; ?></div>
    </div>

    <?php if (!empty($items)) : ?>
        <div id="tienda_products">
            <?php foreach ($items as $item) : ?>
            <div class="product_item">
                <div class="product_thumb">
                    <div class="product_listimage">
                        <a href="<?php echo JRoute::_( $item->link."&filter_category=".$this->cat->category_id ."&Itemid=".$item->itemid ); ?>">
                            <?php echo TiendaHelperProduct::getImage($item->product_id); ?>
                        </a>
                    </div>
                    <div class="reset"></div>
                </div>

                <div id="product_buy_<?php echo $item->product_id; ?>" class="product_buy">
                    <?php echo $item->product_buy; ?>
                </div>
                
                <div class="product_info">
                    <div class="product_name">
                        <span>
                            <a href="<?php echo JRoute::_($item->link."&filter_category=".$this->cat->category_id."&Itemid=".$item->itemid ); ?>">
                            <?php echo $item->product_name; ?>
                            </a>
                        </span>
                    </div>
                    <?php if ( TiendaConfig::getInstance()->get('product_review_enable', '0') ) { ?>  
                    <div class="product_rating">
                       <?php echo TiendaHelperProduct::getRatingImage( $item->product_rating ); ?>
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