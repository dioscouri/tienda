<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items; ?>
<h2><?php echo JText::_( "Results"); ?></h2>

    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_("Num"); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo JText::_("Manufacturer Name"); ?>
                </th>
                <th style="text-align: center; width : 200px;">
                    <?php echo JText::_("Count of Items"); ?>
                </th>
                <th style="text-align: center; width : 200px;">
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
        <?php $i=1; $k=0; ?>
        <?php foreach (@$items as $item) : ?>      
            <tr class='row<?php echo $k; ?>'>
                <td align="center">
                    <?php echo $i++; ?>
                </td>
                <td style="text-align: left;">
                    <?php if( strlen( $item->manufacturer_name ) ) echo $item->manufacturer_name; else echo ' - '.JText::_( 'No Manufacturer' ).' - ';?>
                </td>    
                <td style="text-align: center;">
                    <?php echo @$item->count_items; ?>                    
                </td>           
                <td style="text-align: center;">
                    <?php echo @TiendaHelperBase::currency( $item->price_total );?>
                </td>
            </tr>
            <?php $k = (1 - $k); ?>
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
