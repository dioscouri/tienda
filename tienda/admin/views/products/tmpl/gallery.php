<?php defined('_JEXEC') or die('Restricted access'); 
$images = @$this->images;
$path = @$this->url; 
JLoader::import( 'com_tienda.helpers.product', JPATH_ADMINISTRATOR.DS.'components' );
JLoader::import( 'com_tienda.library.url', JPATH_ADMINISTRATOR.DS.'components' );
?>
<div id="gallery">

	<?php 
	foreach($images as $i){ 
		echo TiendaUrl::popup( $path.$i, '<img src="'.$path."thumbs/".$i.'" />', array('update' => false, 'img' => true));	
	}
	?>

</div>