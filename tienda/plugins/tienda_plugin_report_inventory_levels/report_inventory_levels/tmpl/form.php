<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

<p><?php echo JText::_('COM_TIENDA_THIS_REPORTS_THE_INVENTORY_LEVELS'); ?></p>
<div class="note">
	
	<?php echo JText::_('COM_TIENDA_PRODUCT_NAME'); ?>:
	<input type="text" name="filter_name" id="filter_name" value="<?php echo @$state->filter_name; ?>" />&nbsp;&nbsp;&nbsp;
            
	<?php echo JText::_('COM_TIENDA_SELECT_DATE_RANGE'); ?>:
    <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'javascript:submitbutton(\'view\').click;'); ?>
    <?php echo TiendaSelect::reportrange( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?>
            <span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
            <?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
            <span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
            <?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
	&nbsp;&nbsp;&nbsp;
	<!--  <span><?php //echo JText::_('COM_TIENDA_SELECT_PRODUCT'); ?>:</span>
	<?php //echo TiendaSelect::product( @$state->filter_product_name, 'filter_product_name', $attribs, 'product', true ); ?>
	-->
</div>
