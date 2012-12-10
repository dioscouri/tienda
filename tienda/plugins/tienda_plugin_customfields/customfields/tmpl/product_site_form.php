<?php 
defined('_JEXEC') or die('Restricted access');
Tienda::load('TiendaHelperEav', 'helpers.eav');
foreach (@$vars->fields as $field): ?>	            
	<div class="control-group pao cf_div_<?php echo $field['attribute']->eavattribute_alias; ?>">
		<label for="<?php echo $field['attribute']->eavattribute_alias; ?>" class="cf_span_<?php echo $field['attribute']->eavattribute_alias; ?>">
			<?php echo JText::_( $field['attribute']->eavattribute_label ); ?>:
		</label>
		<?php echo TiendaHelperEav::showField($field['attribute'], $field['value']); ?>
	</div>
<?php endforeach; ?>    