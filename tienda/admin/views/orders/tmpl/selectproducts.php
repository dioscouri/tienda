<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>

<?php 
if (JRequest::getVar('windowtask') == 'close')
{
?>
    <script type="text/javascript">
    window.parent.tiendaAddProductsToOrder();
    </script>
<?php
}
?>

<div class="note" style="width: 95%; text-align: center; margin-left: auto; margin-right: auto;">
    <button onclick="document.getElementById('task').value='addproducts'; document.adminForm.submit();"> <?php echo JText::_( "Add Selected Products to Order" ); ?></button>
</div>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

    <table>
        <tr>
            <td align="left" width="100%">
                <input name="filter" value="<?php echo @$state->filter; ?>" />
                <button onclick="this.form.submit();"><?php echo JText::_('Search'); ?></button>
                <button onclick="tiendaFormReset(this.form);"><?php echo JText::_('Reset'); ?></button>
            </td>
            <td nowrap="nowrap">
                <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                <?php echo TiendaSelect::category( @$state->filter_category, 'filter_category', $attribs, 'category', true ); ?>
            </td>
        </tr>
    </table>

    <table class="adminlist" style="clear: both;">
        <thead>
            <tr>
                <th style="width: 5px;">
                    <?php echo JText::_("Num"); ?>
                </th>
                <th style="width: 20px;">
                    <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
                </th>
                <th style="width: 50px;">
                    <?php echo TiendaGrid::sort( 'ID', "tbl.product_id", @$state->direction, @$state->order ); ?>
                </th>                
                <th style="text-align: left;">
                    <?php echo TiendaGrid::sort( 'Name', "tbl.product_name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="width: 100px;">
                    <?php echo TiendaGrid::sort( 'Price', "price", @$state->direction, @$state->order ); ?>
                </th>
                <th>
                    <?php echo JText::_( "Quantity" ); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
                <td align="center">
                    <?php echo $i + 1; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo TiendaGrid::checkedout( $item, $i, 'product_id' ); ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $item->product_id; ?>
                </td>   
                <td style="text-align: left;">
                    <?php echo $item->product_name; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo TiendaHelperBase::currency( $item->price ); ?>
                </td>
                <td style="text-align: center;">
                    <input name="quantity[<?php echo $item->product_id; ?>]" type="text" value="1" style="width: 30px;" />
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

    <input type="hidden" name="task" id="task" value="selectproducts" />
    <input type="hidden" name="boxchecked" value="" />
    <input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
    <input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
    
    <?php echo $this->form['validate']; ?>
</form>