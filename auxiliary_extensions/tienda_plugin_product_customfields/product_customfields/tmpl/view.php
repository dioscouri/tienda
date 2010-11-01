<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $custom_fields = @$vars->custom_fields; ?>
<?php $custom_fields_id = @$vars->custom_fields_id; ?>
<?php $product_id = @$vars->product_id; ?>
<?php $index = @$vars->index; ?>
<?php $name = 'cartitem_customfields'; ?>
<?php $id = $name.'['.$index.']';?>

<!--Render the custom fields for display in the shopping cart -->
<?php if (!empty($custom_fields)) :?>
	<div id='<?php echo $name; ?>_<?php echo $product_id; ?>' class="<?php echo $name;?>">
		<table class="admintable" style="width: 100%;">
	    <?php
				foreach ($custom_fields as $custom_field): ?> 
		    	<tr>
		    		<td style="width: 100px; text-align: left;" class="key">
						<?php echo JText::_( $custom_field->title ); ?>:
					</td>
					<td>
						<?php echo JText::_( $custom_field->value ); ?>
		    		</td>
		    	</tr>        
		<?php	endforeach;?>
		</table>
		<input type="hidden" id="<?php echo $id;?>" name="<?php echo $id;?>" value="<?php echo $custom_fields_id;?>" />		
	</div>
<?php endif;?>

