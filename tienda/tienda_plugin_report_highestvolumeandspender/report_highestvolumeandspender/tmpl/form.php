<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

    <p><?php echo JText::_( "THIS REPORTS ON HIGHEST VOLUME AND BIGGEST SPENDERS" ); ?></p>
<div>
<table class="adminlist">
	<thead>
		<tr>
            <th style="text-align: center;" class="key">
                <?php echo JText::_("DATE RANGE"); ?>
            </th>
            <th style="text-align: center;" class="key">
                <?php echo JText::_("TOTAL NO. OF PURCHASES"); ?>
            </th> 
            <th style="text-align: center;" class="key">
                <?php echo JText::_("TOTAL AMOUNT SPENT"); ?>
            </th> 
		</tr>
		<tr>
			<th class="key" style="text-align: center; width:500px;">				
				<span class="label"><?php echo JText::_("From"); ?>:</span>
               	<?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' , array('onchange' => 'javascript:submitbutton(\'view\').click;')); ?>
				 <span class="label"><?php echo JText::_("To"); ?>:</span>
				 <?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?> 				 
			</th>
			<th class="key">
					<span class="label"><?php echo JText::_("From"); ?>:</span> <input id="filter_total_from" name="filter_total_from" value="<?php echo @$state->filter_total_from; ?>" size="5" class="input" />
	   				<span class="label"><?php echo JText::_("To"); ?>:</span> <input id="filter_total_to" name="filter_total_to" value="<?php echo @$state->filter_total_to; ?>" size="5" class="input" />
			</th>
			<th class="key" style="text-align: center;">
				<span class="label"><?php echo JText::_("From"); ?>:</span> <input id="filter_total_from" name="filter_total_from" value="<?php echo @$state->filter_total_from; ?>" size="5" class="input" />
	   			<span class="label"><?php echo JText::_("To"); ?>:</span> <input id="filter_total_to" name="filter_total_to" value="<?php echo @$state->filter_total_to; ?>" size="5" class="input" />
			</th>
		</tr>		
	</thead>
</table>
   </div>