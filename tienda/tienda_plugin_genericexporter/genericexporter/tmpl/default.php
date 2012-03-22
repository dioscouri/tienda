<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda_admin.css', 'media/com_tienda/css/');?>
<?php $models = @$vars->models;?>
<?php $types = @$vars->types;?>
<h3><?php echo JText::_('PLEASE PROVIDE THE INFO BELOW TO PROCESS THE EXPORT'); ?></h3>

<form enctype="multipart/form-data" name="adminForm" method="post" action="index.php?option=com_tienda&task=doTask&element=genericexporter&elementTask=filters">
	<table class="adminlist">
		<tbody>
			<tr>
				<th style="width: 25%;">
					<?php echo JText::_('DATA TO BE EXPORTED');?>
				</th>
				<td>
					<?php $list = array();?>
					<?php foreach($models as $model):?>
					<?php $list[] = JHTML::_('select.option',  $model[0], ucfirst($model[1]) );?>
					<?php endforeach;?>
					<?php echo JHTMLSelect::genericlist($list, 'model', array('class' => 'inputbox', 'size' => '1'), 'value', 'text');?>				
				</td>				
			</tr>
			<tr>
				<th style="width: 25%;">
					<?php echo JText::_('EXPORT TYPE')?>
				</th>
				<td>
					<?php $list = array();?>
					<?php foreach($types as $type):?>
					<?php $list[] = JHTML::_('select.option',  $type, $type );?>
					<?php endforeach;?>
					<?php echo JHTMLSelect::genericlist($list, 'type', array('class' => 'inputbox', 'size' => '1'), 'value', 'text');?>				
				</td>								
			</tr>
		</tbody>	
	</table>	
</form>