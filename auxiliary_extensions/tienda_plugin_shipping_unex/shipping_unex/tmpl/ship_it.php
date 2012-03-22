<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php $form = @$vars->form; ?>
<?php $order = @$vars->order; ?>
<?php $url = 'index.php?option=com_tienda&format=raw&task=doTaskAjax&element=shipping_unex&elementTask=sendShipmentAjax&order_id='.$order->order_id; ?>

<div id="unex_ship_it">
        
        <form name="unexForm" id="unexForm" method="get">
        
        <fieldset>
            <legend><?php echo JText::_('Unex Shipment'); ?></legend>

		<table class="admintable" style="clear: both; width: 100%;">
    	<tr>
    	    <td style="width: 100px; text-align: right;" class="key">
    	        <?php echo JText::_('Ship It'); ?>
    	    </td>
    	    <td>
    	        <input value="<?php echo JText::_('Ship This Order With Unex'); ?>" onclick="tiendaDoTask('<?php echo $url; ?>', 'unexResult');" style="float: right;" type="button" />
    	    </td>
    	</tr>
    	<tr>
    	    <td style="width: 100px; text-align: right;" class="key">
    	        <?php echo JText::_('Result'); ?>
    	    </td>
    	    <td>
    	        <div id="unexResult"><?php echo JText::_('Ready')?></div>
    	    </td>
    	</tr>
		</table>		
		
		</fieldset>
		</form>
</div>