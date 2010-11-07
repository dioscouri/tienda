<?php defined('_JEXEC') or die('Restricted access'); ?>

<style type="text/css">
    #authorizedotnet_form { width: 100%; }
    #authorizedotnet_form td { padding: 5px; }
    #authorizedotnet_form .field_name { font-weight: bold; }
</style>

<div class="note">
    <?php echo JText::_( "Tienda Authorizedotnet Payment Message" ); ?>
</div>

<table id="authorizedotnet_form">            
    <tr>
        <td class="field_name"><?php echo JText::_( 'Card Holder Name' ) ?></td>
        <td><input type="text" name="cardholder" size="50" value="<?php echo !empty($vars->prepop['x_card_holder']) ? ($vars->prepop['x_card_num']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Credit Card Type' ) ?></td>
        <td><?php echo $vars->cctype_input ?></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Card Number' ) ?></td>
        <td><input type="text" name="cardnum" size="20" value="<?php echo !empty($vars->prepop['x_card_num']) ? ($vars->prepop['x_card_num']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Start Date' ) ?></td>
        <td><input type="text" name="cardest" size="10" value="<?php echo !empty($vars->prepop['x_start_date']) ? ($vars->prepop['x_start_date']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Expiration Date' ) ?></td>
        <td><input type="text" name="cardexp" size="10" value="<?php echo !empty($vars->prepop['x_exp_date']) ? ($vars->prepop['x_exp_date']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Issue Number' ) ?></td>
        <td><input type="text" name="cardissuenum" size="10" value="" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Card CVV Number' ) ?></td>
        <td><input type="text" name="cardcvv" size="10" value="" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'CV2' ) ?></td>
        <td><input type="text" name="cardcv2" size="10" value="" /></td>
    </tr>
</table>
