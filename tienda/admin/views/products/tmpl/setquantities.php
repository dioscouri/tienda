<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>

<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_( "Set Quantities for" ); ?>: <?php echo $row->product_name; ?></h1>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

    <?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

<div class="note_green" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Current Quantities'); ?></div>
    <div style="float: right;">
        <button onclick="document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.getElementById('task').value='savequantities'; document.adminForm.submit();"><?php echo JText::_('Save All Changes'); ?></button>
    </div>
    <div class="reset"></div>
    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 20px;">
                    #
                </th>
                <th style="width: 20px;">
                    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="text-align: left;">
                    <?php echo JText::_( 'Attribute Names' ); ?>
                </th>
                <th style="text-align: center;">
                    <?php echo TiendaGrid::sort( 'Quantity', "tbl.quantity", @$state->direction, @$state->order ); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
                <td style="text-align: center;">
                    <?php echo $this->pagination->limitstart + $i + 1; ?>
                </th>
                <td style="text-align: center;">
                    <?php echo TiendaGrid::checkedout( $item, $i, 'productquantity_id' ); ?>
                </td>
                <td style="text-align: left;">
                    <?php echo $item->product_attribute_names; ?>
                </td>
                <td style="text-align: center;">
                    <input type="text" name="quantity[<?php echo $item->productquantity_id; ?>]" value="<?php echo $item->quantity; ?>" />
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
        <tfoot>
            <tr>
                <td colspan="20">
                    <?php echo @$this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <input type="hidden" name="order_change" value="0" />
    <input type="hidden" name="id" value="<?php echo $row->product_id; ?>" />
    <input type="hidden" name="task" id="task" value="setquantities" />
    <input type="hidden" name="boxchecked" value="" />
    <input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
    <input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
    
    <?php echo $this->form['validate']; ?>
</div>
</form>