<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_( "index.php?option=com_tienda&view=checkout" ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

    <div class="note">
        <?php echo JText::_('TIENDA LINKPOINT FIRSTDATA PAYMENT PREPARATION MESSAGE'); ?>
        
        <table id="firstdata_form">            
            <tr>
                <td class="field_name"><?php echo JText::_('CARD NUMBER') ?></td>
                <td>************<?php echo $vars->cardnum_last4; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_('EXPIRATION MONTH') ?></td>
                <td><?php echo $vars->cardexpmonth; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_('EXPIRATION YEAR') ?></td>
                <td><?php echo $vars->cardexpyear; ?></td>
            </tr>            
            <tr>
                <td class="field_name"><?php echo JText::_('CARD CVV NUMBER') ?></td>
                <td>****</td>
            </tr>
		</table>
    </div>

    <input type='hidden' name='cardnum' value='<?php echo @$vars->cardnum; ?>'>
    <input type='hidden' name='cardexpmonth' value='<?php echo @$vars->cardexpmonth; ?>'>
    <input type='hidden' name='cardexpyear' value='<?php echo @$vars->cardexpyear; ?>'>    
    <input type='hidden' name='cardcvv' value='<?php echo @$vars->cardcvv; ?>'>

    <input type="submit" class="button" value="<?php echo JText::_('Click Here to Complete Order'); ?>" />

    <input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>'>
    <input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>'>
    <input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>'>
    <input type='hidden' name='task' value='confirmPayment'>
    <input type='hidden' name='paction' value='process'>
    
    <?php echo JHTML::_( 'form.token' ); ?>
</form>