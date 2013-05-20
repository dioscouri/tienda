<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$items = @$this->items;
$citems = @$this->citems;
?>

<div id="tienda" class="products directory">
    <div id="tienda_categories">    
        <div id='tienda_category_header'>
            <h3><?php echo JText::_('COM_TIENDA_BROWSE_CATEGORIES'); ?></h3>
            <div class='category_description'><?php echo $this->cat->category_description; ?></div>
        </div>
        
        <?php if (!empty($citems)) : ?>
            <div class="directory_categories" style="width: 100%;">
                <?php
                foreach ($citems as $citem) :
                    $model = DSCModel::getInstance('Categories', 'TiendaModel');
                    $model->setState('filter_enabled', '1');
                    $model->setState('filter_level', $citem->category_id);
                    $model->setState('order', 'tbl.ordering');
                    $model->setState('direction', 'ASC');
                    $categories = $model->getList();
                    ?>
                    <div class="directory_category" style="width: 49%; float: left; margin: 3px;">
                        <div class="directory_category_header" style="padding: 5px; background-color: #DDDDDD; font-size: 120%; font-weight: bold;">
                            <span><?php echo $citem->category_name; ?></span>
                        </div>
                        <?php if (!empty($categories)) { ?>
                            <ul class="directory_subcategory">
                            <?php foreach ($categories as $category) { 
                                $pmodel = DSCModel::getInstance('Products', 'TiendaModel');
                                $pmodel->setState('filter_category', $category->category_id);
                                $products = $pmodel->getTotal();
                                ?>
                                <li>
                                <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$category->category_id.$category->slug."&Itemid=".$citem->itemid ); ?>">
                                <?php echo $category->category_name; ?> (<?php echo $products; ?>)
                                </a>
                                </li>
                            <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>
                <?php
                endforeach; 
                ?>
                <div class="reset"></div>
            </div>
        <?php endif; ?>
    </div>
    
</div>