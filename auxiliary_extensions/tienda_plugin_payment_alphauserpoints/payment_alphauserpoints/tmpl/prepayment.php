<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_( "index.php?option=com_tienda&view=checkout" ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

    <div class="note">
        <?php echo JText::_( "TIENDA ALPHAUSERPOINTS PAYMENT PREPARATION MESSAGE" ); ?> 
    </div>
       
    <input type="submit" class="button" value="<?php echo JText::_('TIENDA ALPHAUSERPOINTS PAYMENT BUTTON'); ?>" />

    <input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>'>
    <input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>'>
    <input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>'>
    <input type='hidden' name='orderpayment_amount' value='<?php echo @$vars->orderpayment_amount; ?>'>
    <input type='hidden' name='points_rate' value='<?php echo @$vars->points_rate; ?>'>
    <input type='hidden' name='amount_points' value='<?php echo @$vars->amount_points; ?>'>
    <input type='hidden' name='task' value='confirmPayment'>
</form>