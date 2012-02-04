<?php
	defined('_JEXEC') or die('Restricted access');
	Tienda::load('TiendaHelperEav', 'helpers.eav');
	foreach (@$vars->fields as $field):
?>
	<b><?php echo JText::_( $field['attribute']->eavattribute_label ); ?>:</b>&nbsp;                
	<?php echo $field['value']; ?> <br/>
<?php endforeach; ?> <br/>