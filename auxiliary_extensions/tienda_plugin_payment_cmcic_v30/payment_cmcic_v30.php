<?php

/**
 * @version	1.5
 * @package	Tienda
 * @author 	Valérie Isaksen
 * @link 	http://www.alatak.net
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load('TiendaPaymentPlugin', 'library.plugins.payment');

class plgTiendaPayment_cmcic_v30 extends TiendaPaymentPlugin {

    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'payment_cmcic_v30';

    function plgTiendaPayment_cmcic(& $subject, $config) {
        parent::__construct($subject, $config);
        $this->loadLanguage('', JPATH_ADMINISTRATOR);
    }

    /**
     * Prepares the payment form
     * and returns HTML Form to be displayed to the user
     * generally will have a message saying, 'confirm entries, then click complete order'
     *
     * Submit button target for onsite payments & return URL for offsite payments should be:
     * index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=xxxxxx
     * where xxxxxxx = $_element = the plugin's filename
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePayment($data) {
        // prepare the payment form

        $vars = new JObject();
        $vars->order_id = $data['order_id'];


         $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load($data['order_id']);
       $sOptions = "";

// ----------------------------------------------------------------------------
//  CheckOut Stub setting fictious Merchant and Order datas.
//  That's your job to set actual order fields. Here is a stub.
// -----------------------------------------------------------------------------

// Reference: unique, alphaNum (A-Z a-z 0-9), 12 characters max
$vars->sReference = "ref" . date("His");

// Amount : format  "xxxxx.yy" (no spaces)
$vars->sMontant = 1.01;

// Currency : ISO 4217 compliant
$vars->Devise  = "EUR";

// free texte : a bigger reference, session context for the return on the merchant website
$sTexteLibre = "Texte Libre";

// transaction date : format d/m/y:h:m:s
$vars->sDate = date("d/m/Y:H:i:s");

// Language of the company code
$vars->sLangue = "FR";

// customer email
$vars->sEmail = "test@test.zz";

// ----------------------------------------------------------------------------

// between 2 and 4
//$sNbrEch = "4";
$vars->sNbrEch = "";

// date echeance 1 - format dd/mm/yyyy
//$sDateEcheance1 = date("d/m/Y");
$vars->sDateEcheance1 = "";

// montant échéance 1 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance1 = "0.26" . $sDevise;
$vars->sMontantEcheance1 = "";

// date echeance 2 - format dd/mm/yyyy
//$sDateEcheance2 = date("d/m/Y", mktime(0, 0, 0, date("m") +1 , date("d"), date("Y")));
$vars->sDateEcheance2 = "";

// montant échéance 2 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance2 = "0.25" . $sDevise;
$vars->sMontantEcheance2 = "";

// date echeance 3 - format dd/mm/yyyy
//$sDateEcheance3 = date("d/m/Y", mktime(0, 0, 0, date("m") +2 , date("d"), date("Y")));
$vars->sDateEcheance3 = "";

// montant échéance 3 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance3 = "0.25" . $sDevise;
$vars->sMontantEcheance3 = "";

// date echeance 4 - format dd/mm/yyyy
//$sDateEcheance4 = date("d/m/Y", mktime(0, 0, 0, date("m") +3 , date("d"), date("Y")));
$vars->sDateEcheance4 = "";

// montant échéance 4 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance4 = "0.25" . $sDevise;
$vars->sMontantEcheance4 = "";

// ----------------------------------------------------------------------------

$oTpe = new CMCIC_Tpe($vars->sLangue);
$oHmac = new CMCIC_Hmac($oTpe);

// Control String for support
$CtlHmac = sprintf(CMCIC_CTLHMAC, $oTpe->sVersion, $oTpe->sNumero, $oHmac->computeHmac(sprintf(CMCIC_CTLHMACSTR, $oTpe->sVersion, $oTpe->sNumero)));

// Data to certify
$PHP1_FIELDS = sprintf(CMCIC_CGI1_FIELDS,     $oTpe->sNumero,
                                              $vars->sDate,
                                              $vars->sMontant,
                                              $vars->sDevise,
                                              $vars->sReference,
                                              $vars->sTexteLibre,
                                              $oTpe->sVersion,
                                              $oTpe->sLangue,
                                              $oTpe->sCodeSociete,
                                              $vars->sEmail,
                                              $vars->sNbrEch,
                                              $vars->sDateEcheance1,
                                              $vars->sMontantEcheance1,
                                              $vars->sDateEcheance2,
                                              $vars->sMontantEcheance2,
                                              $vars->sDateEcheance3,
                                              $vars->sMontantEcheance3,
                                              $vars->sDateEcheance4,
                                              $vars->sMontantEcheance4,
                                              $vars->sOptions);

// MAC computation
$sMAC = $oHmac->computeHmac($PHP1_FIELDS);


        $vars->oTpe = $oTpe;

        $html = $this->_getLayout('prepayment', $vars);

        return $html;
    }

    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _postPayment($data) {
           }

    /**
     *
     * @return HTML
     */
    function _process() {

        $send_email = false;

        
       
    }

    /**
     * Prepares variables for the payment form
     *
     * @return unknown_type
     */
    function _renderForm($data) {
        $user = JFactory::getUser();
        $vars = new JObject();

        $html = $this->_getLayout('form', $vars);

        return $html;
    }

    
    /**
     * Sends error messages to site administrators
     *
     * @param string $message
     * @param string $paymentData
     * @return boolean
     * @access protected
     */
    function _sendErrorEmail($message, $paymentData='') {
        $mainframe = & JFactory::getApplication();

      
        return true;
    }

    /**
     * Gets admins data
     *
     * @return array|boolean
     * @access protected
     */
    function _getAdmins() {
                 }

}

