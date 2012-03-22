<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$url = "http://www.dioscouri.com/";
if ($amigosid = TiendaConfig::getInstance()->get( 'amigosid', '' ))
{
    $url .= "?amigosid=".$amigosid; 
}
?>

<p align="center">
	<?php echo JText::_('Powered by')." <a href='{$url}' target='_blank'>".JText::_('Tienda Ecommerce')."</a>"; ?>
</p>

