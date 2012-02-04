<?php
	defined('_JEXEC') or die('Restricted access');
	$tabs = @$vars->tabs;
	$row = @$vars->row;
	// Tab
	echo $tabs->startPanel( JText::_( 'Custom Fields' ), "panel_custom_fields"); 
	Tienda::load('TiendaHelperEav', 'helpers.eav');
?>
		<div class="tienda_custom_fields">
			<fieldset>
			<legend><?php echo JText::_( "Custom Fields" ); ?></legend>
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
<?php echo $tabs->endPanel(); ?>