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
                <th style="text-align: left; width : 150px;">
                    <?php echo JText::_('Name'); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_('Billing Address'); ?>
                </th>                
                <th style="width: 150px;">
                    <?php echo JText::_('COM_TIENDA_EMAIL'); ?>
                </th>
                <th style="width: 70px;">
                    <?php echo JText::_('Total'); ?>
                </th>
                <th style="width: 70px;">
                    <?php echo JText::_('Date'); ?>
                </th>
                <th style="width: 70px;">
                    <?php echo JText::_('Shipping costs'); ?>
                </th>
                <th style="width: 70px;">
                    <?php echo JText::_('Tax'); ?>
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
                        <?php echo $item->user_username; ?>
                </td>
                <td style="text-align: left;">
                        <?php
		                    echo $item->billing_first_name." ".$item->billing_last_name."<br/>";
		                    echo $item->billing_address_1.", ";
		                    echo $item->billing_address_2 ? $item->billing_address_2.", " : "";
		                    echo $item->billing_city.", ";
		                    echo $item->billing_zone_name." ";
		                    echo $item->billing_postal_code." ";
		                    echo $item->billing_country_name;
                        ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->email; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->order_total; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo JHTML::_('date', $item->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->order_shipping; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->order_tax; ?>
                </td>
            </tr>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>

            <?php if (!count(@$items)) : ?>
            <tr>
                <td colspan="8" align="center">
                    <?php echo JText::_('COM_TIENDA_NO_ITEMS_FOUND'); ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
