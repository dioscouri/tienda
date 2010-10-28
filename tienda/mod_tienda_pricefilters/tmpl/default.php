<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $document = JFactory::getDocument(); ?>
<?php $document->addStyleSheet( JURI::root(true).'/modules/mod_tienda_pricefilters/tmpl/mod_tienda_pricefilters.css'); ?>

<div id="tienda_pricefilter_mod">
<?php $i = 1;?>
<?php foreach ($priceRanges as $link => $range ) : ?>
	<?php $selected = JRequest::getInt('rangeselected') ?>
	<?php $class = $selected == $i ? 'range selected' : 'range';?>
	
	<div class="<?php echo $class;?>" >
		<span class="arrow">&#187</span><a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products".$link."&rangeselected=".$i ); ?>"><?php echo $range; ?></a>
	</div>
	
<?php $i++;?>
<?php endforeach; ?>	
</div>