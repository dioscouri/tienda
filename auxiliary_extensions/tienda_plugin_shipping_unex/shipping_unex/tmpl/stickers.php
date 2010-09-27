<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $labels = @$vars->labels; ?>
<?php echo Tienda::dump($vars->debug); ?>

<?php 
    	        	foreach($labels as $item)
    	        	{
		    	        ?>
		    	       <div class="productfile">
				            <span class="productfile_image">
				                <a href="<?php echo JRoute::_( 'index.php?option=com_tienda&controller=unex&task=downloadfile&format=raw&id='.$order->order_id."&filename=".$item); ?>">
				                    <img src="<?php echo Tienda::getURL('images')."download.png"; ?>" alt="<?php echo JText::_('Download') ?>" style="height: 24px; padding: 5px; vertical-align: middle;" />
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
    	        ?>