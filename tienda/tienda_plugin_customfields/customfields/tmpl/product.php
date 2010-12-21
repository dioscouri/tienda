<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $tabs = @$vars->tabs; ?>
<?php $row = @$vars->row; ?>
<?php
// Tab
    echo $tabs->startPanel( JText::_( 'Custom Fields' ), "panel_custom_fields"); 
    ?>
	        <div style="clear: both;"></div>
	        
	        
	            <fieldset>
	            <legend><?php echo JText::_( "Custom Fields" ); ?></legend>
	            <table class="admintable" style="width: 100%;">
	            
	            <?php foreach (@$vars->fields as $field): ?>
	                <tr>
	                    <td style="vertical-align: top; width: 100px; text-align: right;" class="key">
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
	            </fieldset>
	       
	
	        <?php
	        // fire plugin event here to enable extending the form
	        JDispatcher::getInstance()->trigger('onDisplayProductFormCustomFields', array( $row ) );                    
	        ?>
	        
	        <div style="clear: both;"></div>
	        
	    <?php 
	    echo $tabs->endPanel();
	    ?>