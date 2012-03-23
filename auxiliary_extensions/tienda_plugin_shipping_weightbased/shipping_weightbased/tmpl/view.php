<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $form = @$this->form2; ?>
<?php $row = @$this->item;
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ); ?>" method="post" name="adminForm" enctype="multipart/form-data">
	<fieldset>
		<legend>
		<?php echo JText::_('Form'); ?>
		</legend>

		<div style="width: 65%; float: left;">
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
					<label for="shipping_method_weightbased_name"><?php echo JText::_('COM_TIENDA_NAME'); ?>: </label>
					</td>
					<td>
						<input type="text" name="shipping_method_weightbased_name" id="shipping_method_weightbased_name" value="<?php echo @$row->shipping_method_weightbased_name; ?>" size="48" maxlength="250" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key"><label for="tax_class_id">
						<?php echo JText::_('Tax Class'); ?>: </label>
					</td>
					<td>
						<?php echo TiendaSelect::taxclass( @$row->tax_class_id, 'tax_class_id', '', 'tax_class_id', false ); ?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="shipping_method_weightbased_enabled"> <?php echo JText::_('Enabled'); ?>:</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'shipping_method_weightbased_enabled', '', @$row->shipping_method_weightbased_enabled ); ?>
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="shipping_method_price_start"> <?php echo JText::_('Price Start'); ?>:</label>
					</td>
					<td>
						<input type="text" name="shipping_method_price_start" id="shipping_method_price_start" value="<?php echo @$row->shipping_method_price_start; ?>" size="10" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="shipping_method_price_end"> <?php echo JText::_('Price End'); ?>:</label>
					</td>
					<td>
						<input type="text" name="shipping_method_price_end" id="shipping_method_price_end" value="<?php echo @$row->shipping_method_price_end; ?>" size="10" />
					</td>
				</tr>
			</table>
		</div>

		<div style="clear: both;"></div>

		<input type="hidden" name="shipping_method_weightbased_id" value="<?php echo @$row->shipping_method_weightbased_id; ?>" />
		<input type="hidden" id="shippingTask" name="shippingTask" value="<?php echo @$form->shippingTask; ?>" />
	</fieldset>
</form>
