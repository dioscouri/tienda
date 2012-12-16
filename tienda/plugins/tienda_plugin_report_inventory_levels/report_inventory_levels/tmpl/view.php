<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items;
var_dump($vars->pagination);
?>

<table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_('COM_TIENDA_NUM'); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_('COM_TIENDA_ID'); ?>
                </th>
                <th style="text -align: left; width:150px;">
                	<?php echo JText::_('COM_TIENDA_PRODUCT_NAME'); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_('COM_TIENDA_ATTRIBUTES_PLUS_OPTIONS'); ?>
                </th>
				<th style="width: 80px;">
                    <?php echo JText::_('COM_TIENDA_MODEL'); ?>
                </th>
                <th style="width: 70px;">
                    <?php echo JText::_('COM_TIENDA_SKU'); ?>
                </th>
                <th style="width: 70px;">
                    <?php echo JText::_('COM_TIENDA_TOTAL_PRICE'); ?>
                </th>
                 <th style="width: 70px;">
                    <?php echo JText::_('COM_TIENDA_TOTAL_QUANTITY'); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="20">

                </td>
            </tr>
        </tfoot>
        <tbody>
        <?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
                <td align="center">
                    <?php echo $i + 1; ?>
                </td>
                <td style="text-align: center;">
                        <?php echo $item->product_id; ?>
                </td>
                <td style="text-align: left;">
                	<a href="index.php?option=com_tienda&view=products&task=edit&id=<?php echo $item->product_id; ?>">
                        <?php echo JText::_($item->product_name); ?>
                    </a>
                 </td>
                 <td style="text-align: left;">
               <?php $attributes = plgTiendaReport_inventory_levels::getAttributes( $item->product_id ); ?>
                 	<div id="current_attributes">
                 		<?php foreach(@$attributes as $attribute): ?>
                      		<?php echo JText::_($attribute->productattribute_name); ?>
                      		<?php echo "(".$attribute->option_names_csv.")"; ?>
                     	<?php endforeach; ?>
                    </div>
                </td>
				<td style="text-align: center;">
                    <?php echo $item->product_model; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->product_sku; ?>
                </td>
                <td style="text-align: right;">
                   <?php echo TiendaHelperBase::currency($item->total_value); ?>
                </td>
                <td style="text-align: center;">
                   <?php echo $item->product_quantity; ?>
                </td>
            </tr>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>

            <?php if (!count(@$items)) : ?>
            <tr>
                <td colspan="10" align="center">
                    <?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
                </td>
            </tr>
            <?php endif; ?>
                 <td colspan="20">
                    <div style="float: right; padding: 5px;"><?php echo @$vars->pagination->getResultsCounter(); ?></div>
                    <?php echo @$vars->pagination->getPagesLinks(); ?>
                </td>
           
        </tbody>
    </table>
 