<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this -> form; ?>

<form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" class="adminform" name="adminForm" >
	<fieldset>
		<div class="header icon-48-css" style="float: left;">
			<?php echo JText::_('Manage Addresses') . ": " . $this -> type . '_' . $this -> filename . '.css';?>
		</div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<table class="toolbar">
				<tr>
					<td align="center">
					<a onclick="javascript:submitbutton('savecss'); return false;" href="#" >
					<span class="icon-32-save" title="<?php echo JText::_('Save', true);?>"></span><?php echo JText::_('Save');?>
					</a>
					</td>
				</tr>
			</table>
		</div>
	</fieldset>
	<textarea style="width:100%" rows="25" name="csscontent" >
		<?php echo $this -> content;?>
	</textarea>
	<input type="hidden" name="file" value="<?php echo $this -> type . '_' . $this -> filename;?>" />
	<input type="hidden" name="task" value="savecss" />
</form>