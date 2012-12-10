<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$form = @$this->form;
$row = @$this->row;
$helper_product = new TiendaHelperProduct();
?>

<table class="table table-striped table-bordered" style="width: 100%;">
    <tr>
        <td class="dsc-key"><?php echo JText::_('COM_TIENDA_MANUFACTURER'); ?>:</td>
        <td class="dsc-value"><?php echo TiendaSelect::manufacturer( @$row->manufacturer_id, 'manufacturer_id', '', 'manufacturer_id', false, true ); ?>
        </td>
    </tr>
    <?php 
    if (empty($row->product_id))
    {
        // doing a new product, so display a notice
        ?>
    <tr>
        <td class="dsc-key"><?php echo JText::_('COM_TIENDA_PRODUCT_ATTRIBUTES'); ?>:</td>
        <td class="dsc-value">
            <div class="note well">
                <?php echo JText::_('COM_TIENDA_CLICK_APPLY_TO_BE_ABLE_TO_CREATE_PRODUCT_ATTRIBUTES'); ?>
            </div>
        </td>
    </tr>
    <?php
    }
    else
    {
        // display lightbox link to manage attributes
        ?>
    <tr>
        <td class="dsc-key"><?php echo JText::_('COM_TIENDA_PRODUCT_ATTRIBUTES'); ?>:</td>
        <td class="dsc-value">[<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setattributes&id=".$row->product_id."&tmpl=component", JText::_('COM_TIENDA_SET_ATTRIBUTES'), array('onclose' => '\function(){tiendaNewModal(\''.JText::_('COM_TIENDA_SAVING_THE_PRODUCT').'\'); submitbutton(\'apply\');}') ); ?>] <?php $attributes = $helper_product->getAttributes( $row->product_id ); ?>
            <div id="current_attributes">
                <?php foreach (@$attributes as $attribute) : ?>
                [<a href="<?php echo "index.php?option=com_tienda&view=productattributes&task=delete&cid[]=".$attribute->productattribute_id."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>"> <?php echo JText::_('COM_TIENDA_REMOVE'); ?>
                </a>] [
                <?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setattributeoptions&id=".$attribute->productattribute_id."&tmpl=component", JText::_('Set Attribute Options'), array('onclose' => '\function(){tiendaNewModal(\''.JText::_('COM_TIENDA_SAVING_THE_PRODUCT').'\'); submitbutton(\'apply\');}') ); ?>
                ]
                <?php echo $attribute->productattribute_name; ?>
                <?php echo "(".$attribute->option_names_csv.")"; ?>
                <br />
                <?php endforeach; ?>
            </div>
        </td>
    </tr>
    <?php
    }
    ?>

    <tr>
        <td class="dsc-key"><?php echo JText::_('COM_TIENDA_PUBLISH_UP'); ?>:</td>
        <td><?php echo JHTML::calendar( @$row->publish_date, "publish_date", "publish_date", '%Y-%m-%d %H:%M:%S', array('size'=>25) ); ?>
        </td>
    </tr>
    <tr>
        <td class="dsc-key"><?php echo JText::_('COM_TIENDA_PUBLISH_DOWN'); ?>:</td>
        <td><?php echo JHTML::calendar( @$row->unpublish_date, "unpublish_date", "unpublish_date", '%Y-%m-%d %H:%M:%S', array('size'=>25) ); ?>
        </td>
    </tr>
    <?php 
    if (empty($row->product_id))
    {
        // doing a new product, so collect default info
        ?>
    <tr>
        <td class="dsc-key">
        <label for="category_id"> <?php echo JText::_('COM_TIENDA_PRODUCT_CATEGORY'); ?>:
        </label>
        </td>
        <td><?php echo TiendaSelect::category( '', 'category_id', '', 'category_id' ); ?>
            <div class="note well">
                <?php echo JText::_('COM_TIENDA_SET_INITIAL_CATEGORY_NOW_ADDITIONAL_ONES_LATER'); ?>
            </div>
        </td>
    </tr>
    <?php
    }
    else
    {
        // display lightbox link to manage categories
        ?>
    <tr>
        <td class="dsc-key"><?php echo JText::_('COM_TIENDA_CATEGORIES'); ?>:</td>
        <td><?php Tienda::load( 'TiendaHelperCategory', 'helpers.category' ); ?> <?php Tienda::load( 'TiendaUrl', 'library.url' ); 
        $options = array('update' => true );
        ?> [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=selectcategories&id=".$row->product_id."&tmpl=component", JText::_('COM_TIENDA_SELECT_CATEGORIES'), $options); ?>] <?php $categories = $helper_product->getCategories( $row->product_id ); ?>
            <div id="current_categories">
                <?php foreach (@$categories as $category) : ?>
                [<a href="<?php echo "index.php?option=com_tienda&view=products&task=selected_disable&id=".$row->product_id."&cid[]=".$category."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$row->product_id); ?>"> <?php echo JText::_('COM_TIENDA_REMOVE'); ?>
                </a>]
                <?php echo TiendaHelperCategory::getPathName( $category ); ?>
                <br />
                <?php endforeach; ?>
            </div>
        </td>
    </tr>
    <?php
    }
    ?>
</table>


<?php
// fire plugin event here to enable extending the form
JDispatcher::getInstance()->trigger('onAfterDisplayProductFormMainColumn', array( $row ) );
?>