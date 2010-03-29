<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; 
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

	<fieldset>
		<legend><?php echo JText::_('Language Information'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('Name')?>
					</td>
					<td>
						<?php echo $row->name; ?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<?php echo JText::_('Code'); ?>
					</td>
					<td>
						<?php echo $row->code; ?>
					</td>
				</tr>
			</table>
	</fieldset>
	<fieldset>
		<legend><?php echo JText::_( 'Strings' ); ?></legend>
			<table class="admintable">
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