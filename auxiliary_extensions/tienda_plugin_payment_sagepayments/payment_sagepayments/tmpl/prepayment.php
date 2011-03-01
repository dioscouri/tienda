<?php defined('_JEXEC') or die('Restricted access'); ?>

<style type="text/css">
    #sagepayments_form { width: 100%; }
    #sagepayments_form td { padding: 5px; }
    #sagepayments_form .field_name { font-weight: bold; }
</style>

<form action="<?php echo JRoute::_( "index.php?option=com_tienda&view=checkout" ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

    <div class="note">
        <?php echo JText::_( "TIENDA SAGEPAYMENTS PAYMENT PREPARATION MESSAGE" ); ?>
        
        <table id="sagepayments_form">            
		    <tr>
		        <td class="field_name"><?php echo JText::_( 'Card Holder Name' ) ?></td>
		        <td><?php echo $vars->cardholder;?></td>
		    </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'Credit Card Type' ) ?></td>
                <td><?php echo $vars->cardtype; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'Card Number' ) ?></td>
                <td>************<?php echo $vars->cardnum_last4; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'Expiration Date' ) ?></td>
                <td><?php echo $vars->cardexp; ?></td>
            </tr>
		    <tr>
		        <td class="field_name"><?php echo JText::_( 'CVV' ) ?></td>
		        <td><?php echo $vars->cardcv2_asterix;?></td>
		    </tr>
        </table>
    </div>

    <input type='hidden' name='cardholder' value='<?php echo @$vars->cardholder; ?>' />
    <input type='hidden' name='cardtype' value='<?php echo @$vars->cardtype; ?>' />
    <input type='hidden' name='cardnum' value='<?php echo @$vars->cardnum; ?>' />
    <input type='hidden' name='cardexp' value='<?php echo @$vars->cardexp; ?>' />
    <input type='hidden' name='cardcv2' value='<?php echo @$vars->cardcv2; ?>' />

    <input type="submit" class="button" value="<?php echo JText::_('Click Here to Complete Order'); ?>" id="submit_button" onclick="document.getElementById('submit_button').disabled = 1;" />

    <input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>' />
    <input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>' />
    <input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>' />
    <input type='hidden' name='task' value='confirmPayment' />
    <input type='hidden' name='paction' value='process' />
    
    <?php echo JHTML::_( 'form.token' ); ?>
</form>