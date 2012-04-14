<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<?php JHTML::_( 'script', 'tienda.js', 'media/com_tienda/js/' ); ?>
<?php JHTML::_( 'script', 'genericimporter.js', 'plugins/tienda/tool_genericimporter/media/' ); ?>
<?php echo @$this->vars->token; ?>

<p><?php echo JText::_('THIS TOOL HANDLES GENERIC IMPORT INTO TIENDA'); ?></p>

<div class="note">
	<span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_('COM_TIENDA_STEP_ONE_OF_THREE'); ?></span>
	<p><?php echo JText::_('PLEASE SELECT TYPE OF IMPORT FIRST'); ?></p>
</div>
    
<fieldset>
	<legend><?php echo JText::_('COM_TIENDA_BASIC_INFORMATION'); ?></legend>
	<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
			<?php echo JText::_('CHOOSE IMPORT'); ?>:
			</td>
			<td>
				<?php echo $this->import_select; ?>
			</td>
			<td></td>
		</tr>
	</table>    
</fieldset>   
<fieldset>
	<legend><?php echo JText::_('ADDITIONAL INFORMATION FOR IMPORT'); ?></legend>
	<div id="divAdditionalInfo">
		<?php echo $this->getHtmlStep( 1, 1 ); // get form 1 for step 1 ?>
	</div>
</fieldset>       