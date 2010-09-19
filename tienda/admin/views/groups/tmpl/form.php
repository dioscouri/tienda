<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row;
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_( 'Name' ); ?>:
					</td>
					<td>
						<input type="text" name="group_name" id="group_name" value="<?php echo @$row->group_name; ?>" size="48" maxlength="250" />
					</td>
				</tr>
				
				<tr>
    				<td style="width: 100px; text-align: right;" class="key">
    					<label for="group_description">
    					<?php echo JText::_( 'Description' ); ?>:
    					</label>
    				</td>
    				<td>
    					<?php $editor = &JFactory::getEditor(); ?>
    					<?php echo $editor->display( 'group_description',  @$row->group_description, '100%', '450', '100', '20' ) ; ?>
    				</td>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->group_id; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>