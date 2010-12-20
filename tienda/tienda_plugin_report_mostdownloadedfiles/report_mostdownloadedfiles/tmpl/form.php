<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

    <p><?php echo JText::_( "THIS REPORTS ON MOST DOWNLOADED FILES" ); ?></p>
    <div class="note">
    	<?php echo JText::_("FILE NAME"); ?>:    	
			<input type="text" name="filter" id="filter" value="<?php echo @$state->filter; ?>" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
	  	<?php echo JText::_("PRODUCT NAME"); ?>:    	
			<input type="text" name="filter" id="filter" value="<?php echo @$state->filter; ?>" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
    	<?php echo JText::_("SELECT DOWNLOADS RANGE"); ?>:
	    	<span class="label"><?php echo JText::_("FROM"); ?>:</span>
	   		<input type="text" name="filter_download_from" id="filter_download_from" value="<?php echo @$state->filter_download_from; ?>" />
	    	<span class="label"><?php echo JText::_("TO"); ?>:</span>
	   		<input type="text" name="filter_download_to" id="filter_download_to" value="<?php echo @$state->filter_download_to; ?>" />	  	
	</div>	