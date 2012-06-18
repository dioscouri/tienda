<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$url = "http://www.dioscouri.com/";
if ($amigosid = Tienda::getInstance()->get( 'amigosid', '' ))
{
    $url .= "?amigosid=".$amigosid; 
}
?>

<p align="center">
	<?php echo JText::_('COM_TIENDA_POWERED_BY')." <a href='{$url}' target='_blank'>".JText::_('COM_TIENDA_TIENDA_ECOMMERCE')."</a>"; ?>
</p>

