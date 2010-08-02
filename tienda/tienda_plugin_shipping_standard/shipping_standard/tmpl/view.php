<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form2; ?>
<?php $row = @$this->item;
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" name="adminForm" enctype="multipart/form-data">
<fieldset>
	<legend><?php echo JText::_('Form'); ?></legend>
	
	<div style="width: 65%; float: left;">
	<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="shipping_method_name">
				<?php echo JText::_( 'Name' ); ?>:
				</label>
			</td>
			<td>
				<input type="text" name="shipping_method_name" id="shipping_method_name" value="<?php echo @$row->shipping_method_name; ?>" size="48" maxlength="250" />
			</td>
		</tr>
        <tr>
            <td width="100" align="right" class="key">
                <label for="tax_class_id">
                <?php echo JText::_( 'Tax Class' ); ?>:
                </label>
            </td>
            <td>
                <?php echo TiendaSelect::taxclass( @$row->tax_class_id, 'tax_class_id', '', 'tax_class_id', false ); ?>
            </td>
        </tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="shipping_method_enabled">
				<?php echo JText::_( 'Enabled' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', 'shipping_method_enabled', '', @$row->shipping_method_enabled ); ?>
			</td>
		</tr>
        <tr>
            <td width="100" align="right" class="key">
                <label for="shipping_method_type">
                <?php echo JText::_( 'Type' ); ?>:
                </label>
            </td>
            <td>
                <?php echo TiendaSelect::shippingtype( @$row->shipping_method_type, 'shipping_method_type', '', 'shipping_method_type', false ); ?>
            </td>
        </tr>
        <tr>
            <td width="100" align="right" class="key">
                <label for="subtotal_minimum">
                <?php echo JText::_( 'Minimum Subtotal Required' ); ?>:
                </label>
            </td>
            <td>
                <input type="text" name="subtotal_minimum" id="subtotal_minimum" value="<?php echo @$row->subtotal_minimum; ?>" size="10" />
            </td>
        </tr>
	</table>
    </div>
    
    <div class="note" style="width: 25%; float: left; padding-right: 20px;">
        <span style="font-weight: bold; font-size: 13px; text-transform: uppercase;"><?php echo JText::_( "Note" ); ?>:</span>
        <?php echo JText::_( "Shipping Type Help Text" ); ?>:
        <ul>
            <li><?php echo JText::_( "Flat Rate Per Item HELP TEXT" ); ?></li>
            <li><?php echo JText::_( "Weight-Based Per Item HELP TEXT" ); ?></li>
            <li><?php echo JText::_( "Flat Rate Per Order HELP TEXT" ); ?></li>
            <li><?php echo JText::_( "Weight-Based Per Order HELP TEXT" ); ?></li>
        </ul>
    </div>    						
    
    <div style="clear: both;"></div>
        
	<input type="hidden" name="shipping_method_id" value="<?php echo @$row->shipping_method_id; ?>" />
	<input type="hidden" id="shippingTask" name="shippingTask" value="<?php echo @$form->shippingTask; ?>" />
	
	
	</fieldset>
</form>