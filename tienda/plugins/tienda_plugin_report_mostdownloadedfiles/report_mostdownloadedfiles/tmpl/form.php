<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

    <p><?php echo JText::_('COM_TIENDA_THIS_REPORTS_ON_MOST_DOWNLOADED_FILES'); ?></p>
    <div class="note">
    	<?php echo JText::_('COM_TIENDA_FILE_NAME'); ?>:    	
			<input type="text" name="filter_file_name" id="filter_file_name" value="<?php echo @$state->filter_file_name; ?>" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
	  	<?php echo JText::_('COM_TIENDA_PRODUCT_NAME'); ?>:    	
			<input type="text" name="filter_product_name" id="filter_product_name" value="<?php echo @$state->filter_product_name; ?>" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
    	<?php echo JText::_('COM_TIENDA_SELECT_DOWNLOADS_RANGE'); ?>:
	    	<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
	    	<?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
	    	<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
	    	<?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
	</div>	