<?php
defined('_JEXEC') or die('Restricted access');

$gallery_data = $this->gallery_data;
if ( $gallery_data->show_gallery )
{
	?>
<div class="reset"></div>
<div class="product_gallery" id="product_gallery">
	<div id="product_gallery_header" class="tienda_header">
		<span><?php echo JText::_('Images'); ?> </span>
	</div>
	<?php
	$i = 1;
	foreach ( $gallery_data->images as $image )
	{
		?>
	<div class="product_gallery_thumb" id="product_gallery_thumb_<?php echo $i;?>">
	<?php 
		echo TiendaUrl::popup( $gallery_data->uri . $image, '<img src="' . $gallery_data->uri . "thumbs/" . $image . '" alt="' . $gallery_data->product_name . '" />', array( 'update' => false, 'img' => true ) ); ?>
	</div>
	<?php
	$i++;
	}
	?>
	<div class="reset"></div>
</div>
	<?php
}
