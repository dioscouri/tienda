<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>
<?php JHTML::_('behavior.tooltip'); ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

	<fieldset>
		<legend><?php echo JText::_('COM_TIENDA_FORM'); ?></legend>
			<table class="admintable">
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_USER'); ?>:
                    </td>
                    <td>
                        <?php $user_element = TiendaSelect::userelement( @$row->user_id, 'user_id' ); ?>
                        <?php echo $user_element['select']; ?>
                        <?php echo $user_element['clear']; ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_AMOUNT'); ?>:
                    </td>
                    <td>
                        <input name="credit_amount" type="text" size="20" value="<?php echo @$row->credit_amount ?> "size="48" maxlength="250" />
                    </td>
                </tr>
				<tr>
					<td title="<?php echo JText::_('COM_TIENDA_CREDIT_TYPE').'::'.JText::_('COM_TIENDA_CREDIT_TYPE_TIP'); ?>" class="key hasTip" style="width: 100px; text-align: right;">
						<?php echo JText::_('COM_TIENDA_TYPE'); ?>:
					</td>
					<td>
						<?php echo TiendaSelect::credittype( @$row->credittype_code, 'credittype_code' ); ?>
					</td>
				</tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_ENABLED'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'credit_enabled', '', @$row->credit_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_CAN_BE_WITHDRAWN'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'credit_withdrawable', '', @$row->credit_withdrawable ); ?>
                    </td>
                </tr>
				<tr>
					<td title="<?php echo JText::_('COM_TIENDA_CREDIT_CODE').'::'.JText::_('COM_TIENDA_CREDIT_CODE_TIP'); ?>" class="key hasTip" style="width: 100px; text-align: right;">
						<?php echo JText::_('COM_TIENDA_CODE'); ?>:
					</td>
					<td>
						<input name="credit_code" type="text" size="40" value="<?php echo @$row->credit_code ?> "size="48" maxlength="250" />
					</td>
				</tr>
				<tr>
					<td style="width: 100px; text-align: right;" class="key">
						<?php echo JText::_('COM_TIENDA_COMMENTS'); ?>:
					</td>
					<td>
						<textarea name="credit_comments" rows="10" cols="35"><?php echo @$row->credit_comments ?></textarea>
					</td>
				</tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_ORDER_ID'); ?>:
                    </td>
                    <td>
                        <?php 
                        if (!empty($row->order_id))
                        {
                            ?>
                            <a href="index.php?option=com_tienda&view=orders&task=view&id=<?php echo $row->order_id; ?>" target="_blank"><?php echo JText::_('COM_TIENDA_VIEW_ORDER').": " .$row->order_id; ?></a>
                            <?php
                        } else {
                            echo JText::_('COM_TIENDA_NONE');
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_BALANCE_UPDATED'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaGrid::boolean( @$row->credits_updated ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_BALANCE_BEFORE'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->credit_balance_before; ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_BALANCE_AFTER'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->credit_balance_after; ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_WITHDRAWABLE_BALANCE_BEFORE'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->withdrawable_balance_before; ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_(COM_TIENDA_WITHDRAWABLE_BALANCE_AFTER'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->withdrawable_balance_after; ?>
                    </td>
                </tr>
			</table>
			<input type="hidden" name="credit_id" value="<?php echo @$row->credit_id; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form>