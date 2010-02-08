<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$state = @$this->state;
$items = @$this->items;
?>

<div class='categoryheading'>
<?php echo JText::_('Search Results for').': '.$state->filter; ?>
</div>

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
<?php
if (empty($items)) {
    echo JText::_('No matching items found.');
} else {
    foreach ($items as $item) {
?>
        <tr class="productitem">
            <td class="productthumb">
                <div class="productbuy">
                    <a href="<?php echo JRoute::_( $item->link ); ?>">
                    <?php echo TiendaHelperProduct::getImage($item->product_id); ?>
                    </a>
                    <p class="price"><?php echo TiendaHelperBase::currency($item->price); ?></p>
                </div>
            </td>
            <td class="productinfo">
                <span class="productname"><a href="<?php echo JRoute::_($item->link); ?>"><?php echo $item->product_name; ?></a></span><br />
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
                </span><br />
                <span class="productrating"><!-- <img src="media/com_tienda/images/ratings_star_4_1.gif" alt="" /> --></span><br />
                <span class="productminidesc"><?php $str = wordwrap($item->product_description, 200, '`|+'); echo substr($str, 0, stripos($str, '`|+')).'...'; ?></span>
            </td>
        </tr>

<?php
    }
}
?>
      </tbody>
    </table>
<?php echo $this->form['validate']; ?>
</form>