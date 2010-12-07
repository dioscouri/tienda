<?php defined('_JEXEC') or die('Restricted access'); ?>

<style type="text/css">
    #sagepayments_form { width: 100%; }
    #sagepayments_form td { padding: 5px; }
    #sagepayments_form .field_name { font-weight: bold; }
</style>

<div class="note">
    <?php echo JText::_( "Tienda Sagepayments Payment Message" ); ?><br />
    <?php echo JText::_( "Tienda Sagepayments Payment PayPal Note" ); ?>
</div>

<table id="sagepayments_form">            
    <tr>
        <td class="field_name"><?php echo JText::_( 'Credit Card Type' ) ?></td>
        <td><?php echo $vars->cctype_input ?></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Card Holder Name' ) ?></td>
        <td><input type="text" name="cardholder" size="50" value="<?php echo !empty($vars->prepop['x_card_holder']) ? ($vars->prepop['x_card_num']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Card Number' ) ?></td>
        <td><input type="text" name="cardnum" size="20" value="<?php echo !empty($vars->prepop['x_card_num']) ? ($vars->prepop['x_card_num']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Expiration Date' ) ?></td>
        <td><input type="text" name="cardexp" size="10" value="<?php echo !empty($vars->prepop['x_exp_date']) ? ($vars->prepop['x_exp_date']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'CVV' ) ?></td>
        <td><input type="text" name="cardcv2" size="10" value="" /></td>
    </tr>
</table>
