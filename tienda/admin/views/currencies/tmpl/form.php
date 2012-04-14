<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; 
JFilterOutput::objectHTMLSafe( $row );
?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

	<fieldset>
		<legend><?php echo JText::_('COM_TIENDA_FORM'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_name">
						<?php echo JText::_('COM_TIENDA_TITLE'); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="currency_name" id="currency_name" size="48" maxlength="250" value="<?php echo @$row->currency_name; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_code">
						<?php echo JText::_('COM_TIENDA_CURRENCY_CODE'); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="currency_code" id="currency_code" size="10" maxlength="250" value="<?php echo @$row->currency_code; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="symbol_left">
						<?php echo JText::_('COM_TIENDA_LEFT_SYMBOL'); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="symbol_left" id="symbol_left" size="10" maxlength="250" value="<?php echo @$row->symbol_left; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="symbol_right">
						<?php echo JText::_('COM_TIENDA_RIGHT_SYMBOL'); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="symbol_right" id="symbol_right" size="10" maxlength="250" value="<?php echo @$row->symbol_right; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_decimals">
						<?php echo JText::_('COM_TIENDA_DECIMALS'); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="currency_decimals" id="currency_decimals" size="10" maxlength="250" value="<?php echo @$row->currency_decimals; ?>" />
					</td>
				</tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="decimal_separator">
                        <?php echo JText::_('COM_TIENDA_DECIMAL_SEPARATOR'); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="decimal_separator" id="decimal_separator" size="10" maxlength="250" value="<?php echo @$row->decimal_separator; ?>" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="thousands_separator">
                        <?php echo JText::_('COM_TIENDA_THOUSANDS_SEPARATOR'); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="thousands_separator" id="thousands_separator" size="10" maxlength="250" value="<?php echo @$row->thousands_separator; ?>" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="exchange_rate">
                        <?php echo JText::_('COM_TIENDA_EXCHANGE_RATE'); ?>:
                        </label>
                    </td>
                    <td>
                    	<?php if( TiendaConfig::getInstance()->get('currency_exchange_autoupdate', '1') ): ?>
                    		<div class="note">
                    			<?php echo JText::_('COM_TIENDA_WARNING_AUTOEXCHANGE_ENABLED'); ?>
                    		</div>
                    	<?php endif;?>
                        <input type="text" name="exchange_rate" id="exchange_rate" size="10" maxlength="250" value="<?php echo @$row->exchange_rate; ?>" />
                    </td>
                </tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_enabled">
						<?php echo JText::_('COM_TIENDA_ENABLED'); ?>:
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'currency_enabled', '', @$row->currency_enabled ) ?>
					</td>
				</tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->currency_id?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>