<?php
	defined('_JEXEC') or die('Restricted access');
	Tienda::load('TiendaHelperEav', 'helpers.eav');
?>

<div class="tienda_header">
	<span><?php echo JText::_("Information"); ?></span>
</div>
<div class="tienda_custom_fields">
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
</div>	        