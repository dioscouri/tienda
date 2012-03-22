<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $document = JFactory::getDocument(); ?>
<?php $document->addStyleSheet( JURI::root(true).'/modules/mod_tienda_pricefilters/tmpl/mod_tienda_pricefilters.css'); ?>

<ul id="tienda_pricefilter_mod">
	
<?php if (empty($priceRanges)) { ?>
    <li class="emptyfilter">
    	<?php echo JText::_('NO CATEGORY/MANUFACTURER FOUND'); ?>
    </li>
<?php }else{?>	

	<?php $i = 1;?>
	<?php foreach ($priceRanges as $link => $range ) : ?>
		<?php $selected = JRequest::getInt('rangeselected') ?>
		<?php $class = ($selected == $i) ? 'range selected' : 'range';?>	
		<li class="<?php echo $class;?>" >
			<a href="<?php echo JRoute::_( "index.php?option=com_tienda&view=products".$link."&rangeselected=".$i ); ?>">
				<span><?php echo $range; ?></span>
			</a>
		</li>	
	<?php $i++;?>
	<?php endforeach; ?>
	
<?php }?>

</ul>

<div style="float: right;">
<?php if (!empty($show_remove)) : ?>
    <a href="<?php echo JRoute::_( $remove_pricefilter_url ); ?>"><?php echo JText::_('Remove Filter') ?></a>
<?php endif; ?>
</div>