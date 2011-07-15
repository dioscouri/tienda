<?php defined('_JEXEC') or die('Restricted access'); ?>

<style type="text/css">
    #ccoffline_form { width: 100%; }
    #ccoffline_form td { padding: 5px; }
    #ccoffline_form .field_name { font-weight: bold; }
</style>

<form action="<?php echo JRoute::_( "index.php?option=com_tienda&view=checkout" ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

    <div class="note">
        <?php echo JText::_( "TIENDA CC OFFLINE PAYMENT PREPARATION MESSAGE" ); ?>
        
        <table id="ccoffline_form">            
            <tr>
                <td class="field_name"><?php echo JText::_( 'TIENDA CC OFFLINE PAYMENT TYPE' ) ?></td>
                <td><?php echo $vars->cardtype; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'TIENDA CC OFFLINE PAYMENT NUMBER' ) ?></td>
                <td>************<?php echo $vars->cardnum_last4; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'TIENDA CC OFFLINE PAYMENT EXPIRATION DATE' ) ?></td>
                <td><?php echo $vars->cardexp; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'TIENDA CC OFFLINE PAYMENT ID' ) ?></td>
                <td>****</td>
            </tr>
        </table>
    </div>

    <input type='hidden' name='cardtype' value='<?php echo @$vars->cardtype; ?>'>
    <input type='hidden' name='cardnum' value='<?php echo @$vars->cardnum; ?>'>
    <input type='hidden' name='cardexp' value='<?php echo @$vars->cardexp; ?>'>
    <input type='hidden' name='cardcvv' value='<?php echo @$vars->cardcvv; ?>'>

    <input type="submit" class="button" value="<?php echo JText::_('TIENDA CC OFFLINE PAYMENT CLICK HERE'); ?>" id="submit_button" onclick="document.getElementById('submit_button').disabled = 1; this.form.submit();" />

    <input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>'>
    <input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>'>
    <input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>'>
    <input type='hidden' name='task' value='confirmPayment'>
    
    <?php echo JHTML::_( 'form.token' ); ?>
</form>