<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

<p><?php echo JText::_('COM_TIENDA_THIS_REPORT_LISTS_ALL_USERS_WHO_OPENED_A_NEW_SUBSCRIPTION_DURING_THE_SELECTED_TIME_PERIOD'); ?></p>



<div>
	<table class="adminlist">
	<thead>   
		<tr>
			<th style="text-align: center; width: 485px;" class="key">
				<?php echo JText::_('COM_TIENDA_SELECT_DATE_RANGE'); ?>
			</th>
			<th style="text-align: left;" class="key">
				<?php echo JText::_('COM_TIENDA_ORDER_STATE'); ?>
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
				<input type="hidden" name="filter_datetype" value="created" />
			</th>
			<th align="left" style="text-align: left;" class="key">
				<?php 
					$attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();');
					echo TiendaSelect::orderstate(@$state->filter_orderstate, 'filter_orderstate', $attribs, 'order_state_id', true ); 
				?>
			</th>
		</tr>
    </thead>
	</table>
</div>
        