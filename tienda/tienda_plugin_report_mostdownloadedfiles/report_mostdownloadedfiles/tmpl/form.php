<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

    <p><?php echo JText::_( "THIS REPORTS ON MOST DOWNLOADED FILES" ); ?></p>
    <div class="note">
    	<?php echo JText::_("FILE NAME"); ?>:    	
			<input type="text" name="filter_file_name" id="filter_file_name" value="<?php echo @$state->filter_file_name; ?>" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
	  	<?php echo JText::_("PRODUCT NAME"); ?>:    	
			<input type="text" name="filter_product_name" id="filter_product_name" value="<?php echo @$state->filter_product_name; ?>" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
    	<?php echo JText::_("SELECT DOWNLOADS RANGE"); ?>:
	    	<span class="label"><?php echo JText::_("FROM"); ?>:</span>
	    	<?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
	    	<span class="label"><?php echo JText::_("TO"); ?>:</span>
	    	<?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
	</div>	