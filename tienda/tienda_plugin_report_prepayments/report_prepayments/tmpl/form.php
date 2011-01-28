<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

    <p><?php echo JText::_( "THIS REPORTS ON PRE-PAYMENTS PRODUCTS" ); ?></p>
    <div class="note">
    	<?php echo JText::_("ID"); ?>:    	
				<span class="label"><?php echo JText::_("From"); ?>:</span> <input id="filter_id_from" name="filter_id_from" value="<?php echo @$state->filter_id_from; ?>" size="5" class="input" />
				<span class="label"><?php echo JText::_("To"); ?>:</span> <input id="filter_id_to" name="filter_id_to" value="<?php echo @$state->filter_id_to; ?>" size="5" class="input" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
    	<?php echo JText::_("DATE OF ORDER"); ?>:
				<span class="label"><?php echo JText::_("From"); ?>:</span>
               	<?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
				 <span class="label"><?php echo JText::_("To"); ?>:</span>
				 <?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
 				 <span class="label"><?php echo JText::_("Type"); ?>:</span>
                 <?php echo TiendaSelect::datetype( @$state->filter_datetype, 'filter_datetype', '', 'datetype' ); ?>
		<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
	    <?php echo JText::_("CUSTOMER"); ?>:
	   	<input id="filter_user" name="filter_user" value="<?php echo @$state->filter_user; ?>" size="25"/>
	  <span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
	  <?php echo JText::_("TOTAL"); ?>:
	   <span class="label"><?php echo JText::_("From"); ?>:</span> <input id="filter_total_from" name="filter_total_from" value="<?php echo @$state->filter_total_from; ?>" size="5" class="input" />
	   <span class="label"><?php echo JText::_("To"); ?>:</span> <input id="filter_total_to" name="filter_total_to" value="<?php echo @$state->filter_total_to; ?>" size="5" class="input" />
	</div>