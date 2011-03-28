<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php Tienda::load('TiendaHelperEav', 'helpers.eav'); ?>
	            
	            <?php foreach (@$vars->fields as $field): ?>
	               
	                    <?php echo JText::_( $field['attribute']->eavattribute_label ); ?>:&nbsp;                
	                    	<?php
	                    		echo TiendaHelperEav::showValue($field['attribute'], $field['value']);
	                    	?> <br/>
	            <?php endforeach; ?>  <br/>  