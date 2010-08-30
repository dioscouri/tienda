<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; 
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_( 'Name' ); ?>:
					</td>
					<td>
						<input name="name" id="name" value="<?php echo @$row->name; ?>" size="48" maxlength="250" type="text" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_( 'Ordering' ); ?>:
					</td>
					<td>
						<input name="ordering" id="ordering" value="<?php echo @$row->ordering; ?>" size="48" maxlength="250" type="text" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_enabled">
						<?php echo JText::_( 'Enabled' ); ?>:
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'published', '', @$row->published ) ?>
					</td>
				</tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->id; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>