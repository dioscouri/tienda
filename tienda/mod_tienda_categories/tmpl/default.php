<?php defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaSelect', 'library.select' );

$document = JFactory::getDocument();
$document->addStyleSheet( JURI::root(true).'/modules/mod_tienda_categories/tmpl/tienda_categories.css'); ?>

<ul id="tienda_categories_mod">
<?php foreach ($items as $item) : ?>
	<li class="level<?php echo $item->level?>">
		<a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$item->category_id.$item->slug ); ?>"><?php echo $item->category_name; ?></a>
	</li>
<?php endforeach; ?>
</ul>