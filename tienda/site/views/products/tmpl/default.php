<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$items = @$this->items;
$citems = @$this->citems;
?>

<?php if ($this->level > 1 ) { ?>
<div class='catcrumbs'><?php echo TiendaHelperCategory::getPathName($this->cat->category_id, 'links'); ?></div>
<?php } ?>
<div class='categoryheading'>
    <table>
        <tr>
            <td style="vertical-align: top;"><?php
                if (isset($state->category_name)) {
                    echo '<img src="'.TiendaHelperCategory::getImage($this->cat->category_id, '', '', '', true).'" alt="" style="max-height: 75px;" />';
                }
                ?>
            </td>
            <td style="vertical-align: top; padding-left: 10px;"><?php
                echo '<span>'.@$this->title.'</span>';
                echo "<div class='catdesc'>".$this->cat->category_description.'</div>';
                ?>
            </td>
        </tr>
    </table>
</div>

<?php if (!empty($citems)) { ?>
<div id="subcategories">
    <?php if ($this->level > 1) { echo '<h3>'.JText::_('Subcategories').'</h3>'; } ?>
    <?php foreach ($citems as $citem) : ?>
    <div class="subcategory">
        <p class="subcatthumb">
            <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$citem->category_id ); ?>">
            <?php echo TiendaHelperCategory::getImage($citem->category_id); ?>
            </a>
        </p>
        <p class="subcatname">
            <a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$citem->category_id ); ?>">
            <?php echo $citem->category_name; ?>
            </a>
        </p>
    </div>
    <?php endforeach; ?>
    <div class="reset"></div>
</div>
<?php } ?>

<form action="<?php echo JRoute::_( @$form['action']."&limitstart=".@$state->limitstart )?>" method="post" name="adminForm" enctype="multipart/form-data">

    <table class="product">
      <tfoot>
        <tr>
            <td colspan="20">
                <div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
                <?php echo @$this->pagination->getListFooter(); ?>
            </td>
        </tr>
      </tfoot>
      <tbody>
<?php foreach ($items as $item) : ?>
        <tr class="productitem">
            <td class="productthumb">
                <div class="productbuy">
                    <a href="<?php echo JRoute::_( $item->link."&filter_category=".$this->cat->category_id ); ?>">
                    <?php echo TiendaHelperProduct::getImage($item->product_id); ?>
                    </a>
                    <p class="price"><?php echo TiendaHelperBase::currency($item->price); ?></p>
                    <?php // TODO Make this display the "quickAdd" layout in a lightbox ?>
                    <?php // $url = "index.php?option=com_tienda&format=raw&controller=carts&task=addToCart&productid=".$item->product_id; ?>
                    <?php // $onclick = 'tiendaDoTask(\''.$url.'\', \'tiendaUserShoppingCart\', \'\');' ?>
                    <?php // <img class="addcart" src="media/com_tienda/images/addcart.png" alt="" onclick="<?php echo $onclick; " /> ?>
                </div>
            </td>
            <td class="productinfo">
                <span class="productname"><a href="<?php echo JRoute::_($item->link."&filter_category=".$this->cat->category_id ); ?>"><?php echo $item->product_name; ?></a></span>
                <br />
                <?php if (!empty($item->product_model) || !empty($item->product_sku)) { ?>
    	            <span class="productnums">
    	            <?php
    	                $sep = '';
    	                if (!empty($item->product_model)) {
    	                    echo '<b>'.JText::_('Model').":</b> $item->product_model";
    	                    $sep = "&nbsp;&nbsp;";
    	                }
    	                if (!empty($item->product_sku)) {
    	                    echo "$sep <b>".JText::_('SKU').":</b> $item->product_sku";
    	                }
    	            ?>
    	            </span>
    	            <br />
    	        <?php } ?>
    	        
                <!-- <span class="productrating"> <img src="media/com_tienda/images/ratings_star_4_1.gif" alt="" /> </span><br />-->
                <span class="productminidesc"><?php $str = wordwrap($item->product_description, 200, '`|+'); echo substr($str, 0, stripos($str, '`|+')).'...'; ?></span>
            </td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
<?php echo $this->form['validate']; ?>
</form>