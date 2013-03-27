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

    function plgTiendaPayment_cmcic_v30(& $subject, $config) {
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
        require_once(JPATH_PLUGINS . DS . 'tienda' . DS . 'payment_cmcic_v30_library' . DS . 'CMCIC_Tpe.inc.php');
        $vars = new JObject();
        
        $vars->order_id = $data['order_id'];

        $order = DSCTable::getInstance('Orders', 'TiendaTable');
        $order->load($data['order_id']);
        $sOptions = "";

        $vars->reference = $data['order_id'];
        $vars->montant = round($data['orderpayment_amount'], 2); // Amount : format  "xxxxx.yy" (no spaces)
        $vars->devise = $this->_getCurrencyIsoCode(Tienda::getInstance()->get('currency'));
        $vars->texteLibre = $data['orderpayment_id'];
        $vars->date = date("d/m/Y:H:i:s");
        $vars->langue = $this->_getLanguageCode('');
        $vars->email = $data['orderinfo']->user_email;
        $vars->CMCICVersion = $this->_getCMCICVersion();
        $vars->key = $this->params->get('key');
        $vars->tpe = $this->params->get('TPE');
        $vars->serverUrl = $this->_getServerUrl();
        $vars->societe = $this->params->get('societe');
// ----------------------------------------------------------------------------
        if ($this->params->get('payment_mode') == 'split') {
            /* TODO */
// between 2 and 4
//$sNbrEch = "4";
            $vars->NbrEch = $this->params->get('payment_split_mode');

// date echeance 1 - format dd/mm/yyyy
//$sDateEcheance1 = date("d/m/Y");
            $vars->DateEcheance1 = "";

// montant échéance 1 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance1 = "0.26" . $sDevise;
            $vars->MontantEcheance1 = "";

// date echeance 2 - format dd/mm/yyyy
//$sDateEcheance2 = date("d/m/Y", mktime(0, 0, 0, date("m") +1 , date("d"), date("Y")));
            $vars->DateEcheance2 = "";

// montant échéance 2 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance2 = "0.25" . $sDevise;
            $vars->MontantEcheance2 = "";

// date echeance 3 - format dd/mm/yyyy
//$sDateEcheance3 = date("d/m/Y", mktime(0, 0, 0, date("m") +2 , date("d"), date("Y")));
            $vars->DateEcheance3 = "";

// montant échéance 3 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance3 = "0.25" . $sDevise;
            $vars->MontantEcheance3 = "";

// date echeance 4 - format dd/mm/yyyy
//$sDateEcheance4 = date("d/m/Y", mktime(0, 0, 0, date("m") +3 , date("d"), date("Y")));
            $vars->DateEcheance4 = "";

// montant échéance 4 - format  "xxxxx.yy" (no spaces)
//$sMontantEcheance4 = "0.25" . $sDevise;
            $vars->MontantEcheance4 = "";
        }
// ----------------------------------------------------------------------------

        $oTpe = new CMCIC_Tpe($vars->CMCICVersion,
                        $vars->key,
                        $vars->tpe,
                        $vars->serverUrl,
                        $vars->societe,
                        $vars->langue,
                        $this->_getUrlOk(),
                        $this->_getUrlKo());

        $oHmac = new CMCIC_Hmac($oTpe);

        // Control String for support
        $CtlHmac = sprintf(CMCIC_CTLHMAC,
                        $oTpe->sVersion,
                        $oTpe->sNumero,
                        $oHmac->computeHmac(sprintf(CMCIC_CTLHMACSTR,
                                        $oTpe->sVersion,
                                        $oTpe->sNumero)));

// Data to certify
        $PHP1_FIELDS = sprintf(CMCIC_CGI1_FIELDS,
                        $oTpe->sNumero,
                        $vars->date,
                        $vars->montant,
                        $vars->devise,
                        $vars->reference,
                        $vars->texteLibre,
                        $oTpe->sVersion,
                        $oTpe->sLangue,
                        $oTpe->sCodeSociete,
                        $vars->email,
                        $vars->nbrEch,
                        $vars->dateEcheance1,
                        $vars->montantEcheance1,
                        $vars->dateEcheance2,
                        $vars->montantEcheance2,
                        $vars->dateEcheance3,
                        $vars->montantEcheance3,
                        $vars->dateEcheance4,
                        $vars->montantEcheance4,
                        $vars->options);

// MAC computation
        $vars->MAC = $oHmac->computeHmac($PHP1_FIELDS);
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
        // Process the payment
        $paction = JRequest::getVar('paction');

        $vars = new JObject();
        $data = JRequest::getVar('DATA', '', 'post');

        switch ($paction) {
            case "display_message":
                $checkout = JRequest::getInt('checkout');

                // get the order_id from the session set by the prePayment
                $mainframe = JFactory::getApplication();
                $order_id = (int) $mainframe->getUserState('tienda.order_id');
                $order = DSCTable::getInstance('Orders', 'TiendaTable');
                $order->load($order_id);
                $items = $order->getItems();

                $html = $this->_getLayout('message', $vars);


                break;
            case "process":
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
                echo $html; // TODO Remove this
                $app = JFactory::getApplication();
                $app->close();
                break;
            case "cancel":
                $vars->message = JText::_('TIENDA_SIPS_RESPONSE_CANCEL_1') . "<br />" . JText::_('TIENDA_SIPS_RESPONSE_CANCEL_2');
                $html = $this->_getLayout('cancel_message', $vars);
                break;
            default:
                $vars->message = JText::_('sips Message Invalid Action');
                // $html = $this->_getLayout('message', $vars);
                break;
        }

        return $html;
    }

    /**
     *
     * @return HTML
     */
    function _process() {


// Begin Main : Retrieve Variables posted by CMCIC Payment Server
        $CMCIC_bruteVars = $this->_getMethode();

// TPE init variables
        $oTpe = new CMCIC_Tpe($vars->CMCICVersion,
                        $vars->key,
                        $vars->tpe,
                        $vars->serverUrl,
                        $vars->societe,
                        $vars->langue,
                        $this->_getUrlOk(),
                        $this->_getUrlKo());

        $oHmac = new CMCIC_Hmac($oTpe);

// Message Authentication
        $cgi2_fields = sprintf(CMCIC_CGI2_FIELDS, $oTpe->sNumero,
                        $CMCIC_bruteVars["date"],
                        $CMCIC_bruteVars['montant'],
                        $CMCIC_bruteVars['reference'],
                        $CMCIC_bruteVars['texte-libre'],
                        $oTpe->sVersion,
                        $CMCIC_bruteVars['code-retour'],
                        $CMCIC_bruteVars['cvx'],
                        $CMCIC_bruteVars['vld'],
                        $CMCIC_bruteVars['brand'],
                        $CMCIC_bruteVars['status3ds'],
                        $CMCIC_bruteVars['numauto'],
                        $CMCIC_bruteVars['motifrefus'],
                        $CMCIC_bruteVars['originecb'],
                        $CMCIC_bruteVars['bincb'],
                        $CMCIC_bruteVars['hpancb'],
                        $CMCIC_bruteVars['ipclient'],
                        $CMCIC_bruteVars['originetr'],
                        $CMCIC_bruteVars['veres'],
                        $CMCIC_bruteVars['pares']
        );



// 1 . check answer
        if ($oHmac->computeHmac($cgi2_fields) == strtolower($CMCIC_bruteVars['MAC'])) {
// 2 . check order_id
            $order_id = $CMCIC_bruteVars['reference'];

            // load the orderpayment record and set some values
            DSCTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables');
            $orderpayment = DSCTable::getInstance('OrderPayments', 'TiendaTable');
            $orderpayment->load($orderpayment_id);
            if (empty($orderpayment_id) || empty($orderpayment->orderpayment_id)) {
                $errors[] = JText::_('TIENDA_SIPS_INVALID ORDERID');
                $this->_sendErrorEmail($errors, $sips_response_array);
                return false;
            }
// 3 . check bank return code
            // set the order's new status and update quantities if necessary
            Tienda::load('TiendaHelperOrder', 'helpers.order');
            Tienda::load('TiendaHelperCarts', 'helpers.carts');
            $order = DSCTable::getInstance('Orders', 'TiendaTable');
            $order->load($orderpayment->order_id);
            $retour = $CMCIC_bruteVars['code-retour'];

            switch ($retour) {
                case "Annulation" :
                    // Payment has been refused
                    $order->order_state_id = $this->params->get('failed_order_state', '10');
                    $orderpayment->transaction_details = JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_ANNULATION');
                    if (!$order->save()) {
                        $errors[] = $order->getError();
                    }
                    break;

                case "payetest":
                    if ($this->params->get('payment_server') != 'test') {
                        break;
                    }
                case "paiement":
                    // Payment has been accepted                 
                    $orderpayment->transaction_details = JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_CVX') . $CMCIC_bruteVars['cvx'] .
                            "\n" . JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_VLD') . " : " . $CMCIC_bruteVars['vld'] .
                            "\n" . JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_BRAND') . " : " . $this->_getCreditCardBrand($CMCIC_bruteVars['brand']) .
                            "\n" . JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_STATUS3D') . " : " . $this->_getStatus3D($CMCIC_bruteVars['status3d']) .
                            "\n" . JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_ORIGINECB') . " : " . $CMCIC_bruteVars['originecb'] .
                            "\n" . JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_BINCB') . " : " . $CMCIC_bruteVars['bincb'] .
                            "\n" . JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_ORIGINTR') . " : " . $CMCIC_bruteVars['originetr']
                    ;

                    $orderpayment->transaction_id = $CMCIC_bruteVars['numauto'];
                    //$orderpayment->transaction_status = $retour; // ???

                    $order->order_state_id = $this->params->get('payment_received_order_state', '17');
                    // save the order
                    if (!$order->save()) {
                        $errors[] = $order->getError();
                    }
                    // PAYMENT RECEIVED
                    $this->setOrderPaymentReceived($orderpayment->order_id);

                    // send email
                    $send_email = true;

                    break;
            }

            $receipt = CMCIC_CGI2_MACOK;
        }
        // HMAC is not OK, tell the bank
        else {
            $receipt = CMCIC_CGI2_MACNOTOK . $cgi2_fields;
            //  HMAC doesn't match -- tell admin
            $errors[] = JText::_('TIENDA_CMCIC_CGI2_MACNOTOK') . $cgi2_fields;
        }
        printf(CMCIC_CGI2_RECEIPT, $receipt);


        return count($errors) ? implode("\n", $errors) : 'processed';
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

    // URLs des serveurs de paiement
    var $_test_payment_server_urls = array(
        'obc' => 'https://ssl.paiement.banque-obc.fr/test/paiement.cgi',
        'cic' => 'https://ssl.paiement.cic-banques.fr/test/paiement.cgi',
        'cm' => 'https://paiement.creditmutuel.fr/test/paiement.cgi',
    );
    var $_production_payment_server_urls = array(
        'obc' => 'https://ssl.paiement.banque-obc.fr/paiement.cgi',
        'cic' => 'https://ssl.paiement.cic-banques.fr/paiement.cgi',
        'cm' => 'https://paiement.creditmutuel.fr/paiement.cgi',
    );
    // List of supported currencies by the CMCIC. ISO 4217 Liste des codes des monnaies et des types de fonds
    var $_iso_currencies = array(
        'EUR' => '978',
        'USD' => '840',
        'CHF' => '756',
        'GBP' => '826',
        'CAD' => '124',
        'JPY' => '392',
        'MXP' => '484',
        'TRL' => '792',
        'AUD' => '036',
        'NZD' => '554',
        'NOK' => '578',
        'BRC' => '986',
        'ARP' => '032',
        'KHR' => '116',
        'TWD' => '901',
        'SEK' => '752',
        'DKK' => '208',
        'KRW' => '410',
        'SGD' => '702',
    );
    // List of supported languages by the CMCIC
    var $_languages = array(
        'gb-GB' => 'EN',
        'de-DE' => 'DE',
        'es-ES' => 'ES',
        'it-IT' => 'IT',
        'fr-FR' => 'FR',
        'nl-NL' => 'NL',
    );
    // List of supported languages by the CMCIC
    var $_creditCardBrand = array(
        'VI' => 'Visa',
        'MC' => 'MasterCard',
    );
    var $_default_currency_alphabetic_code = 'EUR';
    // Default language in case it is not configured in the array above
    var $_default_language = 'fr';

    // ----------------------------------------------------------------
    // _getLanguageCode()
    //
    // Get the language code from the internal representation to the
    // value expected by ATOS/SIPS.
    // The list of supported languages are in the array 'languages;
    // set at the constructor level.
    function _getLanguageCode() {
        jimport('joomla.language.helper');
        $language = JLanguageHelper::detectLanguage();

        if (!in_array($language, $this->_languages)) {
            $value = $this->_languages[$language];
        } else {
            $value = $this->_default_language;
        }

        return $value;
    }

    // ----------------------------------------------------------------
    // getCurrencyIsoCode()
    //
    // Get the currency alphabetic code from the internal representation to the
    // representation expected by CMCIC

    function _getCurrencyIsoCode($currency_alphabetic_code) {

        if (!in_array($currency_alphabetic_code, $this->_iso_currencies)) {
            $currency_alphabetic_code = $this->_default_currency_alphabetic_code;
        }
        return $currency_alphabetic_code;
    }

    function _getServerUrl() {
        if ($this->params->get('payment_server') == 'test') {
            $url = $this->_test_payment_server_urls[$this->params->get('payment_bank')];
        } else {
            $url = $this->_production_payment_server_urls[$this->params->get('payment_bank')];
        }
        return $url;
    }

    function _getCMCICVersion() {
        return '3.0';
    }

    function _getUrlOK() {
        $urlOK = JURI::root() . "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=" . $this->_element . "&paction=display_message&checkout=1";

        return $urlOK;
    }

    function _getUrlKO() {
        $urlKO = JURI::root() . "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=" . $this->_element . "&paction=cancel";
        return $urlKO;
    }

    function _getCreditCardBrand($brand) {

        if (in_array($brand, $this->_creditCardBrand)) {
            $value = $this->_creditCardBrand[$brand];
        } else {
            $value = "???";
        }

        return $value;
    }

    function _getStatus3D($status3D) {

        if ($status3D == -1) {
            $status3Dtext = JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_STATUS3D_MINUS1');
        } else if ($status3D == 1) {
            $status3Dtext = JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_STATUS3D_1');
        } else if ($status3D == 2) {
            $status3Dtext = JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_STATUS3D_2');
        } else if ($status3D == 3) {
            $status3Dtext = JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_STATUS3D_3');
        } else if ($status3D == 4) {
            $status3Dtext = JText::_('TIENDA_CMCIC_RESPONSE_PAYMENT_STATUS3D_4');
        }

        return $status3Dtext;
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
        $mainframe = JFactory::getApplication();

        // grab config settings for sender name and email
        $config = Tienda::getInstance();
        $mailfrom = $config->get('emails_defaultemail', $mainframe->getCfg('mailfrom'));
        $fromname = $config->get('emails_defaultname', $mainframe->getCfg('fromname'));
        $sitename = $config->get('sitename', $mainframe->getCfg('sitename'));
        $siteurl = $config->get('siteurl', JURI::root());

        $recipients = $this->_getAdmins();
        $mailer = JFactory::getMailer();

        $subject = JText::sprintf('TIENDA_SIPS_EMAIL_PAYMENT_ERROR_SUBJECT', $sitename);

        foreach ($recipients as $recipient) {
            $mailer = JFactory::getMailer();
            $mailer->addRecipient($recipient->email);

            $mailer->setSubject($subject);
            $mailer->setBody(JText::sprintf('TIENDA_SIPS_EMAIL_PAYMENT_ERROR_BODY', $recipient->name, $sitename, $siteurl, $message, $paymentData));
            $mailer->setSender(array($mailfrom, $fromname));
            $sent = $mailer->send();
        }

        return true;
    }

    /**
     * Gets admins data
     *
     * @return array|boolean
     * @access protected
     */
    function _getAdmins() {
        $db = JFactory::getDBO();
        $q = "SELECT name, email FROM #__users "
                . "WHERE LOWER(usertype) = \"super administrator\" "
                . "AND sendEmail = 1 "
        ;
        $db->setQuery($q);
        $admins = $db->loadObjectList();

        if ($error = $db->getErrorMsg()) {
            JError::raiseError(500, $error);
            return false;
        }

        return $admins;
    }

    // ----------------------------------------------------------------------------
// function getMethode
//
// IN:
// OUT: Données soumises par GET ou POST / Data sent by GET or POST
// description: Renvoie le tableau des donn�es / Send back the data array
// ----------------------------------------------------------------------------

    function _getMethode() {
        if ($_SERVER["REQUEST_METHOD"] == "GET")
            return $_GET;

        if ($_SERVER["REQUEST_METHOD"] == "POST")
            return $_POST;

        die('Invalid REQUEST_METHOD (not GET, not POST).');
    }

}

