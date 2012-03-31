<?php defined('_JEXEC') or die('Restricted access'); ?>

<style type="text/css">
    #sagepayments_form { width: 100%; }
    #sagepayments_form td { padding: 5px; }
    #sagepayments_form .field_name { font-weight: bold; }
</style>

<div class="note">
    <?php echo JText::_('Tienda Sagepayments Payment Message'); ?><br />
    <?php echo JText::_('Tienda Sagepayments Payment PayPal Note'); ?>
</div>

<table id="sagepayments_form">            
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
        <td class="field_name"><?php echo JText::_('COM_TIENDA_EXPIRATION_DATE') ?></td>
        <td>
        <?php Tienda::load( 'TiendaSelect', 'library.select' ); ?>
        <?php $year = date('Y'); $year_end = $year + 25; ?>
        <?php echo TiendaSelect::integerlist( '1', '12', '1', 'cardexp_month' ); ?>
        <?php echo TiendaSelect::integerlist( $year, $year_end, '1', 'cardexp_year' ); ?>
        <?php /* <input type="text" name="cardexp" size="10" value="<?php echo !empty($vars->prepop['x_exp_date']) ? ($vars->prepop['x_exp_date']) : '' ?>" /> */ ?>
        </td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_('COM_TIENDA_CREDIT_CARD_ID') ?></td>
        <td>
            <input type="text" name="cardcv2" size="10" value="" />
            <br/>
            <a href="javascript:void(0);" onclick="window.open('index.php?option=com_tienda&view=checkout&task=doTask&element=payment_sagepayments&elementTask=showCVV&tmpl=component', '<?php echo JText::_('COM_TIENDA_WHERE_IS_MY_CARD_ID') . "?"; ?>', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=600, height=550');"><?php echo JText::_('COM_TIENDA_WHERE_IS_MY_CARD_ID') . "?"; ?></a>
        </td>
    </tr>
</table>
