<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items;?>

<table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_("Num"); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_("ID"); ?>
                </th>
                <th style="text -align: left; width:150px;">
                	<?php echo JText::_("Product name"); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_("Attributes + Options"); ?>
                </th>
				<th style="width: 80px;">
                    <?php echo JText::_("Model"); ?>
                </th>
                <th style="width: 70px;">
                    <?php echo JText::_("SKU"); ?>
                </th>
                <th style="width: 70px;">
                    <?php echo JText::_("Total Price"); ?>
                </th>
                 <th style="width: 70px;">
                    <?php echo JText::_("Total Quantity"); ?>
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
                    <?php echo JText::_('No items found'); ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>