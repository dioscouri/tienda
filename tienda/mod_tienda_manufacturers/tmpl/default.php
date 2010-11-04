<?php defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaSelect', 'library.select' );

$document = JFactory::getDocument();
$document->addStyleSheet( JURI::root(true).'/modules/mod_tienda_manufacturers/tmpl/tienda_manufacturers.css'); ?>

<ul id="tienda_manufacturers_mod">
<?php foreach ($items as $item) : ?>
	<li class="level<?php echo $item->level?>">
		<a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&task=manufacturers&filter_manufacturer=".$item->manufacturer_id); ?>"><?php echo $item->manufacturer_name; ?></a>
     
	</li>
<?php endforeach; ?>
</ul>

