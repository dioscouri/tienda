<?php defined('_JEXEC') or die('Restricted access'); ?>

<style type="text/css">
    #sagepay_form { width: 100%; }
    #sagepay_form td { padding: 5px; }
    #sagepay_form .field_name { font-weight: bold; }
</style>

<div class="note">
    <?php echo JText::_('Tienda Sagepay Payment Message'); ?><br />
    <?php echo JText::_('Tienda Sagepay Payment PayPal Note'); ?>
</div>

<table id="sagepay_form">            
    <tr>
        <td class="field_name"><?php echo JText::_('COM_TIENDA_CREDIT_CARD_TYPE') ?></td>
        <td><?php echo $vars->cctype_input ?></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_('Card Holder Name') ?></td>
        <td><input type="text" name="cardholder" size="50" value="<?php echo !empty($vars->prepop['x_card_holder']) ? ($vars->prepop['x_card_num']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_('COM_TIENDA_CARD_NUMBER') ?></td>
        <td><input type="text" name="cardnum" size="20" value="<?php echo !empty($vars->prepop['x_card_num']) ? ($vars->prepop['x_card_num']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_('Start Date') ?></td>
        <td><input type="text" name="cardst" size="10" value="<?php echo !empty($vars->prepop['x_start_date']) ? ($vars->prepop['x_start_date']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_('COM_TIENDA_EXPIRATION_DATE') ?></td>
        <td><input type="text" name="cardexp" size="10" value="<?php echo !empty($vars->prepop['x_exp_date']) ? ($vars->prepop['x_exp_date']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_('Issue Number') ?></td>
        <td><input type="text" name="cardissuenum" size="10" value="" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_('CV2') ?></td>
        <td><input type="text" name="cardcv2" size="10" value="" /></td>
    </tr>
</table>
