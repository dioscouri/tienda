<?php defined('_JEXEC') or die('Restricted access'); ?>
	        
	        
<div class="tienda_header">
	<span><?php echo JText::_("Informations"); ?></span>
</div>
	  
		<table border="0">  
				<?php foreach (@$vars->fields as $field): ?>	            
	            
	                <tr>
	                    <td style="vertical-align: top; width: 100px; text-align: left;" class="key">
	                        <?php echo JText::_( $field['label'] ); ?>:
	                    </td>
	                    <td>
	                        <?php echo $field['value'] ?>
	                    </td>
	                </tr>
	            <?php endforeach; ?>    
	                
         </table>
	        