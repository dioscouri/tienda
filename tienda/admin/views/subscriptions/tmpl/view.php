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
		<legend><?php echo JText::_('COM_TIENDA_SUBSCRIPTION_INFORMATION'); ?></legend>
			<table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_SUBSCRIPTION_ENABLED'); ?>:
                    </td>
                    <td>
                        <?php echo TiendaGrid::boolean( @$row->subscription_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_LIFETIME_SUBSCRIPTION'); ?>?
                    </td>
                    <td>
                        <?php echo TiendaGrid::boolean( @$row->lifetime_enabled ); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_CREATED'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('date', $row->created_datetime, Tienda::getInstance()->get('date_format')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100px; text-align: right;" class="key">
                        <?php echo JText::_('COM_TIENDA_EXPIRATION_DATE'); ?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('date', $row->expires_datetime, Tienda::getInstance()->get('date_format')); ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_TRANSACTION_ID'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->transaction_id; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_PRODUCT'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->product_name; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_PRODUCT_ID'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->product_id; ?>
                    </td>
                </tr>
            </table>
    </fieldset>
    
    <fieldset>
        <legend><?php echo JText::_('COM_TIENDA_USER_INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_USER'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->user_name; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_EMAIL'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->email; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_USERNAME'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->user_username; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_USER_ID'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->user_id; ?>
                    </td>
                </tr>
		            <?php if ( Tienda::getInstance()->get( 'display_subnum', 0 ) ) : ?>
		            <tr>
                    <td width="100" align="right" class="key">
		                    <?php echo JText::_('COM_TIENDA_SUB_NUM'); ?>
		                </td>
		                <td colspan="2">
				            	<?php Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' ); ?>
		    	        		<?php echo TiendaHelperSubscription::displaySubNum( $row->sub_number ); ?>                    
		                </td>
		            </tr>
		            <?php endif; ?>
            </table>
    </fieldset>
    
    <fieldset>
        <legend><?php echo JText::_('COM_TIENDA_ORDER_INFORMATION'); ?></legend>
            <table class="admintable">
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_ORDER_ID'); ?>:
                    </td>
                    <td>
                        <?php echo @$row->order_id; ?>
                    </td>
                </tr>
                <tr>
                    <td width="100" align="right" class="key">
                        <?php echo JText::_('COM_TIENDA_ORDERITEM_ID'); ?>:
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
            <legend><?php echo JText::_('COM_TIENDA_SUBSCRIPTION_HISTORY'); ?></legend>
                <table class="adminlist" style="clear: both;">
                <thead>
                    <tr>
                        <th style="text-align: left;"><?php echo JText::_('COM_TIENDA_DATE'); ?></th>
                        <th style="text-align: center;"><?php echo JText::_('COM_TIENDA_TYPE'); ?></th>
                        <th style="text-align: center;"><?php echo JText::_('COM_TIENDA_NOTIFICATION_SENT'); ?></th>
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
                            <?php echo JHTML::_('date', $history->created_datetime, Tienda::getInstance()->get('date_format')); ?>
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
                                <b><?php echo JText::_('COM_TIENDA_COMMENTS'); ?></b>:
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
                            <?php echo JText::_('COM_TIENDA_NO_SUBSCRIPTION_HISTORY_FOUND'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
                </table>
        </fieldset>
    
        <fieldset>
        <legend><?php echo JText::_('COM_TIENDA_UPDATE_SUBSCRIPTION'); ?></legend>
        
        <table class="admintable" style="clear: both; width: 100%;">
        <tr>
            <td style="width: 100px; text-align: right;" class="key">
                <?php echo JText::_('COM_TIENDA_NEW_ENTRY_TYPE'); ?>
            </td>
            <td>
                <input value="<?php echo JText::_('COM_TIENDA_ADD_ENTRY_TO_HISTORY'); ?>" onclick="document.getElementById('task').value='update_subscription'; this.form.submit();" style="float: right;" type="button" />
                <input type='text' name="subscriptionhistory_type" size="25" />
            </td>
        </tr>
        <tr>
            <td style="width: 100px; text-align: right;" class="key">
                <?php echo JText::_('COM_TIENDA_NOTIFY_CUSTOMER_ABOUT_CHANGE_IN_SUBSCRIPTION'); ?>
            </td>
            <td>
                <?php echo JHTML::_('select.booleanlist', 'notify_customer' ); ?>
            </td>
        </tr>
        <tr>
            <td style="width: 100px; text-align: right;" class="key">
                <?php echo JText::_('COM_TIENDA_COMMENTS'); ?>
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