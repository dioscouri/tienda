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
        <td class="field_name"><?php echo JText::_( 'Credit Card Type' ) ?></td>
        <td><?php echo $vars->cctype_input ?></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Card Number' ) ?></td>
        <td><input type="text" name="cardnum" size="35" value="<?php echo !empty($vars->prepop['x_card_num']) ? ($vars->prepop['x_card_num']) : '' ?>" /></td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Expiration Date' ) ?></td>
        <td>
        <?php Tienda::load( 'TiendaSelect', 'library.select' ); ?>
        <?php $year = date('Y'); $year_end = $year + 25; ?>
        <?php echo TiendaSelect::integerlist( '1', '12', '1', 'cardexp_month' ); ?>
        <?php echo TiendaSelect::integerlist( $year, $year_end, '1', 'cardexp_year' ); ?>
        <?php /* <input type="text" name="cardexp" size="10" value="<?php echo !empty($vars->prepop['x_exp_date']) ? ($vars->prepop['x_exp_date']) : '' ?>" /> */ ?>
        </td>
    </tr>
    <tr>
        <td class="field_name"><?php echo JText::_( 'Credit Card ID' ) ?></td>
        <td>
            <input type="text" name="cardcvv" size="10" value="" />
            <br/>
            <a href="javascript:void(0);" onclick="window.open('index.php?option=com_tienda&view=checkout&task=doTask&element=payment_authorizedotnet&elementTask=showCVV&tmpl=component', '<?php echo JText::_( "Where is My Card ID" ) . "?"; ?>', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=600, height=550');"><?php echo JText::_( "Where is my card ID" ) . "?"; ?></a>
        </td>
    </tr>
</table>
