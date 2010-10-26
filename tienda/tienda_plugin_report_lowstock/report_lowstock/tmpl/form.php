<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

    <p><?php echo JText::_( "This reports on low stock products" ); ?></p>

    <div class="note">
    	<?php echo JText::_("Product Name"); ?>:
			<input type="text" name="filter_name" id="filter_name" value="<?php echo @$state->filter_name; ?>" />
	  	<span style="border-right: 2px solid #000; margin: 0 20px;"></span>
    	<?php echo JText::_("Select Quantity Range"); ?>:
	    <?php $attribs = array('class' => 'inputbox', 'size' => '1'); ?>
	    <?php echo TiendaSelect::reportrange( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?>
	   		<span class="label"><?php echo JText::_("From"); ?>:</span>
	   		<input type="text" name="filter_qty_from" id="filter_qty_from" value="<?php echo @$state->filter_qty_from; ?>" />
	    	<span class="label"><?php echo JText::_("To"); ?>:</span>
	   		<input type="text" name="filter_qty_to" id="filter_qty_to" value="<?php echo @$state->filter_qty_to; ?>" />
	  	<span style="border-right: 2px solid #000; margin: 0 20px;"></span>
	    <?php echo JText::_("Select Category"); ?>:
	    <?php $attribs = array('class' => 'inputbox', 'size' => '1'); ?>
	    <?php echo TiendaSelect::category( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?>

	</div>
