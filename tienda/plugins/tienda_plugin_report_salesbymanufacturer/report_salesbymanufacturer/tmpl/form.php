<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

<p><?php echo JText::_('COM_TIENDA_THIS_REPORT_DISPLAYS_THE_SALES_BY_EACH_MANUFACTURER_DURING_A_SELECTED_TIME_PERIOD'); ?></p>

<div>
	<table class="adminlist">
	<thead>   
		<tr>
			<th style="text-align: center;" class="key">
                <?php echo JText::_('COM_TIENDA_SELECT_DATE_RANGE'); ?>
            </th>
            <th style="text-align: center;" class="key">
                <?php echo JText::_('COM_TIENDA_MANUFACTURER_NAME'); ?>
            </th>
        </tr>
        <tr>
        	<th align="left" style="text-align: left;" class="key">
        		<?php $attribs = array('class' => 'inputbox', 'size' => '1'); ?>	
				<?php echo TiendaSelect::reportrange( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?>	
				<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
				<?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
				<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
				<?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
        <span class="label"><?php echo JText::_('COM_TIENDA_TYPE'); ?>:</span>
       <?php echo TiendaSelect::subdatetype( @$state->filter_datetype, 'filter_datetype', '', 'filter_datetype' ); ?>
        	</th>
        	<th class="key">
        		<input type="text" name="filter_manufacturer_name" id="filter_manufacturer_name" value="<?php echo @$state->filter_manufacturer_name; ?>" style="width: 250px;" />
        	</th>
        </tr>
    </thead>
	</table>
</div>