<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php Tienda::load('TiendaHelperEav', 'helpers.eav'); ?>

	            
	            <?php foreach (@$vars->fields as $field): ?>
	               
	                    <b><?php echo JText::_( $field['attribute']->eavattribute_label ); ?>:</b>&nbsp;                
	                    	<?php
	                    		echo $field['value'];
	                    	?>
	            <?php endforeach; ?>    <br/>