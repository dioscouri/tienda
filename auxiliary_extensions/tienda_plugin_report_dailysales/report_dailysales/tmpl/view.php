<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php $items = @$vars->items; ?>
<?php $options = array('num_decimals'=>'2'); ?>
    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_('COM_TIENDA_NUM'); ?>
                </th>
                <th style=" width : 100px;">
                    <?php echo JText::_('Data'); ?>
                </th>
                
                <th style="width: 20px;">
                    <?php echo JText::_('Orders Quantity'); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_('Total Daily Amout'); ?>
                </th>
                <th >
                    
                </th>

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
        <?php //foreach (@$items as $item) : ?>
		<?php foreach (@$items as $key =>$item) : ?>
		<?php //fb(@$items,'items'); ?>
		<?php //fb($items,'items'); ?>
		<?php // fb($item,'item'); ?>
            <tr class='row<?php echo $k; ?>'>
                <td align="center"> 
                    <?php echo $i + 1; ?>
                </td>
                <td style="text-align: center;">
                        <?php //echo $item->user_username; ?>
						<?php echo $key; ?>

                </td>

                <td style="text-align: center;">
                    <?php echo $item->num; ?>
                </td>
                <td style="text-align: center;">
                    <?php //echo $item->order_total; ?>
					<?php echo TiendaHelperBase::currency( $item->amount, '', $options ); ?>
                </td>
				<td>
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
