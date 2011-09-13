<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php Tienda::load( 'TiendaHelperOrder', 'helpers.order' );?>
<div class='componentheading'>
	<span><?php echo JText::_( "Order History" ); ?></span>
</div>

	<?php if ($menu =& TiendaMenu::getInstance()) { $menu->display(); } ?>
		
<form action="<?php echo JRoute::_( @$form['action']."&limitstart=".@$state->limitstart )?>" method="post" name="adminForm" enctype="multipart/form-data">
    
    <table>
        <tr>
            <td align="left" width="100%">
                <?php echo JText::_( "Search by applying filters" ); ?>
            </td>
            <td nowrap="nowrap" style="text-align: right;">
                <input name="filter" value="<?php echo @$state->filter; ?>" />
                <button onclick="this.form.submit();"><?php echo JText::_('Search'); ?></button>
                <button onclick="tiendaFormReset(this.form);"><?php echo JText::_('Reset'); ?></button>
            </td>
        </tr>
    </table>

    <table class="adminlist" style="clear: both;" >
        <thead>
            <tr class="filterline">
                <th>
                    <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                </th>
                <th>
                </th>
                <th>
                </th>                
                <th>
                </th>
                <th>
                    <?php echo TiendaSelect::orderstate(@$state->filter_orderstate, 'filter_orderstate', $attribs, 'order_state_id', true ); ?>
                </th>
            </tr>
            <tr>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'ID', "tbl.order_id", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 200px;">
                    <?php echo TiendaGrid::sort( 'Date', "tbl.created_date", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo JText::_( "Items" ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Total', "tbl.order_total", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'State', "s.order_state_name", @$state->direction, @$state->order ); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="20">
                    <div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
                    <?php echo @$this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
        <?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
                <td style="text-align: center;">
                    <a href="<?php echo JRoute::_( $item->link_view ); ?>">
                        <?php echo TiendaHelperOrder::displayOrderNumber( $item ); ?>
                    </a>
                </td>
                <td style="text-align: center;">
                    <a href="<?php echo JRoute::_( $item->link_view ); ?>">
                        <?php echo JHTML::_('date', $item->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
                    </a>
                </td>
                <td style="text-align: center;">
                    <a href="<?php echo JRoute::_( $item->link_view ); ?>">
                        <?php echo $item->items_count." ".JText::_( "Items" ); ?>
                    </a>
                </td>
                <td style="text-align: center;">
                    <?php echo TiendaHelperBase::currency( $item->order_total, $item->currency ); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->order_state_name; ?>
                </td>
            </tr>
            <?php $i=$i+1; $k = (1 - $k); ?>
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

    <input type="hidden" name="order_change" value="0" />
    <input type="hidden" name="id" value="" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="" />
    <input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
    <input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
    
    <?php echo $this->form['validate']; ?>
</form>