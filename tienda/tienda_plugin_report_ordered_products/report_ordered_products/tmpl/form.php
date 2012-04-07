<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

<p><?php echo JText::_('COM_TIENDA_THIS_REPORT_DISPLAYS_ORDERED_PRODUCTS'); ?></p>

<div class="note">
	<?php echo JText::_('COM_TIENDA_ENTER_PRODUCT_NAME'); ?>:
	<input type="text" name="filter_product_name" id="filter_product_name" value="<?php echo @$state->filter_product_name; ?>" style="width: 250px;" />
	<br /><br />
	<?php echo JText::_('COM_TIENDA_MANUFACTURER'); ?>:
	<?php echo TiendaSelect::manufacturer( @$state->filter_manufacturer_id, 'filter_manufacturer_id', array('class' => 'inputbox', 'size' => '1'), null, true ) ?>
	<br/><br/>
	<?php echo JText::_('COM_TIENDA_SELECT_DATE_RANGE'); ?>:	
	<?php $attribs = array('class' => 'inputbox', 'size' => '1'); ?>	
	<?php echo TiendaSelect::reportrange( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?>	
	<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
	<?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
	<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
	<?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
</div>