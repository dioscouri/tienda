<?php 
	defined('_JEXEC') or die('Restricted access'); 
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); 
 	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); 
 	JHTML::_('script', 'tienda_checkout.js', 'media/com_tienda/js/'); 
	$order_link = @$this->order_link;
	$plugin_html = @$this->plugin_html;
?>

<div class='componentheading'>
    <span><?php echo JText::_( "Checkout Results" ); ?></span>
</div>

<?php echo $plugin_html; ?>

<div class="note">
	<a href="<?php echo JRoute::_($order_link); ?>">
        <?php echo JText::_( "Click Here to View and Print an Invoice" ); ?>
	</a>
</div>
