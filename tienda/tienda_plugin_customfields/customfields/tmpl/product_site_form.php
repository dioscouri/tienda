<?php 
defined('_JEXEC') or die('Restricted access');
Tienda::load('TiendaHelperEav', 'helpers.eav');
foreach (@$vars->fields as $field): ?>	            
	<div class="pao cf_div_<?php echo $field['attribute']->eavattribute_alias; ?>">
		<span class="cf_span_<?php echo $field['attribute']->eavattribute_alias; ?>">
			<?php echo JText::_( $field['attribute']->eavattribute_label ); ?>:
		</span>                    
		<?php echo TiendaHelperEav::showField($field['attribute'], $field['value']); ?>
	</div>
<?php endforeach; ?>    