<?php
	defined('_JEXEC') or die('Restricted access');
	$tabs = @$vars->tabs;
	$row = @$vars->row;
	// Tab

	Tienda::load('TiendaHelperEav', 'helpers.eav');
?>

 <div class="tab-pane" id="customfields">

		<div class="tienda_custom_fields">
			<fieldset>
			<legend><?php echo JText::_('COM_TIENDA_CUSTOM_FIELDS'); ?></legend>
<?php foreach (@$vars->fields as $field): ?>
			<div class="tienda_custom_fields_line">
				<div class="tienda_custom_fields_key">
					<span><?php echo JText::_( $field['attribute']->eavattribute_label ); ?>:</span>
				</div>
				<div class="tienda_custom_fields_value">
					<span><?php echo TiendaHelperEav::showField($field['attribute'], $field['value']); ?></span>
				</div>
			</div>
<?php endforeach; ?>    
			</fieldset>
		</div>
<?php
	// fire plugin event here to enable extending the form
	JDispatcher::getInstance()->trigger('onDisplayProductFormCustomFields', array( $row ) );                    
?>
	  <div style="clear: both;"></div>
</div>