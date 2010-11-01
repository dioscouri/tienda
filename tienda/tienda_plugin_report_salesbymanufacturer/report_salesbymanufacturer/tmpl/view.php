<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items; ?>

    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_("Num"); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo JText::_("Date"); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_("Manufacturer Name"); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo JText::_("Count of Items"); ?>
                </th>
                <th style="text-align: right;">
                    <?php echo JText::_("Sales Amount"); ?>
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
                	<?php // JHTML created date ?>
                    <?php echo JHTML::_('date', $item->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
                </td>
                <td style="text-align: left;">
                    <?php echo JText::_($item->manufacturer_name); ?>                    
                </td>    
                <td style="text-align: center;">
                    <?php echo $item->sales_count; ?>                    
                </td>           
                <td style="text-align: right;">
                    <?php echo TiendaHelperBase::currency($item->total_sales);?>
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
