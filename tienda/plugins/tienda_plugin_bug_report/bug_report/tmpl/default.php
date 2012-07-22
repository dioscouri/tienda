<?php defined('_JEXEC') or die('Restricted access'); ?>


<?php 
if(version_compare(JVERSION,'1.6.0','ge')) { ?>
	<a target="_blank" href="http://projects.dioscouri.com/projects/tienda/"><?php echo JText::_('COM_TIENDA_SUBMIT_BUG'); ?></a>
<?php } else {?>	
	<a href="<?php echo JRoute::_( 'index.php?option=com_tienda&task=doTask&element=bug_report&elementTask=submitBug' ); ?>"><?php echo JText::_('COM_TIENDA_SUBMIT_BUG'); ?></a>
	<?php
}
?>



