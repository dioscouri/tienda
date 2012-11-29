<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
$url = "http://www.dioscouri.com/";
if ($amigosid = Tienda::getInstance()->get( 'amigosid', '' ))
{
    $url .= "?amigosid=".$amigosid; 
}
?>

<?php if (Tienda::getInstance()->get('show_linkback')) : ?>
<p align="center">
	<?php echo JText::_('COM_TIENDA_POWERED_BY')." <a href='{$url}' target='_blank'>".JText::_('COM_TIENDA_TIENDA_ECOMMERCE')."</a>"; ?>
</p>
<?php endif; ?>
