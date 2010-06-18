<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
$downloadItems = @$this->downloadItems;
$nondownloadItems = @$this->nondownloadItems;
?>

    <div class="productdesc">
       <div class="productdesctitle"><?php echo JText::_("Files"); ?></div>
        <?php
        $k = 0;         
        foreach ($downloadItems as $item): ?>
        <div class="productfile">
            <span class="productfile_image">
                <a href="<?php echo JRoute::_( 'index.php?option=com_tienda&view=products&task=downloadfile&format=raw&id='.$item->productfile_id."&product_id=".$this->product_id); ?>">
                    <img src="<?php echo Tienda::getURL('images')."download.png"; ?>" alt="<?php echo JText::_('Download') ?>" style="height: 24px; padding: 5px; vertical-align: middle;" />
                </a>
            </span>            
            <span class="productfile_link" style="vertical-align: middle;" >
                <a href="<?php echo JRoute::_( 'index.php?option=com_tienda&view=products&task=downloadfile&format=raw&id='.$item->productfile_id."&product_id=".$this->product_id); ?>"><?php echo $item->productfile_name; ?></a>
            </span>
        </div>
        <?php $k = 1 - $k; ?>           
        <?php endforeach; 
        
        foreach ($nondownloadItems as $item): ?>
        <div class="productfile">
            <span class="productfile_image">
                   <img src="<?php echo Tienda::getURL('images')."download.png"; ?>" alt="<?php echo JText::_('Download') ?>" style="height: 24px; padding: 5px; vertical-align: middle;" />
                           </span>            
            <span class="productfile_link" style="vertical-align: middle;" >
               <?php echo $item->productfile_name; ?>
            </span>
        </div>
        <?php $k = 1 - $k; ?>           
        <?php endforeach; ?> 
        
    </div>


