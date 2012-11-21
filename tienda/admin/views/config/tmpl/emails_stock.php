<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_LOW_STOCK_NOTIFY'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('low_stock_notify', 'class="inputbox"', $this -> row -> get('low_stock_notify', '0')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_LOW_STOCK_NOTIFY_VALUE'); ?>
            </th>
            <td><input ="text" name="low_stock_notify_value" value="<?php echo $this -> row -> get('low_stock_notify_value', '0'); ?>" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_LOW_STOCK_NOTIFY_VALUE_DESC'); ?>
            </td>
        </tr>
    </tbody>
</table>
