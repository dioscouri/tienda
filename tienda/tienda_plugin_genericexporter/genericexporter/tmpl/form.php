<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda_admin.css', 'media/com_tienda/css/');?>
<?php $columns = @$vars->columns;?>
<h3><?php echo JText::_( "Please select the columns you want to export." ); ?></h3>
<form enctype="multipart/form-data" name="adminForm" method="post" action="index.php?option=com_tienda&task=doTask&element=generic_exporter&elementTask=doExport">
<div class="note">
<div style="border-bottom: 2px solid #CCC; padding: 5px;">
	<input name="toggle" type="checkbox" onclick="checkAll(<?php echo count($columns);?>);" value=""/><span class="label"><?php echo JText::_('Select/Not Select All');?></span>
</div>

<?php $i = 0?>
<?php foreach($columns as $column):?>
	<div style="padding: 5px;">
		<input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $column;?>" name="cid[]" id="cb<?php echo $i;?>">
		<span class="label"><?php echo $column;?></span>
	</div>	
	<?php $i++;?>
<?php endforeach;?>
<input type="button" value="Submit" onclick="javascript: document.adminForm.submit();" />
</div>
<input type="hidden" value="" name="boxchecked">
<input type="hidden" value="<?php echo JRequest::getVar('model');?>" name="model">
<input type="hidden" value="<?php echo JRequest::getVar('type');?>" name="type">
</form>
        