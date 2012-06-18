<?php defined('_JEXEC') or die('Restricted access'); ?>

	<?php
		$img_file = "dioscouri_logo_transparent.png";
		$img_path = "../media/com_tienda/images";

		JPluginHelper::importPlugin('tienda');
		$dispatcher =& JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'onGetFooter', array() );
		
		$html = implode('', $results);
		echo $html;
		
		$url = "http://www.dioscouri.com/";
		if ($amigosid = Tienda::getInstance()->get( 'amigosid', '' ))
		{
			$url .= "?amigosid=".$amigosid;
		}
	?>

	<table style="margin-bottom: 5px; width: 100%; border-top: thin solid #e5e5e5;">
	<tbody>
	<tr>
		<td style="text-align: left; width: 33%;">
			<a href="<?php echo $url; ?>" target="_blank"><?php echo JText::_('COM_TIENDA_DIOSCOURICOM_SUPPORT_CENTER'); ?></a>
			<br/>
			<a href="http://twitter.com/dioscouri" target="_blank"><?php echo JText::_('COM_TIENDA_FOLLOW_US_ON_TWITTER'); ?></a>
			<br/>
			<a href="http://extensions.joomla.org/extensions/owner/dioscouri" target="_blank"><?php echo JText::_('COM_TIENDA_LEAVE_JED_FEEDBACK'); ?></a>
			<br/>
			<?php echo $this->extraHtml; ?>
		</td>
		<td style="text-align: center; width: 33%;">
			<?php echo JText::_('COM_TIENDA_TIENDA'); ?>: <?php echo JText::_('COM_TIENDA_TIENDA_DESC'); ?>
			<br/>
			<?php echo JText::_('COM_TIENDA_COPYRIGHT'); ?>: <?php echo Tienda::getCopyrightYear(); ?> &copy; <a href="<?php echo $url; ?>" target="_blank">Dioscouri Design</a>
			<br/>
			<?php echo JText::_('COM_TIENDA_VERSION'); ?>: <?php echo Tienda::getVersion(); ?>
			<br/>
			<?php echo sprintf( JText::_('COM_TIENDA_PHP_VERSION_LINE'), Tienda::getMinPhp(), Tienda::getServerPhp() );?>
		</td>
		<td style="text-align: right; width: 33%;">
			<a href="<?php echo $url; ?>" target="_blank"><img src="<?php echo $img_path."/".$img_file;?>"></img></a>
		</td>
	</tr>
	</tbody>
	</table>
