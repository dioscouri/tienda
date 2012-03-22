<?php defined('_JEXEC') or die('Restricted access'); ?>

<div class="note">
    <?php echo JText::_('TIENDA LINKPOINT FIRSTDATA PAYMENT MESSAGE'); ?>
</div>

<table id="firstdata_form">            
    <tr>
        <td class="field_name"><?php echo JText::_('CARD NUMBER') ?></td>
        <td><input type="text" name="cardnum" size="35" value="<?php echo !empty($vars->prepop['x_card_num']) ? ($vars->prepop['x_card_num']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_('EXPIRATION MONTH') ?></td>
        <td><input type="text" name="cardexpmonth" size="2" value="<?php echo !empty($vars->prepop['x_exp_month']) ? ($vars->prepop['x_exp_month']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_('EXPIRATION YEAR') ?></td>
        <td><input type="text" name="cardexpyear" size="2" value="<?php echo !empty($vars->prepop['x_exp_year']) ? ($vars->prepop['x_exp_year']) : '' ?>" /></td>
    </tr>    
    <tr>
        <td class="field_name"><?php echo JText::_('CARD CVV NUMBER') ?></td>
        <td><input type="text" name="cardcvv" size="10" value="" /></td>
    </tr>
</table>