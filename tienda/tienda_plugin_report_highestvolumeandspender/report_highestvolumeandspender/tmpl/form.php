<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<p><?php echo JText::_('COM_TIENDA_THIS_REPORTS_ON_HIGHEST_VOLUME_AND_BIGGEST_SPENDERS'); ?></p>
<div>
<table class="adminlist">
	<thead>
		<tr>
            <th style="text-align: center;" class="key">
                <?php echo JText::_('COM_TIENDA_DATE_RANGE'); ?>
            </th>
            <th style="text-align: center;" class="key">
                <?php echo JText::_('COM_TIENDA_TOTAL_NUMBER_OF_PURCHASES'); ?>
            </th> 
            <th style="text-align: center;" class="key">
                <?php echo JText::_('COM_TIENDA_TOTAL_AMOUNT_SPENT'); ?>
            </th>
		</tr>
		<tr>
			<th class="key" style="text-align: center; width:500px;">				
				<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
               	<?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
				 <span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
				 <?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?> 				 
			</th>
			<th class="key">
					<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span> <input id="filter_totalpurchase_from" name="filter_totalpurchase_from"  value="<?php echo @$state->filter_totalpurchase_from; ?>" size="5" class="input" />
	   				<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span> <input id="filter_totalpurchase_to" name="filter_totalpurchase_to" value="<?php echo @$state->filter_totalpurchase_to; ?>" size="5" class="input" />
			</th>
			<th class="key" style="text-align: center;">
				<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span> <input id="filter_totalspent_from" name="filter_totalspent_from" value="<?php echo @$state->filter_totalspent_from; ?>" size="5" class="input" />
	   			<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span> <input id="filter_totalspent_to" name="filter_totalspent_to" value="<?php echo @$state->filter_totalspent_to; ?>" size="5" class="input" />	   				
			</th>
		</tr>		
	</thead>
</table>
   </div>