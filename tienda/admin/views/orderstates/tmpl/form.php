<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<label for="name">
						<?php echo JText::_( 'Name' ); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="name" id="name" size="48" maxlength="250" value="<?php echo @$row->name; ?>" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="id" value="<?php echo@$row->id?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>