<?php defined('_JEXEC') or die('Restricted access'); ?>


<?php

$sips_result = exec("$vars->bin_request $vars->parm");
$sips_values = explode("!", "$sips_result");



if (( $sips_values['1'] =='0')) {

    echo JText::_("TIENDA_SIPS_PAYMENT_STANDARD_PREPARATION");
    echo $sips_values['3'];

} else {
    $this->_sipsExecError($vars);
}
?>



