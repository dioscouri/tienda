<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items; ?>

<h2><?php echo JText::_('Results'); ?></h2>

    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_('Num'); ?>
                </th>
                <th style="width: 50px;">
                    <?php echo JText::_('ID'); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_('Product Name'); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_('Price'); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_('Created'); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_('Expires'); ?>
                </th>
                
                <th style="text-align: left;">
                    <?php echo JText::_('User'); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_('Order ID'); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_('Order State'); ?>
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
                    <?php echo $item->subscription_id; ?>
                </td>
                <td style="text-align: left;">
                	<?php // Also product ID, [in brackets] ?>
                    <?php echo "[" . $item->product_id . "] " . JText::_($item->product_name); ?>                    
                </td>
                <td style="text-align: center;">
                    <?php // Price of subscription ?>
                    <?php echo TiendaHelperBase::currency( $item->orderitem_final_price ); ?>
                </td>
                <td style="text-align: center;">
                	<?php // JHTML created date ?>
                    <?php echo JHTML::_('date', $item->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
                </td>
                <td style="text-align: center;">
                    <?php // JHTML expires date ?>
                    <?php echo JHTML::_('date', $item->expires_datetime, TiendaConfig::getInstance()->get('date_format')); ?>
                </td>                
                <td style="text-align: left;">
                	<?php // Also more details on user, such as email and full name ?>
                    <?php echo $item->user_name . ", " . $item->user_username . ", " . "<a href=\"mailto:" . $item->email . "\">" . $item->email . "</a>"; ?>                    
                </td>
                <td style="text-align: center;">
                    <?php // Order id ?>
                    <?php echo $item->order_id; ?>
                </td>
                <td style="text-align: center;">
                    <?php // Order state ?>
                    <?php 
                    	$orderstate = JTable::getInstance('Orderstates', 'TiendaTable');
            			$orderstate->load( $item->order_state_id);
                    
                    	echo $orderstate->order_state_name; 
                    ?>
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
