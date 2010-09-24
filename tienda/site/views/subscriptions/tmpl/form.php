<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>

<form action ="/tiendalatest/index.php?option=com_tienda&view=subscriptions&task=update"> 
<input type="submit" value="Unsubscribe">
</form>

<!-- <form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

	<fieldset>
		<legend><?php echo JText::_('Form'); ?></legend>
			<table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Subscription Enabled' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'subscription_enabled', '', @$row->subscription_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Lifetime Subscription' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'lifetime_enabled', '', @$row->lifetime_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'User ID' ); ?>:
                    </td>
                    <td>
                        <input name="user_id" value="<?php echo @$row->user_id; ?>" size="15" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Product ID' ); ?>:
                    </td>
                    <td>
                        <input name="product_id" value="<?php echo @$row->product_id; ?>" size="15" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Order ID' ); ?>:
                    </td>
                    <td>
                        <input name="order_id" value="<?php echo @$row->order_id; ?>" size="15" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Orderitem ID' ); ?>:
                    </td>
                    <td>
                        <input name="orderitem_id" value="<?php echo @$row->orderitem_id; ?>" size="15" maxlength="250" type="text" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Created' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->created_datetime, "created_datetime", "created_datetime", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Expiration Date' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::calendar( @$row->expires_datetime, "expires_datetime", "expires_datetime", '%Y-%m-%d %H:%M:%S' ); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Transaction ID' ); ?>:
                    </td>
                    <td>
                        <input name="transaction_id" value="<?php echo @$row->transaction_id; ?>" size="48" maxlength="250" type="text" />
                    </td>
                </tr>
                 <tr>
                    <td>
                        <input name="save" value="Unsubscribe" size="48" maxlength="250" type="submit" />
                    </td>
                </tr>
			</table>
			<input type="hidden" name="id" value="<?php echo @$row->subscription_id; ?>" />
			<input type="hidden" name="task" value="" />
	</fieldset>
</form> -->
