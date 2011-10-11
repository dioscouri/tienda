<?php defined('_JEXEC') or die('Restricted access'); ?>
	        
	  
				<?php foreach (@$vars->fields as $field): ?>	            
	            
	               <div class="pao cf_div_<?php echo $field['attribute']->eavattribute_alias; ?>">
	               	<span class="cf_span_<?php echo $field['attribute']->eavattribute_alias; ?>">
	                        <?php echo JText::_( $field['attribute']->eavattribute_label ); ?>:
	                 </span>
	                    
	                        <?php
	                    		Tienda::load('TiendaHelperEav', 'helpers.eav');
	                    		echo TiendaHelperEav::showField($field['attribute'], $field['value']);
	                    	?>
	                    </div>
	            <?php endforeach; ?>    