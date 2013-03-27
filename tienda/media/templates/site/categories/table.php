<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$items = @$this->items;
$citems = @$this->citems;
?>

<div id="tienda" class="products default">

    <?php if ($this->level > 1 && Tienda::getInstance()->get('display_tienda_pathway')) : ?>
        <div id='tienda_breadcrumb'>
            <?php echo TiendaHelperCategory::getPathName($this->cat->category_id, 'links'); ?>
        </div>
    <?php endif; ?>

    <div id="tienda_categories">    
        <div id='tienda_category_header'>
            <?php if (isset($state->category_name)) : ?>
                <span><?php echo @$this->title; ?></span>
            <?php else : ?>
                <span><?php echo JText::_('COM_TIENDA_ALL_CATEGORIES'); ?></span>
            <?php endif; ?>
                        
            <div class='category_description'><?php echo $this->cat->category_description; ?></div>
        </div>
        
        <?php if (!empty($citems)) : ?>
            <div class="tienda_subcategories">
                <?php
                foreach ($citems as $citem) :
                    $model = DSCModel::getInstance('Products', 'TiendaModel');
                    $model->setState('filter_enabled', '1');
                    $model->setState('filter_category', $citem->category_id);
                    $model->setState('order', 'tbl.ordering');
                    $model->setState('direction', 'ASC');
                    $products = $model->getList();
                    // if there are no products, skip it
                    if (empty($products)) { continue; }                
                    ?>
                    <table class="subcategory" style="width: 100%;">
                    <thead>
                    <tr>
                        <th class="subcategory_name" style="background-color: #DDDDDD;">
                            <?php echo $citem->category_name; ?>
                        </th>
                        <th class="subcategory_price" style="background-color: #DDDDDD;">
                            <?php echo JText::_('COM_TIENDA_PRICE'); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($products as $product)
                    {
                        $itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $citem->category_id, true );
                        $product->itemid = (!empty($itemid)) ? $itemid : JRequest::getInt('Itemid', $itemid);
                        ?>
                        <tr>
                        <td>
                            <a href="<?php echo JRoute::_($product->link."&filter_category=".$citem->category_id."&Itemid=".$product->itemid ); ?>">
                            <?php echo $product->product_name; ?>
                            </a>
                        </td>
                        <td class="subcategory_price">
                            <?php echo TiendaHelperBase::currency( $product->price ); ?>
                        </td>
                        </tr>
                        <?php 
                    } 
                    ?>
                    </tbody>
                    </table>
                <?php
                endforeach; 
                ?>
                <div class="reset"></div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($items)) : ?>
        <table class="subcategory" style="width: 100%;">
        <thead>
        <tr>
            <th class="subcategory_name" style="background-color: #DDDDDD;">
                <?php echo $this->cat->category_name; ?>
            </th>
            <th class="subcategory_price" style="background-color: #DDDDDD;">
                <?php echo JText::_('COM_TIENDA_PRICE'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item) : 
            $itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category( $this->cat->category_id, true );
            $item->itemid = (!empty($itemid)) ? $itemid : JRequest::getInt('Itemid', $itemid);
            ?>
                <tr>
                <td>
                    <a href="<?php echo JRoute::_($item->link."&filter_category=".$this->cat->category_id."&Itemid=".$item->itemid ); ?>">
                    <?php echo $item->product_name; ?>
                    </a>
                </td>
                <td class="subcategory_price">
                    <?php echo TiendaHelperBase::currency( $item->price ); ?>
                </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        </table>

    <?php endif; ?>
    
</div>