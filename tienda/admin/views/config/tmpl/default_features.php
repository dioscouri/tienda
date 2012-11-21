<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $row = @$this -> row; ?>

<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_SUBSCRIPTIONS'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('display_subscriptions', 'class="inputbox"', $this -> row -> get('display_subscriptions', '1')); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_ENABLE_SUBSCRIPTIONS_NOTE'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_MY_DOWNLOADS'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('display_mydownloads', 'class="inputbox"', $this -> row -> get('display_mydownloads', '1')); ?>
            </td>
            <td><?php echo JText::_('COM_TIENDA_ENABLE_MY_DOWNLOADS_NOTE'); ?>
            </td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_WISHLIST'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('display_wishlist', 'class="inputbox"', $this -> row -> get('display_wishlist', '0')); ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <th style="width: 25%;"><?php echo JText::_('COM_TIENDA_ENABLE_CREDITS'); ?>
            </th>
            <td style="width: 150px;"><?php  echo TiendaSelect::btbooleanlist('display_credits', 'class="inputbox"', $this -> row -> get('display_credits', '0')); ?>
            </td>
            <td></td>
        </tr>
    </tbody>
</table>
