<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

    <p><?php echo JText::_('THIS REPORTS ON DISPLAYING LIST OF USERS WITH ITEMS NOT PURCHASED'); ?></p>
    <div class="note">
    	<?php echo JText::_('USER NAME'); ?>:
			<input type="text" name="filter_name" id="filter_name" value="<?php echo @$state->filter_name; ?>" />&nbsp;&nbsp;&nbsp;
	    <?php echo JText::_('SELECT DATE RANGE'); ?>:
	    <?php $attribs = array('class' => 'inputbox', 'size' => '1'); ?>
	    <?php echo TiendaSelect::reportrange( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?>
	            <span class="label"><?php echo JText::_('From'); ?>:</span>
	            <?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
	            <span class="label"><?php echo JText::_('To'); ?>:</span>
	            <?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
	</div>