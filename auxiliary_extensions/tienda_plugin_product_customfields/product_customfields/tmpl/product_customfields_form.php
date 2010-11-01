<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $custom_fields = @$vars->custom_fields; ?>

<!--Render the custom fields based on the specified data type-->
<!--for required fields, register the field for form validation-->
<?php if (!empty($custom_fields)) :?>
	<div id='product_custom_fields_<?php echo $item->product_id; ?>' class="product_customfields">
		<fieldset>
			<legend><?php echo JText::_( "Details" ); ?></legend>
			<table class="admintable" style="width: 100%;">
		    <?php
					foreach ($custom_fields as $custom_field): ?> 
			    	<tr>
			    		<td style="width: 100px; text-align: right;" class="key">
							<?php echo JText::_( $custom_field->title ); ?>:
						</td>
						<td>
							<?php  	
							switch($custom_field->datatype)
					    	{
					    		case 'date':
									echo JHTML::calendar( '', $custom_field->id, $custom_field->id, $custom_field->typeformat);
					    			break;
					    		case 'text':
					    			echo '<input id="'.$custom_field->id.'" name="'.$custom_field->id.'" type="text" />';
					    			break;
					    		case 'textarea':
					    			echo '<textarea id="'.$custom_field->id.'" name="'.$custom_field->id.'"></textarea>';
					    			break;
					    	}
					    	?>	
			    		</td>
			    	</tr>        
			<?php	endforeach;?>
			</table>
		</fieldset>
		<input type="hidden" id="hasCustomFields" name="hasCustomFields" value="1" />
	</div>
<?php endif;?>