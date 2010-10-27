<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

    <p><?php echo JText::_( "THIS REPORTS ON LOW STOCK PRODUCTS" ); ?></p>
    <div class="note">
    	<?php echo JText::_("Product Name"); ?>:
			<input type="text" name="filter_name" id="filter_name" value="<?php echo @$state->filter_name; ?>" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
    	<?php echo JText::_("Select Quantity Range"); ?>:
	    	<span class="label"><?php echo JText::_("From"); ?>:</span>
	   		<input type="text" name="filter_quantity_from" id="filter_quantity_from" value="<?php echo @$state->filter_quantity_from; ?>" />
	    	<span class="label"><?php echo JText::_("To"); ?>:</span>
	   		<input type="text" name="filter_quantity_to" id="filter_quantity_to" value="<?php echo @$state->filter_quantity_to; ?>" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
	    <?php echo JText::_("Select Category"); ?>:
	   	<?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'javascript:submitbutton(\'view\').click;'); ?>
	  	<?php echo TiendaSelect::category( @$state->filter_category, 'filter_category', $attribs, 'category', true ); ?>
	
	</div>