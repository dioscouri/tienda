<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_name">
						<?php echo JText::_( 'Title' ); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="currency_name" id="currency_name" size="48" maxlength="250" value="<?php echo @$row->currency_name; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_code">
						<?php echo JText::_( 'Currency Code' ); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="currency_code" id="currency_code" size="10" maxlength="250" value="<?php echo @$row->currency_code; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="symbol_left">
						<?php echo JText::_( 'Left Symbol' ); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="symbol_left" id="symbol_left" size="10" maxlength="250" value="<?php echo @$row->symbol_left; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="symbol_right">
						<?php echo JText::_( 'Right Symbol' ); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="symbol_right" id="symbol_right" size="10" maxlength="250" value="<?php echo @$row->symbol_right; ?>" />
					</td>
				</tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_decimals">
						<?php echo JText::_( 'Decimals' ); ?>:
						</label>
					</td>
					<td>
						<input type="text" name="currency_decimals" id="currency_decimals" size="10" maxlength="250" value="<?php echo @$row->currency_decimals; ?>" />
					</td>
				</tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="decimal_separator">
                        <?php echo JText::_( 'Decimal Separator' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="decimal_separator" id="decimal_separator" size="10" maxlength="250" value="<?php echo @$row->decimal_separator; ?>" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <label for="thousands_separator">
                        <?php echo JText::_( 'Thousands Separator' ); ?>:
                        </label>
                    </td>
                    <td>
                        <input type="text" name="thousands_separator" id="thousands_separator" size="10" maxlength="250" value="<?php echo @$row->thousands_separator; ?>" />
                    </td>
                </tr>
				<tr>
					<td width="100" align="right" class="key">
						<label for="currency_enabled">
						<?php echo JText::_( 'Enabled' ); ?>:
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