<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda_admin.css', 'media/com_tienda/css/');?>
<?php $filters = @$vars->filters;?>
<h3><?php echo JText::_( "PLEASE PROVIDE THE FILTERS FOR THE DATA EXPORT BELOW" ); ?></h3>
<form enctype="multipart/form-data" name="adminForm" method="post" action="index.php?option=com_tienda&task=doTask&element=genericexporter&elementTask=doExport">
<fieldset class="adminform">
	<legend><?php echo JText::_("FILTERS");?></legend>
	<table class="admintable">
		<tbody>
			<?php foreach($filters as $key=>$value):?>
			<tr>
				<td class="key">
					<label for="filter[<?php echo $key;?>]">
						<?php echo $value;?>
					</label>				
				</td>
				<td>
					<input type="text" value="" class="text_area" name="filter[<?php echo $key;?>]">
				</td>
			</tr>		
			<?php endforeach;?>		
		</tbody>
	</table>
	<br />
	<input type="button" value="Submit" onclick="javascript: document.adminForm.submit();" />
</fieldset>
<input type="hidden" value="" name="boxchecked">
<input type="hidden" value="<?php echo JRequest::getVar('model');?>" name="model">
<input type="hidden" value="<?php echo JRequest::getVar('type');?>" name="type">
</form>
        