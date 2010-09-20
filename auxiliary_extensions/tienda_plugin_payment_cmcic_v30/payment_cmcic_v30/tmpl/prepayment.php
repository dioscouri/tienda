<?php defined('_JEXEC') or die('Restricted access'); ?>


<!-- FORMULAIRE TYPE DE PAIEMENT / PAYMENT FORM TEMPLATE -->
<form action="<?php echo $vars->oTpe->sUrlPaiement;?>" method="post" id="PaymentRequest">

	<input type="hidden" name="version"             id="version"        value="<?php echo $vars->oTpe->sVersion;?>" />
	<input type="hidden" name="TPE"                 id="TPE"            value="<?php echo $vars->oTpe->sNumero;?>" />
	<input type="hidden" name="date"                id="date"           value="<?php echo $vars->sDate;?>" />
	<input type="hidden" name="montant"             id="montant"        value="<?php echo $vars->sMontant . $vars->sDevise;?>" />
	<input type="hidden" name="reference"           id="reference"      value="<?php echo $vars->sReference;?>" />
	<input type="hidden" name="MAC"                 id="MAC"            value="<?php echo $vars->sMAC;?>" />
	<input type="hidden" name="url_retour"          id="url_retour"     value="<?php echo $vars->oTpe->sUrlKO;?>" />
	<input type="hidden" name="url_retour_ok"       id="url_retour_ok"  value="<?php echo $vars->oTpe->sUrlOK;?>" />
	<input type="hidden" name="url_retour_err"      id="url_retour_err" value="<?php echo $vars->oTpe->sUrlKO;?>" />
	<input type="hidden" name="lgue"                id="lgue"           value="<?php echo $vars->oTpe->sLangue;?>" />
	<input type="hidden" name="societe"             id="societe"        value="<?php echo $vars->oTpe->sCodeSociete;?>" />
	<input type="hidden" name="texte-libre"         id="texte-libre"    value="<?php echo HtmlEncode($vars->sTexteLibre);?>" />
	<input type="hidden" name="mail"                id="mail"           value="<?php echo $vars->sEmail;?>" />
	<!-- Uniquement pour le Paiement fractionné -->
	<input type="hidden" name="nbrech"              id="nbrech"         value="<?php echo $vars->sNbrEch;?>" />
	<input type="hidden" name="dateech1"            id="dateech1"       value="<?php echo $vars->sDateEcheance1;?>" />
	<input type="hidden" name="montantech1"         id="montantech1"    value="<?php echo $vars->sMontantEcheance1;?>" />
	<input type="hidden" name="dateech2"            id="dateech2"       value="<?php echo $vars->sDateEcheance2;?>" />
	<input type="hidden" name="montantech2"         id="montantech2"    value="<?php echo $vars->sMontantEcheance2;?>" />
	<input type="hidden" name="dateech3"            id="dateech3"       value="<?php echo $vars->sDateEcheance3;?>" />
	<input type="hidden" name="montantech3"         id="montantech3"    value="<?php echo $vars->sMontantEcheance3;?>" />
	<input type="hidden" name="dateech4"            id="dateech4"       value="<?php echo $vars->sDateEcheance4;?>" />
	<input type="hidden" name="montantech4"         id="montantech4"    value="<?php echo $vars->sMontantEcheance4;?>" />
	<!-- -->
	<input type="submit" name="bouton"              id="bouton"         value="Connexion / Connection" />

</form>
<!-- FIN FORMULAIRE TYPE DE PAIEMENT / END PAYMENT FORM TEMPLATE -->





