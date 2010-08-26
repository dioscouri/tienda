<?php defined('_JEXEC') or die('Restricted access'); ?>


<?php

$result = exec("$vars->bin_request $vars->parm");
$sips_html = explode("!", "$result");
$code = $sips_html[1];
$error = $sips_html[2];
$html = $sips_html[3];

if (( $code == "" ) && ( $error == "" )) {
    $message = JTEXT::_('TIENDA_SIPS_EXEC_REQ_NOT_FOUND') . JTEXT::_('TIENDA_SIPS_EXEC_REQ_FILE');
  if (file_exists($vars->bin_request)) {
		print "<br />Le fichier $vars->bin_request existe";
	} else {
		print "<br />Le fichier $vars->bin_request n'existe pas";
	}

	echo "<br />Accès de ".$vars->bin_request." ".substr(sprintf('%o', fileperms($vars->bin_request)), -4);

}

//	Erreur, affiche le message d'erreur
else if ($code != 0) {
    echo JTEXT::_('TIENDA_SIPS_ERROR_REQUEST') . " " . $error;
    	if (file_exists($vars->bin_request)) {
		print "<br />Le fichier $vars->bin_request existe";
	} else {
		print "<br />Le fichier $vars->bin_request n'existe pas";
	}

	echo "<br />Accès de ".$vars->bin_request." ".substr(sprintf('%o', fileperms($vars->bin_request)), -4);


} else {
    echo JText::_("TIENDA_SIPS_PAYMENT_STANDARD_PREPARATION");

    echo $html;
}
?>



