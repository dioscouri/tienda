<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

<p><?php echo JText::_( "This report displays the quantity of each product that was ordered during a selected time period." ); ?></p>

<div class="note">
	<?php echo JText::_("Select Product"); ?>:
	<?php echo TiendaSelect::product(@$state->filter_productid ? $state->filter_productid : 'product_name', 'product_name', $attribs, $idtag, $allowAny, $allowNone, $title = 'Select Product', $title_none = 'No Parent', $enabled = null ); ?>
	<br/><br/>
	<?php echo JText::_("Select Date Range"); ?>:	
	<?php $attribs = array('class' => 'inputbox', 'size' => '1'); ?>	
	<?php echo TiendaSelect::reportrange( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?>	
	<span class="label"><?php echo JText::_("From"); ?>:</span>
	<?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
	<span class="label"><?php echo JText::_("To"); ?>:</span>
	<?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
	<input type="hidden" name="filter_datetype" value="created" />
</div>