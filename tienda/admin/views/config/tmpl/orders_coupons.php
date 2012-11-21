<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_COUPONS'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('coupons_enabled', 'class="inputbox"', $this -> row -> get('coupons_enabled', '1')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_MULTIPLE_USER_SUBMITTED_COUPONS_PER_ORDER'); ?>
            </th>
            <td><?php  echo TiendaSelect::btbooleanlist('multiple_usercoupons_enabled', 'class="inputbox"', $this -> row -> get('multiple_usercoupons_enabled', '0')); ?>
            </td>
            <td></td>
        </tr>
    </tbody>
</table>
