<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; 
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

	<fieldset>
		<legend><?php echo JText::_('COM_TIENDA_LANGUAGE_INFORMATION'); ?></legend>
			<table class="table table-striped table-bordered">
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('COM_TIENDA_NAME')?>
					</td>
					<td>
						<?php echo $row->name; ?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('COM_TIENDA_CODE'); ?>
					</td>
					<td>
						<?php echo $row->code; ?>
					</td>
				</tr>
			</table>
	</fieldset>
	<fieldset>
		<legend><?php echo JText::_('COM_TIENDA_STRINGS'); ?></legend>
			<table class="table table-striped table-bordered">
			<?php foreach($row->strings['strings'] as $k => $v){ ?>
				<tr>
					<td width="100" align="right" class="key">
						<?php echo $k; ?>:
					</td>
					<td>
					    <textarea name="<?php echo $k; ?>" id="<?php echo $k; ?>" rows="8" cols="50"><?php echo $v; ?></textarea>
					</td>
				</tr>
			<?php } ?>
			</table>
	</fieldset>
	
	<input type="hidden" name="id" value="<?php echo @$row->code; ?>" />
	<input type="hidden" name="task" value="" />
</form>