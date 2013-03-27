<?php

/**
 * @Id          $Id$
 * @Revision    $Revision$
 * @Date        $Date$
 * @version	1.5
 * @package	Tienda
 * @author 	$Author$
 * @link 	http://www.alatak.net
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

Tienda::load('TiendaPaymentPlugin', 'library.plugins.payment');

define('SIPS_RESULT_CODE', 1);
define('SIPS_RESULT_ERROR', 2);

class plgTiendaPayment_sips extends TiendaPaymentPlugin {

    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'payment_sips';

    function plgTiendaPayment_sips(& $subject, $config) {
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

        $this->os_info = $this->_getOperatingSystemInfo();        // set sips checkout type
        $order = DSCTable::getInstance('Orders', 'TiendaTable');
        $order->load($data['order_id']);
        $merchant_id = $this->_getMerchantId();
        if (!$pathfile = $this->_getPathfileFileName()) {
            JError::raiseWarning('500', JText::_('TIENDA_SIPS_NO_PATHFILE') . " " . $pathfile);
            return false;
        }
        $parm = "merchant_id=" . $merchant_id;
        // TO DO find merchant-country
        $parm.=" merchant_country=fr";
        $parm.=" amount=" . $data['orderpayment_amount'] * 100;
        $parm.=" currency_code=" . $this->_getCurrencyIsoCode(Tienda::getInstance()->get('currency'));
        $parm.=" pathfile=" . $pathfile;
        $parm.=" transaction_id=" . substr(time(), -5, 5) . rand(0, 9); //unique number during the day

        $parm.=" normal_return_url=" . JURI::root() . "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=" . $this->_element . "&paction=display_message&checkout=1";
        $parm.=" cancel_return_url=" . JURI::root() . "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=" . $this->_element . "&paction=cancel";
        $parm.=" automatic_response_url=" . JURI::root() . "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=" . $this->_element . "&paction=process&tmpl=component";

        $parm.=" language=" . $this->_getLanguageCode();
        // TO DO: get Joomla admin language
        $parm.=" merchant_language=" . $this->_getLanguageCode('');
        $parm.=" payment_means=" . $this->params->get('payment_means');
        $parm.=" header_flag=" . $this->params->get('header_flag');
        $parm.=" capture_day=" . $this->params->get('capture_day');
        $parm.=" capture_mode=" . $this->params->get('capture_mode');
        $parm.=" block_align=" . $this->params->get('block_align');
        $parm.=" block_order=" . $this->params->get('block_order');
        $parm.=" customer_email=" . $data['orderinfo']->user_email;
        $parm.=" order_id=" . $data['orderpayment_id']; //$data['order_id'];



        if ($this->_getOSName() != 'WIN') {
            $parm = escapeshellcmd($parm);
        }
        $vars->parm = $parm;
        $vars->bin_request = $this->_getBinPath("request");

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

                //$vars->message .= JText::_('TIENDA_SIPS_MESSAGE_PAYMENT_ACCEPTED');
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

        $send_email = false;

        $data = JRequest::getVar('DATA', '', 'post');
        // Invalidate data if it is in the wrong format
        if (!preg_match(':^[a-zA-Z0-9]+$:', $data)) {
            $data = '';
        }
        $this->os_info = $this->_getOperatingSystemInfo();        // set sips checkout type
// Next line is there to help me to debug
// should not be removed        
//$data = '2020333732603028502c2360532d5328532d2360522d4360502c4360502c3334502c3330512d2324562d5334592c3324512c33242a2c2360532c2360502d2324502c23602a2c2360552c2360502d433c552e3328572c4048512c2334502c23605435444533303048502c2338502c2324542c4360512c2360582c4344502e3334582d233c2a2c3360532c2360502d4324512d3344502c5048512c2330502c2360582c4360512c2360582c43442a2c3360512c2360502c4360505c224324502c4360502c3360512c4340532c233c552e3330535c224324502c2360502c2338502d5334592d232c2a2c2328582c2360502c4639525c224360522e3360502c2329463c4048502c2340502c2360532e333c585c224324502d4360502c233c512c3324512b4360505c224324512d2360502c2338522c2324522c23242a2c2360592c2360502c4639525c224360532c2360502c4321413b26255438364c512c232160383651413d26254b2b4659453d6048502c3338502c23605334552d2c5c224360502d5360502c2328502c4048502c5340502c2360522e33382a2c2330502c2360512d2425353524412f34455d2330352134353529255c224360532e3360502c2324505c224324502e3360502c23292e335048512c3360502c236051334048512c3324502c2360522c23602a2c2328562c2360502d5344572d3344522d53282adc970880f8cf2717';
//
        // RĂ©cupĂ©ration de la variable cryptĂ©e DATA
        $message = "message=" . $data;
        $pathfile.=" pathfile=" . $this->_getPathfileFileName($this->params->get('pathfile'));
        $bin_response = $this->_getBinPath("response");
        $parm = $message . " " . $pathfile;

        $result = exec("$bin_response $parm");
        $sips_response_array = explode("!", $result);


        list (,
                $code,
                $error,
                $merchant_id,
                $merchant_country,
                $amount,
                $transaction_id,
                $payment_means,
                $transmission_date,
                $payment_time,
                $payment_date,
                $response_code,
                $payment_certificate,
                $authorisation_id,
                $currency_code,
                $card_number,
                $cvv_flag,
                $cvv_response_code,
                $bank_response_code,
                $complementary_code,
                $complementary_info,
                $return_context,
                $caddie,
                $receipt_complement,
                $merchant_language,
                $language,
                $customer_id,
                $orderpayment_id,
                $customer_email,
                $customer_ip_address,
                $capture_day,
                $capture_mode,
                $data
                ) = $sips_response_array;




        if ($code != 0) {
            $errors[] = JText::_('TIENDA_SIPS_RETURN_CODE_INVALID') . " " . $code;
        } elseif ($error != 0) {
            $errors[] = JText::_('TIENDA_SIPS_RETURN_ERROR') . " " . $sips_error;
        } elseif ($merchant_id != $this->params->get('merchant_id')) {
            $errors[] = JText::_('TIENDA_SIPS_MERCHANT_ID_RECEIVED_INVALID');
        } else {
            // load the orderpayment record and set some values
            DSCTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables');
            $orderpayment = DSCTable::getInstance('OrderPayments', 'TiendaTable');
            $orderpayment->load($orderpayment_id);
            if (empty($orderpayment_id) || empty($orderpayment->orderpayment_id)) {
                $errors[] = JText::_('TIENDA_SIPS_INVALID ORDERPAYMENTID');
            }
        }
        if (count($errors)) {
            echo $errors;
            print_r($errors);

            $this->_sendErrorEmail($errors, $sips_response_array);
            return false;
        }


        // check the stored amount against the payment amount        
    	Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $stored_amount = TiendaHelperBase::number( $orderpayment->get('orderpayment_amount'), array( 'thousands'=>'' ) );
        $respond_amount = TiendaHelperBase::number( $amount, array( 'thousands'=>'' ) );
        if ($stored_amount != $respond_amount ) {
        	$errors[] = JText::_('TIENDA_SIPS_AMOUNT_INVALID');
            $errors[] = $stored_amount . " != " . $respond_amount;
        }

        // set the order's new status and update quantities if necessary
        Tienda::load('TiendaHelperOrder', 'helpers.order');
        Tienda::load('TiendaHelperCarts', 'helpers.carts');
        $order = DSCTable::getInstance('Orders', 'TiendaTable');
        $order->load($orderpayment->order_id);
        if (count($errors) or $response_code != '00') {
            if ($response_code != '00') {
                $orderpayment->transaction_details = JText::_('TIENDA_SIPS_RESPONSE_CODE') .
                        $response_code .
                        "\n" . JText::_('TIENDA_SIPS_RESPONSE_CODE_SIPS_ERROR') .
                        constant('TIENDA_SIPS_RESPONSE_' . $response['response_code']) .
                        "\n" . JText::_('TIENDA_SIPS_READ_SIPS_DOCUMENTATION');
            } else {
                $orderpayment->transaction_details = implode(" ", $errors);
            }
            $order->order_state_id = $this->params->get('failed_order_state', '10'); // FAILED
            // save the order
            if (!$order->save()) {
                $errors[] = $order->getError();
            }
            $send_email = false;
        } else {
            define($credit_card_type, $payment_means);
            $credit_card = split('\.', $card_number);
            $credit_card_number = $credit_card[0] . ' #### #### ##' . $credit_card[1];
// TO DO: DECODE TIME AND DATE
            $orderpayment->transaction_details = JText::_('TIENDA_SIPS_TRANSMISSION_DATE') . $transmission_date .
                    "\n" . JText::_('TIENDA_SIPS_RESPONSE_PAYMENT_TIME') . " : " . $payment_time .
                    "\n" . JText::_('TIENDA_SIPS_RESPONSE_PAYMENT_DATE') . " : " . $payment_date .
                    "\n" . JText::_('TIENDA_SIPS_RESPONSE_PAYMENT_CERTIFICATE') . " : " . $payment_certificate .
                    "\n" . JText::_('TIENDA_SIPS_RESPONSE_AUTHORIZATION_ID') . $authorisation_id .
                    //"\n".JText::_('TIENDA_SIPS_PAYMENT_MEANS') ." : ". $payment_means .
                    "\n" . JText::_('TIENDA_SIPS_RESPONSE_CREDIT_CARD_TYPE') . " : " . constant($credit_card_type) .
                    "\n" . JText::_('TIENDA_SIPS_RESPONSE_CREDIT_CARD_NUMBER') . " : " . $credit_card_number;


            $orderpayment->transaction_id = $transaction_id;
            $orderpayment->transaction_status = $response_code; // ???


            $order->order_state_id = $this->params->get('payment_received_order_state', '17');
            // save the order
            if (!$order->save()) {
                $errors[] = $order->getError();
            }
            // PAYMENT RECEIVED
            $this->setOrderPaymentReceived($orderpayment->order_id);

            // send email
            $send_email = true;
        }


        // save the orderpayment
        if (!$orderpayment->save()) {
            $errors[] = $orderpayment->getError();
        }

        if ($send_email) {
            // send notice of new order
            Tienda::load("TiendaHelperBase", 'helpers._base');
            $helper = TiendaHelperBase::getInstance('Email');
            $model = Tienda::getClass("TiendaModelOrders", "models.orders");
            $model->setId($orderpayment->order_id);
            $order = $model->getItem();
            $helper->sendEmailNotices($order, 'new_order');
        }

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

    // List of supported currencies by the ATOS system. ISO 4217 Liste des codes des monnaies et des types de fonds
    var $iso_currencies = array(
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
    // List of supported languages by the ATOS system
    var $languages = array(
        'gb-GB' => 'en',
        'de-DE' => 'de',
        'es-ES' => 'es',
        'it-IT' => 'it',
        'fr-FR' => 'fr',
    );
    var $default_currency = '978';
    // Default language in case it is not configured in the array above
    var $default_language = 'fr';
    // Operating System specific information (like name, command
    // line parameter delimiter, path separator).
    var $os_info;

    // ----------------------------------------------------------------
    // _getBinPath()
    //
    // Get file name in the SIPS protected directory handling
    // external files required by the ATOS SDK.
    function _getBinPath($name) {


        $name = $this->params->get('cgi_path') . $name;
        jimport('joomla.filesystem.file');
        if (!JFile::exists($name)) {
            return false;
        }

        return $name;
    }

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

        $value = $this->languages[$language];

        if ($value)
            return $value;

        return $this->default_language;
    }

    // ----------------------------------------------------------------
    // getCurrencyIsoCode()
    //
    // Get the currency code from the internal representation to the
    // representation expected by ATOS/SIPS.
    // The list of supported languages are in the array 'languages;
    // set at the constructor level.
    function _getCurrencyIsoCode($currency) {
        $value = $this->iso_currencies[$currency];

        if ($value)
            return $value;
        return $this->default_currency;
    }

    // ----------------------------------------------------------------
    // _getOperatingSystemInfo()
    //
    // Get information about the OS
    function _getOperatingSystemInfo() {
        $info = array(
            'name' => PHP_OS,
            'bin_suffix' => "",
        );

        if (substr($info['name'], 0, 3) == 'WIN') {
            $info['bin_suffix'] = ".exe";
        }


        return $info;
    }

// ----------------------------------------------------------------
    // _updatePathfileContent()
    //
    // Update the content of the pathfile according to actual configuration
    // Return the pathfile to be used when calling one of the ATOS
    // external applications.
    function _updatePathfileContent($pathfile) {



        if ($this->params->get('payment_server') == 'production') {
            return true;
        }

        jimport('joomla.filesystem.file');
        if (!JFile::exists($pathfile)) {
            JError::raiseWarning('500', JText::_('TIENDA_SIPS_PATHFILE_OPEN_FAILED') . " " . $pathfile);
            //return;
        }

        $user = JFactory::getUser();
        $date = JFactory::getDate();

        $certif = $this->_getCertificateFilePath();
        $parcom = $this->_getParcomFilePath();
        $parcomAndSuffix = $this->_getParcomAndSuffixFilePath();


        $content = "#########################################################################\n";
        $content .= "#\n";
        $content .= "#	Pathfile \n";
        $content .= "#\n";
        $content .= "#	Liste fichiers parametres utilisĂ©s par le module de paiement\n";
        $content .= "#	Mise Ă  jour le " . $date->toFormat("%Y-%m-%d 00:00:00") . " \n";
        $content .= "#	Par " . $user->get('name') . "\n";
        $content .= "#\n";
        $content .= "#########################################################################\n";
        $content .= "\n";
        $content .= "##-------------------------------------------------------------------------\n";
        $content .= "## Activation (YES) / DĂ�sactivation (NO) du mode DEBUG\n";
        $content .= "##-------------------------------------------------------------------------\n";
        $content .= "##\n";
        $content .= "DEBUG!" . $this->_getSipsDebug() . "!\n";
        $content .= "# ------------------------------------------------------------------------\n";
        $content .= "# Chemin vers le rĂ©pertoire des logos depuis le web alias  \n";
        $content .= "# Exemple pour le rĂ©pertoire www.merchant.com/xxx/payment/logo/\n";
        $content .= "# indiquer:\n";
        $content .= "# ------------------------------------------------------------------------\n";
        $content .= "#\n";
        $content .= "D_LOGO!" . JURI::root(true) . DS . "images" . DS . "sips/!\n";
        $content .= "#\n";
        $content .= "#------------------------------------------------------------------------\n";
        $content .= "#  Fichiers parametres lies a l'api paiement	\n";
        $content .= "#------------------------------------------------------------------------\n";
        $content .= "#\n";
        $content .= "# Fichier des paramĂ¨tres commerĂ§ant\n";
        $content .= "#\n";
        $content .= "F_DEFAULT!" . $parcomAndSuffix . "!\n";
        $content .= "#\n";
        $content .= "# Certificat du commercant\n";
        $content .= "#\n";
        $content .= "F_CERTIFICATE!" . $certif . "!\n";
        $content .= "#\n";
        $content .= "# Fichier paramĂ¨tre commercant\n";
        $content .= "#\n";
        $content .= "F_PARAM!" . $parcom . "!\n";
        $content .= "#\n";
        $content .= "# Fichier des paramĂ¨tres commerĂ§ant\n";
        $content .= "#\n";

        $content .= "# --------------------------------------------------------------------------\n";
        $content .= "# 	end of file\n";
        $content .= "# --------------------------------------------------------------------------\n";


        if (!JFile::write($pathfile, $content)) {
            JError::raiseWarning('500', JText::_('TIENDA_SIPS_PATHFILE_WRITE_FAILED') . " " . $pathfile);

            return false;
        }
        JError::raiseNotice('500', JText::_('TIENDA_SIPS_PATHFILE_UPDATE_OK') . " " . $pathfile);

        return true;
    }

    function _getPathfileFileName() {

        $pathfileFileName = $this->params->get('pathfile') . "pathfile";
        if (!$this->_updatePathfileContent($pathfileFileName)) {
            return false;
        }
        return $pathfileFileName;
    }

    function _getOSName() {
        return $this->os_info['name'];
    }

    // to do : test if TESTING mmode or Production mode
    function _getMerchantId() {

        return $this->params->get('merchant_id');
    }

    function _getTestCertificateId() {

        $sips_test_certificates_id = array();
        $sips_test_certificates_id['e-transactions'] = '013044876511111';
        $sips_test_certificates_id['cyberplus'] = '038862749811111';
        $sips_test_certificates_id['mercanet'] = '082584341411111';
        $sips_test_certificates_id['sogenactif'] = '014213245611111';
        $sips_test_certificates_id['scellius'] = '014141675911111';
        $sips_test_certificates_id['sherlocks'] = '014295303911111';
        $sips_test_certificates_id['webaffaires'] = '014022286611111';
        return $sips_test_certificates_id[$this->params->get('payment_solution_name')];
    }

    function _getCertificateFilePath() {

        $certif = JPATH::clean($this->params->get('pathfile')) . 'certif';

        return $certif;
    }

    function _getParcomFilePath() {
        $parcom = JPATH::clean($this->params->get('pathfile')) . 'parmcom';

        return $parcom;
    }

    function _getParcomAndSuffixFilePath() {
        $parcom = JPATH::clean($this->params->get('pathfile')) . 'parmcom' . '.' .
                $this->params->get('payment_solution_name');

        return $parcom;
    }

    // ----------------------------------------------------------------
    // _getCertificate()
    //
    // Get a certificate name
    // depends if testing or production
    function _getCertificateId() {

        if ($this->params->get('payment_server') == 'test') {
            return $this->_getTestCertificateId();
        } else {
            return $this->params->get('merchant_id');
        }
    }

    // ----------------------------------------------------------------
    // _getCertificate()
    //
    // Get a certificate name
    // depends if testing or production
    function _getSipsDebug() {

        if ($this->params->get('payment_sips_debug')) {
            return "YES";
        } else {
            return "NO";
        }
    }

    function _sipsExecError($vars) {

        $message = JText::_('TIENDA_SIPS_REQUEST_EXEC_ERROR') . "<br />";
        $message.= JText::_('TIENDA_SIPS_REQUEST_EXEC_DIAG') . "<br />";

        $error = false;
        $message.= JText::_('TIENDA_SIPS_REQUEST_EXEC_DIAG_CGI_PATH');
        $message .= $vars->bin_request;
        if (!file_exists($vars->bin_request)) {
            $message.= "<br />\t" . JText::_('TIENDA_SIPS_REQUEST_EXEC_DIAG_CGI_FILE_EXIST_KO');
            $error = true;
        } else {
            $message.= "<br />\t" . JText::_('TIENDA_SIPS_REQUEST_EXEC_DIAG_CGI_FILE_EXIST_OK');
        }
        $message.= "<br /><br />" . JText::_('TIENDA_SIPS_REQUEST_EXEC_DIAG_CGI_FILEPERMISSIONS') . " " . $vars->bin_request . " : ";

        $message.=JPath::getPermissions($vars->bin_request);
        if (!$this->checkPermissionsExecute($vars->bin_request)) {
            if (!JPath::setPermissions($vars->bin_request, '0755')) {
                $message.=JText::_('TIENDA_SIPS_REQUEST_EXEC_DIAG_CGI_COULD_NOT_CHANNGEFILEPERMISSIONS');
            } else {
                $message.=JText::_('TIENDA_SIPS_REQUEST_EXEC_DIAG_CGI_FILEPERMISSIONS_CHANGED');
            }
            $error = true;
        } else {
            $message.="<br />" . JText::_('TIENDA_SIPS_REQUEST_EXEC_DIAG_CGI_FILEPERMISSIONS_OK');
        }

        if ($error) {
            $message.="<br /><br />" . JText::_('TIENDA_SIPS_REQUEST_EXEC_DIAG_ERROR_FOUND');
        } else {
            $message.="<br /><br />" . JText::_('TIENDA_SIPS_REQUEST_EXEC_DIAG_ERROR_NOT_FOUND');
        }

        if ($this->params->get('payment_server') != 'production') {
            echo $message;
            JError::raiseWarning('', JText::_('TIENDA_SIPS_REQUEST_EXEC_ERROR'));
        } else {
            $this->_sendErrorEmail($message, '');
        }
    }

    function checkPermissionsExecute($path) {
        $path = JPath::clean($path);
        $mode = @ decoct(@ fileperms($path) & 0777);

        if (strlen($mode) < 3) {
            return false;
        }
        $parsed_mode = '';
        for ($i = 0; $i < 3; $i++) {
            if (!$mode { $i } & 01) {
                return false;
            }
        }
        return true;
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

}

