<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php $form = @$vars->form; ?>
<?php $items = @$vars->list; ?>

<p><?php echo sprintf( JText::_('SHIPPING_PLUGIN_UNEX_CONFIG_HELP'), $vars->link ); ?></p>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >
    <fieldset>
        <legend><?php echo JText::_('COM_TIENDA_ENABLED_SERVICES'); ?></legend>
        
    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_('COM_TIENDA_ID'); ?>
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_('COM_TIENDA_NAME'); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_('COM_TIENDA_CODE'); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="20">
                    &nbsp;
                </td>
            </tr>
        </tfoot>
        <tbody>
        <?php $k=0; ?>
        <?php foreach (@$items as $key=>$item) : ?>
            <tr class='row<?php echo $k; ?>'>
                <td align="center">
                    <?php echo @$item->service_id ?>
                </td>
<!--                <td style="text-align: center;">-->
<!--                    <input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $key; ?>" onclick="isChecked(this.checked);" />-->
<!--                </td>-->
                <td style="text-align: left;">
                <a href="<?php echo @$item->link; ?>">
                    <?php echo @$item->service_name ?>
                    </a>
                </td>
                <td style="text-align: center;">
                    <?php echo @$item->service_code ?>
                </td>
            </tr>
            <?php $k = (1 - $k); ?>
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

    <input type="hidden" name="order_change" value="0" />
    <input type="hidden" name="sid" value="" />
    <input type="hidden" name="shippingTask" value="_default" />
    <input type="hidden" name="task" value="view" />
    <input type="hidden" name="boxchecked" value="" />
    <input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
    <input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
        
    </fieldset>
    
    <input type="hidden" name="id" value="<?php echo @$vars->id; ?>" />
</form>

