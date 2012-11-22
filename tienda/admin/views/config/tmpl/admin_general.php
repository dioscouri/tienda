<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_DISPLAY_STATISTICS'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('display_dashboard_statistics', 'class="inputbox"', $this -> row -> get('display_dashboard_statistics', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_SELECT_ORDER_STATES_TO_REPORT_ON'); ?>
            </th>
            <td><input type="text" name="orderstates_csv" value="<?php echo $this -> row -> get('orderstates_csv', '2, 3, 5, 17'); ?>" />
            </td>
            <td><?php echo JText::_('COM_TIENDA_CONFIG_ORDER_STATES_TO_REPORT_ON'); ?>
            </td>
        </tr>
    </tbody>
</table>
