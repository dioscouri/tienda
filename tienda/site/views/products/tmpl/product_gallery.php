<?php
defined('_JEXEC') or die('Restricted access');

$gallery_data = $this->gallery_data;
if ( $gallery_data->show_gallery )
{
	?>

<div class="dsc-wrap product_gallery" id="product_gallery">
	<div id="product_gallery_header" class="tienda_header dsc-wrap">
		<span><?php echo JText::_('COM_TIENDA_IMAGES'); ?> </span>
	</div>
	<?php
	$i = 1;
	foreach ( $gallery_data->images as $image )
	{
	    $src = $gallery_data->uri . $image;
	    if (JFile::exists( Tienda::getPath( 'products_thumbs' ) . "/" . $image )) {
	        $src = $gallery_data->uri . "thumbs/" . $image;
	    }
		?>
    	<div class="dsc-wrap product_gallery_thumb" id="product_gallery_thumb_<?php echo $i;?>">
    	<?php 
    		echo TiendaUrl::popup( $gallery_data->uri . $image, '<img src="' . $src . '" alt="' . $gallery_data->product_name . '" />', array( 'update' => false, 'img' => true ) ); ?>
    	</div>
    	<?php
    	$i++;
	}
	?>
</div>
	<?php
}
