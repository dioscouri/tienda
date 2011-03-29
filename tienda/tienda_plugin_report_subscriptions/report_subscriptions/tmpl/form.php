<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

<p><?php echo JText::_( "This report lists all users who opened a new subscription during the selected time period." ); ?></p>



<div>
	<table class="adminlist">
	<thead>   
		<tr>
			<th style="text-align: center; width: 485px;" class="key">
				<?php echo JText::_("Select Date Range"); ?>
			</th>
			<th style="text-align: left;" class="key">
				<?php echo JText::_("Order State"); ?>
			</th>
		</tr>
		<tr>
			<th align="left" style="text-align: left;" class="key">
				<?php $attribs = array('class' => 'inputbox', 'size' => '1'); ?>
				<?php echo TiendaSelect::reportrange( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?>
				<span class="label"><?php echo JText::_("From"); ?>:</span>
				<?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
				<span class="label"><?php echo JText::_("To"); ?>:</span>
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
        