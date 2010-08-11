<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; JFilterOutput::objectHTMLSafe( $row ); ?>
<?php $histories = Tienda::getClass( 'TiendaHelperSubscription', 'helpers.subscription' )->getHistory( $row->subscription_id ); ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

<table style="width: 100%;">
<tr>
    <td style="width: 65%; vertical-align: top;">
    
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onBeforeDisplaySubscriptionViewSubscriptionInfo', array( $row ) );                    
    ?>
    
	<fieldset>
		<legend><?php echo JText::_('Subscription Information'); ?></legend>
			<table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Subscription Enabled' ); ?>:
                    </td>
                    <td>
                        <?php echo TiendaGrid::boolean( @$row->subscription_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Lifetime Subscription' ); ?>?
                    </td>
                    <td>
                        <?php echo TiendaGrid::boolean( @$row->lifetime_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Created' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('date', $row->created_datetime, TiendaConfig::getInstance()->get('date_format')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_( 'Expiration Date' ); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('date', $row->expires_datetime, TiendaConfig::getInstance()->get('date_format')); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Transaction ID' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->transaction_id; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Product' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->product_name; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Product ID' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->product_id; ?>
                    </td>
                </tr>
            </table>
    </fieldset>
    
    <fieldset>
        <legend><?php echo JText::_('User Information'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'User' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->user_name; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Email' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->email; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Username' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->user_username; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'User ID' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->user_id; ?>
                    </td>
                </tr>
            </table>
    </fieldset>
    
    <fieldset>
        <legend><?php echo JText::_('Order Information'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Order ID' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->order_id; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_( 'Orderitem ID' ); ?>:
                    </td>
                    <td>
                        <?php echo @$row->orderitem_id; ?>
                    </td>
                </tr>
			</table>
	</fieldset>
	
    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onAfterDisplaySubscriptionViewSubscriptionInfo', array( $row ) );                    
    ?>
	
    </td>
    <td style="width: 35%; vertical-align: top;">
    
        <?php
            // fire plugin event here to enable extending the form
            JDispatcher::getInstance()->trigger('onBeforeDisplaySubscriptionViewSubscriptionHistory', array( $row ) );                    
        ?>
    
        <fieldset>
            <legend><?php echo JText::_('Subscription History'); ?></legend>
                <table class="adminlist" style="clear: both;">
                <thead>
                    <tr>
                        <th style="text-align: left;"><?php echo JText::_("Date"); ?></th>
                        <th style="text-align: center;"><?php echo JText::_("Type"); ?></th>
                        <th style="text-align: center;"><?php echo JText::_("Notification Sent"); ?></th>
                    </tr>
                </thead>
                <tbody>

                <?php
                if (!empty($histories))
                { 
                ?>
                <?php $i=0; $k=0; ?>
                <?php foreach (@$histories as $history) : ?>
                    <tr class='row<?php echo $k; ?>'>
                        <td style="text-align: left;">
                            <?php echo JHTML::_('date', $history->created_datetime, TiendaConfig::getInstance()->get('date_format')); ?>
                        </td>
                        <td style="text-align: center;">
                            <?php echo JText::_( $history->subscriptionhistory_type ); ?>
                        </td>
                        <td style="text-align: center;">
                            <?php echo TiendaGrid::boolean( $history->notify_customer ); ?>
                        </td>
                    </tr>
                    <?php
                    if (!empty($history->comments))
                    { 
                        ?>
                        <tr class='row<?php echo $k; ?>'>
                            <td colspan="3" style="text-align: left; padding-left: 10px;">
                                <b><?php echo JText::_( "Comments" ); ?></b>:
                                <?php echo $history->comments; ?>
                            </td>
                        </tr>               
                        <?php 
                    }
                    ?>
                    
                <?php $i=$i+1; $k = (1 - $k); ?>
                <?php endforeach; ?>
                <?php
                }
                ?>                
                <?php if (empty($histories)) : ?>
                    <tr>
                        <td colspan="10" align="center">
                            <?php echo JText::_('No subscription history found'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
                </table>
        </fieldset>
    
        <fieldset>
        <legend><?php echo JText::_('Update Subscription'); ?></legend>
        
        <table class="admintable" style="clear: both; width: 100%;">
        <tr>
            <td style="width: 100px; text-align: right;" class="key">
                <?php echo JText::_("New Entry Type"); ?>
            </td>
            <td>
                <input value="<?php echo JText::_( "Add Entry to History" ); ?>" onclick="document.getElementById('task').value='update_subscription'; this.form.submit();" style="float: right;" type="button" />
                <input type='text' name="subscriptionhistory_type" size="25" />
            </td>
        </tr>
        <tr>
            <td style="width: 100px; text-align: right;" class="key">
                <?php echo JText::_("Notify Customer about Change in Subscription"); ?>
            </td>
            <td>
                <?php echo JHTML::_('select.booleanlist', 'notify_customer' ); ?>
            </td>
        </tr>
        <tr>
            <td style="width: 100px; text-align: right;" class="key">
                <?php echo JText::_("Comments"); ?>
            </td>
            <td>
                <textarea name="comments" rows="5" style="width: 100%;"></textarea>
            </td>
        </tr>
        </table>
        </fieldset>
        
        <?php
            // fire plugin event here to enable extending the form
            JDispatcher::getInstance()->trigger('onAfterDisplaySubscriptionViewSubscriptionHistory', array( $row ) );                    
        ?>
    </td>
</tr>
</table>

    <?php
        // fire plugin event here to enable extending the form
        JDispatcher::getInstance()->trigger('onAfterDisplaySubscriptionView', array( $row ) );                    
    ?>

<input type="hidden" name="id" value="<?php echo @$row->subscription_id; ?>" />
<input type="hidden" name="task" id="task" value="" />
</form>