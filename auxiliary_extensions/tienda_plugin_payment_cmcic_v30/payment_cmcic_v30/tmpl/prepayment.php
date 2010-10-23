<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Valérie Isaksen
 * @link 	http://www.alatak.net
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
?>
<?php defined('_JEXEC') or die('Restricted access'); ?>


<!-- FORMULAIRE TYPE DE PAIEMENT / PAYMENT FORM TEMPLATE -->
<form action="<?php echo $vars->serverUrl; ?>" method="post" id="PaymentRequest">

    <input type="hidden" name="version"             id="version"        value="<?php echo $vars->oTpe->sVersion; ?>" />
    <input type="hidden" name="TPE"                 id="TPE"            value="<?php echo $vars->oTpe->sNumero; ?>" />
    <input type="hidden" name="date"                id="date"           value="<?php echo $vars->date; ?>" />
    <input type="hidden" name="montant"             id="montant"        value="<?php echo $vars->montant . $vars->devise; ?>" />
    <input type="hidden" name="reference"           id="reference"      value="<?php echo $vars->reference; ?>" />
    <input type="hidden" name="MAC"                 id="MAC"            value="<?php echo $vars->MAC; ?>" />
    <input type="hidden" name="url_retour"          id="url_retour"     value="<?php echo $vars->oTpe->sUrlKO; ?>" />
    <input type="hidden" name="url_retour_ok"       id="url_retour_ok"  value="<?php echo $vars->oTpe->sUrlOK; ?>" />
    <input type="hidden" name="url_retour_err"      id="url_retour_err" value="<?php echo $vars->oTpe->sUrlKO; ?>" />
    <input type="hidden" name="lgue"                id="lgue"           value="<?php echo $vars->oTpe->sLangue; ?>" />
    <input type="hidden" name="societe"             id="societe"        value="<?php echo $vars->oTpe->sCodeSociete; ?>" />
    <input type="hidden" name="texte-libre"         id="texte-libre"    value="<?php echo HtmlEncode($vars->texteLibre); ?>" />
    <input type="hidden" name="mail"                id="mail"           value="<?php echo $vars->email; ?>" />
    <?php if ($vars->sNbrEch) {
    ?>
        <!-- Uniquement pour le Paiement fractionné -->
        <input type="hidden" name="nbrech"              id="nbrech"         value="<?php echo $vars->nbrEch; ?>" />
    <?php }
        if (@$vars->sDateEcheance1) { ?>
        <input type="hidden" name="dateech1"            id="dateech1"       value="<?php echo $vars->dateEcheance1; ?>" />
    <?php }
    if (@$vars->sMontantEcheance1) {
 ?>
        <input type="hidden" name="montantech1"         id="montantech1"    value="<?php echo $vars->montantEcheance1; ?>" />
    <?php }
    if (@$vars->sDateEcheance2) { ?>
        <input type="hidden" name="dateech2"            id="dateech2"       value="<?php echo $vars->dateEcheance2; ?>" />
    <?php }
    if (@$vars->sMontantEcheance2) { ?>
        <input type="hidden" name="montantech2"         id="montantech2"    value="<?php echo $vars->montantEcheance2; ?>" />
    <?php }
    if (@$vars->sDateEcheance3) { ?>
        <input type="hidden" name="dateech3"            id="dateech3"       value="<?php echo $vars->dateEcheance3; ?>" />
    <?php }
    if (@$vars->sMontantEcheance3) { ?>
        <input type="hidden" name="montantech3"         id="montantech3"    value="<?php echo $vars->montantEcheance3; ?>" />
    <?php }
    if (@$vars->sMontantEcheance4) { ?>
        <input type="hidden" name="dateech4"            id="dateech4"       value="<?php echo $vars->dateEcheance4; ?>" />
    <?php }
    if (@$vars->sDateEcheance1) { ?>
        <input type="hidden" name="montantech4"         id="montantech4"    value="<?php echo $vars->montantEcheance4; ?>" />
<?php } ?>
    <!-- -->
    <input type="submit" name="bouton"              id="bouton"         value="<?php echo JText::_('TIENDA_CMCIC_CLICK_HERE_TO_PAY'); ?>" />

</form>
<!-- FIN FORMULAIRE TYPE DE PAIEMENT / END PAYMENT FORM TEMPLATE -->





