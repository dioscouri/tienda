<?php	defined('_JEXEC') or die('Restricted access');?>
<?php	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');?>
<?php	JHTML::_('script', 'installsampledata.js', 'plugins/tienda/tool_installsampledata/includes/');?>
<?php	JHTML::_('stylesheet', 'installsampledata.css', 'plugins/tienda/tool_installsampledata/includes/');?>
<?php	$state = @$vars->state;?>
<?php	echo @$vars->token;?>
<p>
	<?php	echo JText::_('THIS TOOL INSTALL SAMPLE DATA TO TIENDA');?>
</p>
<div class="note">
	<span style="float: right; font-size: large; font-weight: bold;">
		<?php	echo JText::_('STEP ONE OF THREE');?>
	</span>
	<p>
		<?php	echo JText::_('Please provide the requested information.');?>
	</p>
</div>
<fieldset>
	<?php
	$options = array();
	$options[] = JHTML::_('select.option', 'electronic', JText::_('ELECTRONIC STORE'));
	$options[] = JHTML::_('select.option', 'clothing', JText::_('CLOTHING STORE'));
	?>
	<table class="admintable">
		<tr id="sampledataupload" >
			<td width="100" align="right" class="key">
			<?php	echo JText::_('File');?>: *
			</td>
			<td>
			<input type="file" name="file" id="file" size="48" value="<?php	echo @$state->file;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
			<?php	echo JText::_('Install Default Data');?>:
			</td>
			<td>
			<input type="checkbox" name="install_default" id="install_default" onclick="javascript:showSample();" />
			<?php  echo JHTML::_('select.genericlist', $options, 'sampledata', 'class="inputbox" style="display:none;"', 'value', 'text', 'electronic', 'sampledatatype');?>
			</td>
		</tr>
	</table>
</fieldset>
