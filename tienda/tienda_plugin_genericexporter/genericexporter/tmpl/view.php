<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'tienda_admin.css', 'media/com_tienda/css/');?>
<?php $types = @$vars->types;?>
<h3><?php echo JText::_( "Please select the data you want to export below." ); ?></h3>

<div class="note">
<ul>
<?php foreach($types as $type):?>
	<li>
		<a href="index.php?option=com_tienda&task=doTask&element=generic_exporter&elementTask=viewcolumns&type=<?php echo $type;?>"><?php echo ucfirst($type);?></a>		
	</li>
<?php endforeach;?>
</ul>
</div>
        