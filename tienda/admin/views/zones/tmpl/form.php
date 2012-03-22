<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row;
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<label for="zone_name">
						<?php echo JText::_('Name'); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="zone_name" id="zone_name" size="48" maxlength="250" value="<?php echo @$row->zone_name; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="code">
						<?php echo JText::_('Code'); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="code" id="code" size="10" maxlength="250" value="<?php echo @$row->code; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="country_id">
						<?php echo JText::_('COM_TIENDA_COUNTRY'); ?>:
						</label>
					</td>
					<td>
						<?php echo TiendaSelect::country( @$row->country_id, 'country_id' ); ?>
					</td>
				</tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->zone_id?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>