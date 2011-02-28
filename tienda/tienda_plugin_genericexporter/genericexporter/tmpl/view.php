<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda_admin.css', 'media/com_tienda/css/');?>
<div class="downloadfile">
	<span class="downloadfile_image">
    	<a href="<?php echo @$vars->link?>">
        	<img src="<?php echo Tienda::getURL('images')."download.png"; ?>" alt="<?php echo JText::_('Download') ?>" style="height: 24px; padding: 5px; vertical-align: middle;" />
        </a>
    </span>            
   	<span class="downloadfile_link" style="vertical-align: middle;" >
        <a href="<?php echo @$vars->link;?>"><?php echo @$vars->name; ?></a>
    </span>
</div>

        