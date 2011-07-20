<?php defined('_JEXEC') or die('Restricted access'); ?>
	        
	        
<div class="tienda_header">
	<span><?php echo JText::_("Information"); ?></span>
</div>
	  
		<table border="0">  
				<?php foreach (@$vars->fields as $field): ?>	            
	            
	                <tr>
	                    <td style="vertical-align: top; width: 100px; text-align: left;" class="key">
	                        <?php echo JText::_( $field['attribute']->eavattribute_label ); ?>:
	                    </td>
	                    <td>
	                        <?php
	                    		Tienda::load('TiendaHelperEav', 'helpers.eav');
	                    		echo TiendaHelperEav::showField($field['attribute'], $field['value']);
	                    	?>
	                    </td>
	                </tr>
	            <?php endforeach; ?>    
	                
         </table>
	        