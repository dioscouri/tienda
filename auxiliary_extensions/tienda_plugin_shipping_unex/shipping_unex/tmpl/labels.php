<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php $form = @$vars->form; ?>
<?php $order = @$vars->order; ?>
<?php $labels = @$vars->labels; ?>
<?php $url = 'index.php?option=com_tienda&format=raw&task=doTaskAjax&element=shipping_unex&elementTask=fetchStickersAjax&order_id='.$order->order_id; ?>

<div id="unex_labels">
        
        <form name="unexForm" id="unexForm" method="get">
        
        <fieldset>
            <legend><?php echo JText::_('Unex Shipment'); ?></legend>

		<table class="admintable" style="clear: both; width: 100%;">
    	<tr>
    	    <td style="width: 100px; text-align: right;" class="key">
    	        <?php echo JText::_('Fetch Labels'); ?>
    	    </td>
    	    <td>
    	        <input value="<?php echo JText::_('Fetch Labels'); ?>" onclick="tiendaDoTask('<?php echo $url; ?>', 'unexResult');" style="float: right;" type="button" />
    	    </td>
    	</tr>
    	<tr>
    	    <td style="width: 100px; text-align: right;" class="key">
    	        <?php echo JText::_('Result'); ?>
    	    </td>
    	    <td>
    	        <div id="unexResult">
    	        <?php 
    	        	if($labels)
    	        	{
	    	        	foreach($labels as $item)
	    	        	{
			    	        ?>
			    	       <div class="productfile">
					            <span class="productfile_image">
					                <a href="<?php echo JRoute::_( 'index.php?option=com_tienda&controller=unex&task=downloadfile&format=raw&id='.$order->order_id."&filename=".$item); ?>">
					                    <img src="<?php echo Tienda::getURL('images')."download.png"; ?>" alt="<?php echo JText::_('COM_TIENDA_DOWNLOAD') ?>" style="height: 24px; padding: 5px; vertical-align: middle;" />
					                </a>
					            </span>            
					            <span class="productfile_link" style="vertical-align: middle;" >
					                <a href="<?php echo JRoute::_( 'index.php?option=com_tienda&controller=unex&task=downloadfile&format=raw&id='.$id."&filename=".$item); ?>"> 
					                <?php echo $item; ?>
					                </a>
					            </span>
					        </div>
			    	        <?php
	    	        	}
    	        	}
    	        ?>
    	        </div>
    	    </td>
    	</tr>
		</table>		
		
		</fieldset>
		</form>
</div>
