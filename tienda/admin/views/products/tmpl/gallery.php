<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('stylesheet', 'tienda.js', 'media/com_tienda/js/');  
$images = @$this->images;
$path = @$this->url; 
Tienda::load( "TiendaHelperProduct", 'helpers.product' );
Tienda::load( 'TiendaUrl', 'library.url' );
$product_id = JRequest::getInt('id', 0);
$update_parent = JRequest::getInt('update_parent');
if (!empty($update_parent))
{
    ?>
    <script type="text/javascript">
    window.parent.tiendaUpdateParentDefaultImage('<?php echo $product_id; ?>');
    </script>
    <?php
}
?>
<div id="gallery">
	<table border="0">
		<tr>
			
			<?php 
			foreach($images as $i){ 
				?>
				<td>
				<?php echo TiendaUrl::popup( $path.$i, '<img src="'.$path."thumbs/".$i.'" style="vertical-align: bottom;" />', array('update' => false, 'img' => true));?>
				</td>
				<?php 
			}
			?>
			
	</tr>
	<tr>
		
		<?php 
		foreach($images as $i){ 
		?>
		<td>
			<a href="index.php?option=com_tienda&controller=products&task=deleteImage&product_id=<?php echo $product_id?>&image=<?php echo $i; ?>"><?php echo JText::_('COM_TIENDA_DELETE'); ?></a><br />
			<a href="index.php?option=com_tienda&controller=products&task=setDefaultImage&product_id=<?php echo $product_id?>&image=<?php echo $i; ?>"><?php echo JText::_('Make Default'); ?></a>
		</td>
		<?php 	
		}
		?>
		
	</tr>
	</table>
</div>