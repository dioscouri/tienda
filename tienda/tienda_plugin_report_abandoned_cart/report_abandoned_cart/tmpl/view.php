<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items;?>
<?php debug(8888,$items);?>
    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_("Num"); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_("ID"); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_("Name"); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_("Email"); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_("Date"); ?>
                </th>

                <th style="width: 70px;">
                   <?php echo JText::_("No. of Carts"); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_("Subtotal of Carts"); ?>
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
                        <?php echo $item->id; ?>
                </td>
                <td style="text-align: left;">
                <!-- jos users -->
                	<?php echo JText::_($item->name); ?>
                </td>
                <td style="text-align: left;">
                <!-- jos users -->
                	<?php echo JText::_($item->email); ?>
                </td>
                <td style="text-align: left;">
                <!-- jos_tienda_orders -->
                	<?php echo JText::_($item->created_date); ?>
                </td>

                <td style="text-align: center;">
                    <?php echo $item->total_carts; ?>
                </td>
                <!-- subtotal of items on cart -->
                <td style="text-align: center;">
                    <?php echo $item->total_carts; ?>
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
