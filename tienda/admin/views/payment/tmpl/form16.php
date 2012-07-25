<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this -> form; ?>
<?php $row = @$this -> row; ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

  <fieldset class="adminform">
    <legend><?php echo JText::_('COM_TIENDA_BASIC_INFORMATION'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('COM_TIENDA_NAME'); ?>:
					</td>
					<td>
						<input name="name" id="name" value="<?php echo @$row -> name; ?>" size="48" maxlength="250" type="text" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('COM_TIENDA_ORDERING'); ?>:
					</td>
					<td>
						<input name="ordering" id="ordering" value="<?php echo @$row -> ordering; ?>" size="10" maxlength="250" type="text" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_enabled">
						<?php echo JText::_('COM_TIENDA_ENABLED'); ?>:
						</label>
					</td>
					<td>
					<?php echo JHTML::_('select.booleanlist', 'enabled', '', @$row -> enabled); ?>
					</td>
				</tr>
			</table>
			</fieldset>
			<fieldset class="adminform">
    		<legend><?php echo JText::_('COM_TIENDA_PARAMETERS'); ?></legend>
			<?php
			
		
 			$path = JPATH_SITE.'/plugins/'.$row->folder.'/'.$row->element.'/jform/'.$row->element.'.xml';
			$form = JForm::getInstance($row->element, $path);
		
			foreach($row->data as $k => $v) {
				$form->setValue($k, 'params',$v);
			}
		
			$fieldSets = $form->getFieldsets();
			foreach ($fieldSets as $name => $fieldSet) :
			?>
		<?php $hidden_fields = ''; ?>
		<table>
			<?php foreach ($form->getFieldset($name) as $field) : ?>
			<?php if (!$field->hidden) : ?>
			<tr><td class="dsc-key"><?php echo $field -> label; ?></td> <td class="dsc-value"><?php echo $field -> input; ?></td></tr>
			<?php else : $hidden_fields.= $field->input; ?>
			<?php endif; ?>
			<?php endforeach; ?>
		</table>
		<?php echo $hidden_fields; ?>
	
		<?php endforeach; ?>
		<?php ?>
	
			
			</fieldset>
		
	
			<input type="hidden" name="extension_id" value="<?php echo @$row -> extension_id; ?>" />
	        <input type="hidden" name="type" value="plugin" /> 
			<input type="hidden" name="task" value="" />
			
	
</form>