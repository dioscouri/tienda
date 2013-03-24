<?php
	defined('_JEXEC') or die('Restricted access');
	JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
	JHTML::_('script', 'joomla.javascript.js', 'includes/js/');
	Tienda::load( 'TiendaGrid', 'library.grid' );
	$items = @$this->items;
	$state = @$this->state;
	Tienda::load( "TiendaHelperRoute", 'helpers.route' );
	$router = new TiendaHelperRoute();
	Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
	$menu = TiendaMenu::getInstance( @$this->submenu );
?>


<div>
<?php foreach($items as $item) : ?>
	<?php var_dump($item);
?>
<div>
	<a href="<?php echo $item->link; ?>"><?php echo $item->name; ?></a>
	

<?php endforeach; ?>
</div>
