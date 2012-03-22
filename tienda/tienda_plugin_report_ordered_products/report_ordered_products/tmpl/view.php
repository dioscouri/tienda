<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items; ?>

    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_('Num'); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_('Product Name'); ?>
                </th>
                <th style="text-align: center; width : 200px;">
                    <?php echo JText::_('Manufacturer'); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_('Attributes'); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_('Quantity'); ?>
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
                <td style="text-align: left;">
                    <?php echo "[" . $item->product_id . "] " . JText::_($item->product_name); ?>                    
                </td>     
                <td style="text-align: left;">
                    <?php echo $item->manufacturer_name; ?>                    
                </td>        
                <td style="text-align: left;">
                    <?php echo JText::_($item->orderitem_attribute_names); ?>                    
                </td>        
                <td style="text-align: center;">
                    <?php echo $item->total_quantity;?>
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
        </tbody>
    </table>
