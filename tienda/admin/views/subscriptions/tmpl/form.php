<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" id="adminForm" enctype="multipart/form-data" >


			<table class="table table-striped table-bordered">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_SUBSCRIPTION_ENABLED'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::btbooleanlist(  'subscription_enabled', '', @$row->subscription_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_LIFETIME_SUBSCRIPTION'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaSelect::btbooleanlist(  'lifetime_enabled', '', @$row->lifetime_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_USER_ID'); ?>:
                    </td>
                    <td>
                        <input name="user_id" value="<?php echo @$row->user_id; ?>" size="15" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_PRODUCT_ID'); ?>:
                    </td>
                    <td>
                        <input name="product_id" value="<?php echo @$row->product_id; ?>" size="15" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_ORDER_ID'); ?>:
                    </td>
                    <td>
                        <input name="order_id" value="<?php echo @$row->order_id; ?>" size="15" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_ORDERITEM_ID'); ?>:
                    </td>
                    <td>
                        <input name="orderitem_id" value="<?php echo @$row->orderitem_id; ?>" size="15" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_CREATED'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->created_datetime, "created_datetime", "created_datetime", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_EXPIRATION_DATE'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->expires_datetime, "expires_datetime", "expires_datetime", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_TRANSACTION_ID'); ?>:
                    </td>
                    <td>
                        <input name="transaction_id" value="<?php echo @$row->transaction_id; ?>" size="48" maxlength="250" type="text" />
                    </td>
                </tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->subscription_id; ?>" />
			<input type="hidden" name="task" value="" />

</form>