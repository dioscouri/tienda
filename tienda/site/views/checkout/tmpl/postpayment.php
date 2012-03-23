<?php 
	defined('_JEXEC') or die('Restricted access'); 
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); 
 	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); 
 	JHTML::_('script', 'tienda_checkout.js', 'media/com_tienda/js/'); 
	$order_link = @$this->order_link;
	$plugin_html = @$this->plugin_html;
?>

<div class='componentheading'>
    <span><?php echo JText::_('COM_TIENDA_CHECKOUT_RESULTS'); ?></span>
</div>

<?php if( !TiendaConfig::getInstance()->get('one_page_checkout', '0') ) : ?>
<!-- Progress Bar -->
<?php echo $this->progress; ?>
<?php endif; ?>

<?php if (!empty($this->onBeforeDisplayPostPayment)) : ?>
    <div id='onBeforeDisplayPostPayment_wrapper'>
    <?php echo $this->onBeforeDisplayPostPayment; ?>
    </div>
<?php endif; ?>

<?php echo $plugin_html; ?>

<div class="note">
	<a href="<?php echo JRoute::_($order_link); ?>">
        <?php echo JText::_('COM_TIENDA_CLICK_HERE_TO_VIEW_AND_PRINT_AN_INVOICE'); ?>
	</a>
</div>

<?php foreach ($this->articles as $article) : ?>
    <div class="postpayment_article">
        <?php echo $article; ?>
    </div>    
<?php endforeach; ?>

<?php if (!empty($this->onAfterDisplayPostPayment)) : ?>
    <div id='onAfterDisplayPostPayment_wrapper'>
    <?php echo $this->onAfterDisplayPostPayment; ?>
    </div>
<?php endif; ?>
