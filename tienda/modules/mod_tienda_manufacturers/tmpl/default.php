<?php defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaSelect', 'library.select' );

$doc = JFactory::getDocument();
$doc->addStyleSheet( JURI::root(true).'/modules/mod_tienda_manufacturers/tmpl/tienda_manufacturers.css'); ?>

<ul id="tienda_manufacturers_mod">
<?php foreach ($items as $item) : ?>
	<li class="level<?php echo $item->level?>">
		<a href="<?php echo TiendaHelperRoute::manufacturer($item->manufacturer_id); ?>"><?php echo $item->manufacturer_name; ?></a>    
	</li>
<?php endforeach; ?>
</ul>

