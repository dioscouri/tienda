<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $custom_fields = @$vars->custom_fields; ?>

<!--Render the custom fields based on the specified data type-->
<!--for required fields, register the field for form validation-->
<?php if (!empty($custom_fields)) :?>
	<div id='product_custom_fields_<?php echo $vars->product_id; ?>' class="product_customfields">

	    <?php foreach ($custom_fields as $custom_field): ?>
            <div class="tienda_customfield">
	    	<span class="title">
				<?php echo JText::_( $custom_field->title ); ?>:
			</span>
			<?php  	
			switch($custom_field->datatype)
	    	{
	    		case 'date':
					echo JHTML::calendar( '', $custom_field->id, $custom_field->id, $custom_field->typeformat);
	    			break;
	    		case 'text':
	    			echo '<input id="'.$custom_field->id.'" name="'.$custom_field->id.'" type="text" />';
	    			break;
	    		case 'file':
	    			echo '<input id="'.$custom_field->id.'" name="'.$custom_field->id.'" type="file" />';
	    			break;					    			
	    		case 'textarea':
	    			echo '<textarea id="'.$custom_field->id.'" name="'.$custom_field->id.'"></textarea>';
	    			break;
	    	}
	    	?>
	    	</div>
		<?php endforeach; ?>

		<input type="hidden" id="hasCustomFields" name="hasCustomFields" value="1" />
	</div>
<?php endif;?>

<div style="clear: both;"></div>