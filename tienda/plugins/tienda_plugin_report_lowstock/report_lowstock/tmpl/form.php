<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

    <p><?php echo JText::_('COM_TIENDA_THIS_REPORTS_ON_LOW_STOCK_PRODUCTS'); ?></p>
    <div class="note">
    	<?php echo JText::_('COM_TIENDA_PRODUCT_NAME'); ?>:    	
			<input type="text" name="filter_name" id="filter_name" value="<?php echo @$state->filter_name; ?>" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
    	<?php echo JText::_('COM_TIENDA_SELECT_QUANTITY_RANGE'); ?>:
	    	<span class="label"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
	   		<input type="text" name="filter_quantity_from" id="filter_quantity_from" value="<?php echo @$state->filter_quantity_from; ?>" />
	    	<span class="label"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
	   		<input type="text" name="filter_quantity_to" id="filter_quantity_to" value="<?php echo @$state->filter_quantity_to; ?>" />
	  	<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
	    <?php echo JText::_('COM_TIENDA_SELECT_CATEGORY'); ?>:
	   	<?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'javascript:submitbutton(\'view\').click;'); ?>
	  	<?php echo TiendaSelect::category( @$state->filter_category, 'filter_category', $attribs, 'category', true ); ?>
		<span style="font-size:1.6em; border-right: 2px groove #333; margin: 0 20px;"></span>
		<?php echo JText::_('COM_TIENDA_SELECT_PRODUCTS_TO_SHOW'); ?>:
		<?php $attribs = array('class' => 'inputbox', 'size' => '1' ); ?>
		<?php 
				$arr = array(
					JHTML::_('select.option',  '', JText::_( JText::_('COM_TIENDA_ALL') ) ),
					JHTML::_('select.option',  '1', JText::_( JText::_('COM_TIENDA_ENABLED') ) ),
					JHTML::_('select.option',  '0',  JText::_( JText::_('COM_TIENDA_DISABLED') ) )
				);
		
				echo JHtml::_( 'select.radiolist', $arr, 'filter_enabled', $attribs, 'value', 'text', @$state->filter_enabled );
		?>
		
	</div>