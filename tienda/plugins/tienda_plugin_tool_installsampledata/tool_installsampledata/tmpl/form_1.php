<?php	defined('_JEXEC') or die('Restricted access');?>
<?php	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php	$state = @$vars->state;?>
<?php	echo @$vars->token;?>
<p>
	<?php	echo JText::_('COM_TIENDA_THIS_TOOL_INSTALL_SAMPLE_DATA_TO_TIENDA');?>
</p>
<div class="note">
	<span style="float: right; font-size: large; font-weight: bold;">
		<?php	echo JText::_('COM_TIENDA_STEP_ONE_OF_THREE');?>
	</span>
	<p>
		<?php	echo JText::_('COM_TIENDA_PLEASE_PROVIDE_THE_REQUESTED_INFORMATION');?>
	</p>
</div>
<fieldset>
	<?php
	$options = array();
	$options[] = JHTML::_('select.option', 'electronic', JText::_('COM_TIENDA_ELECTRONIC_STORE'));
	$options[] = JHTML::_('select.option', 'clothing', JText::_('COM_TIENDA_CLOTHING_STORE'));
	?>
	<table class="admintable">
		<tr id="sampledataupload" >
			<td width="100" align="right" class="key">
			<?php	echo JText::_('COM_TIENDA_FILE');?>: *
			</td>
			<td>
			<input type="file" name="file" id="file" size="48" value="<?php	echo @$state->file;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
			<?php	echo JText::_('COM_TIENDA_INSTALL_DEFAULT_DATA');?>:
			</td>
			<td>
			<input type="checkbox" name="install_default" id="install_default" onclick="Dsc.showHideDiv('sampledata');" />
			<?php  echo JHTML::_('select.genericlist', $options, 'sampledata', 'class="inputbox" style="display:none;"', 'value', 'text', 'electronic', 'sampledatatype');?>
			</td>
		</tr>
	</table>
</fieldset>
