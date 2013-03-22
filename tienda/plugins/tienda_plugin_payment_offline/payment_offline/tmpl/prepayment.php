<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_( "index.php?option=com_tienda&view=checkout" ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

    <div class="note">
        <?php echo JText::_('COM_TIENDA_OFFLINE_PAYMENT_PREPARATION_MESSAGE'); ?>
    
        <p>
            <strong><?php echo JText::_('COM_TIENDA_OFFLINE_PAYMENT_METHOD');?>:</strong> 
            <?php echo JText::_( $vars->offline_payment_method ); ?>
        </p>
    </div>
    
    <input type='hidden' name='offline_payment_method' value='<?php echo @$vars->offline_payment_method; ?>'>
    
    <input type="submit" class="btn" value="<?php echo JText::_('COM_TIENDA_CLICK_HERE_TO_COMPLETE_ORDER'); ?>" />

    <input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>'>
    <input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>'>
    <input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>'>
    <input type='hidden' name='task' value='confirmPayment'>
</form>