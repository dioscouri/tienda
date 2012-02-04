<?php 
	defined('_JEXEC') or die('Restricted access');
	Tienda::load('TiendaHelperEav', 'helpers.eav');	            
	foreach (@$vars->fields as $field):
		echo JText::_( $field['attribute']->eavattribute_label ); ?>:&nbsp;
		<?php echo TiendaHelperEav::showValue($field['attribute'], $field['value']); ?>
		<br/>
	<?php endforeach; ?>
<br/>