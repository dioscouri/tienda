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

    /*     * **********************************
     * Note to 3pd:
     *
     * The methods between here
     * and the next comment block are
     * yours to modify
     *
     * ********************************** */

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
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load($data['order_id']);


        $parm = "merchant_id=" . $this->params->get('merchant_id');
        $parm.=" merchant_country=fr";
        $parm.=" amount=" . $data['orderpayment_amount'] * 100;
        $parm.=" currency_code=" . $this->_getCurrencyIsoCode(TiendaConfig::getInstance()->get('currency'));
        $parm.=" pathfile=" . $this->_getPathfileFileName($this->params->get('pathfile'));
        $parm.=" transaction_id=" . substr(time(), -5, 5) . rand(0, 9); //unique number during the day

        $parm.=" normal_return_url=" . JURI::root() . "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=" . $this->_element . "&paction=display_message&checkout=1";
        $parm.=" cancel_return_url=" . JURI::root() . "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=" . $this->_element . "&paction=cancel";
        $parm.=" automatic_response_url=" . JURI::root() . "index.php?option=com_tienda&view=checkout&task=confirmPayment&orderpayment_type=" . $this->_element . "&paction=process&tmpl=component";

        $parm.=" language=" . $this->_getLanguageCode('fr-FR');
        $parm.=" merchant_language=" . $this->_getLanguageCode('fr-FR');
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

        switch ($paction) {
            case "display_message":
                $checkout = JRequest::getInt('checkout');

                // get the order_id from the session set by the prePayment
                $mainframe = & JFactory::getApplication();
                $order_id = (int) $mainframe->getUserState('tienda.order_id');
                $order = JTable::getInstance('Orders', 'TiendaTable');
                $order->load($order_id);
                $items = $order->getItems();

                //$vars->message .= JText::_('TIENDA_SIPS_MESSAGE_PAYMENT_ACCEPTED');
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();

                break;
            case "process":
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
                echo $html; // TODO Remove this
                $app = & JFactory::getApplication();
                $app->close();
                break;
            case "cancel":
                $vars->message = JText::_('TIENDA_SIPS_RESPONSE_CANCEL_1') . "<br />" . JText::_('TIENDA_SIPS_RESPONSE_CANCEL_2');
                $html = $this->_getLayout('message', $vars);
                break;
            default:
                $vars->message = JText::_('sips Message Invalid Action');
                $html = $this->_getLayout('message', $vars);
                break;
        }

        return $html;
    }

    /**
     *
     * @return HTML
     */
    function _process() {
        $data = JRequest::getVar('DATA', '', 'post');
        // Invalidate data if it is in the wrong format
        if (!preg_match(':^[a-zA-Z0-9]+$:', $data))
            $data = '';

        $this->os_info = $this->_getOperatingSystemInfo();        // set sips checkout type

        //$data = '2020333730603028502c2360532d5360532d2360522d4360502c4360502c3334502e2328552e2330532d2324542c3324512c33242a2c2360532c2360502d2330532d53602a2c2360552c2360502d432c502d5334542c5048512c2334502c2360523054282a2c2360562c2360512d2328502c3360502e2324592c3334512c432c545c224324502c5360502c2338512d5324522d33282a2c3360542c2360502e2328502c3360502e2324595c224324502c3360502c2328502c6048512c2328502c2324502c3328582c4328532c233c572c4048512c2360502c2360562c432c502d533c525c224360522e2360502c2329463c4048502c4344502c2360523947282a2c2360582c2360502c5344572e6048512c2338502c2360572c3324512c3258502c6048512c3330502c2360562c4360512d2360515c224360502e3360502c2329463c4048502c5360502c232850383651413d26254b2c3360503026254c383731413a52594e3937302a2c2324562c2360502c552d33336048502c233c502c2360522c23282a2c232c582c2360502c432c555c224360542c2360502c333121353531283355293f3054253035253532313048502c5344502c2360512c6048512c2344502c2360522d24302a2c3324502c2360502c33242a2c3324512c2360502c4360505c224360522d4360502c233c592c5360572d3330535c224060608874a11017d2f86a';
        // Récupération de la variable cryptée DATA
        $message = "message=" . $data;
        $pathfile.=" pathfile=" . $this->_getPathfileFileName($this->params->get('pathfile'));
        $bin_response = $this->_getBinPath("response");
        $parm = $message . " " . $pathfile;

        $result = exec("$bin_response $parm");
        $sips_data = explode("!", $result);

        $errors = array();
        $code = $sips_data[1];
        $sips_error = $sips_data[2];
        if ($code == "" && ( $error == "" )) {
            $errors[] = JText::_('TIENDA_SIPS_RETURN_CODE_INVALID') . " " . $code;
        }

        if ($code != 0) {
            $errors[] = JText::_('TIENDA_SIPS_RETURN_ERROR') . " " . $sips_error;
        }

        $merchant_id = $sips_data[3];

        $amount = $sips_data[5];
        $transaction_id = $sips_data[6];
        $payment_means = $sips_data[7];
        $transmission_date = $sips_data[8];
        $payment_time = $sips_data[9];
        $payment_date = $sips_data[10];
        $response_code = $sips_data[11];
        $payment_certificate = $sips_data[12];
        $authorisation_id = $sips_data[13];
        $currency_code = $sips_data[14];
        $card_number = $sips_data[15];
        $cvv_flag = $sips_data[16];
        $cvv_response_code = $sips_data[17];
        $bank_response_code = $sips_data[18];
        $complementary_code = $sips_data[19];
        $i = 20;
        $complementary_info = $sips_data[$i++];
        $return_context = $sips_data[$i++];
        $caddie = $sips_data[$i++];
        $receipt_complement = $sips_data[$i++];
        $merchant_language = $sips_data[$i++];
        $language = $sips_data[$i++];
        $customer_id = $sips_data[$i++];
        $orderpayment_id = $sips_data[$i++];
        $customer_email = $sips_data[$i++];
        $customer_ip_address = $sips_data[$i++];
        $capture_day = $sips_data[$i++];
        $capture_mode = $sips_data[$i++];
        //$data = $sips_data[$i];
        // is the recipient correct?
        if ($merchant_id != $this->params->get('merchant_id')) {
            $errors[] = JText::_('TIENDA_SIPS_MERCHANT_ID_RECEIVED_INVALID');
        }


        // load the orderpayment record and set some values
        JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables');
        $orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
        $orderpayment->load($orderpayment_id);
        if (empty($orderpayment_id) || empty($orderpayment->orderpayment_id)) {
            $errors[] = JText::_('TIENDA_SIPS_INVALID ORDERPAYMENTID');
            return count($errors) ? implode("\n", $errors) : '';
        }
        $orderpayment->transaction_details = $data['transaction_details'];
        $orderpayment->transaction_id = $transaction_id;
        $orderpayment->transaction_status = $response_code;

        // check the stored amount against the payment amount
        $stored_amount = $orderpayment->get('orderpayment_amount') * 100;
        if ((int) $stored_amount != $amount) {
            $errors[] = JText::_('TIENDA_SIPS_AMOUNT_INVALID');
        }

        // set the order's new status and update quantities if necessary
        Tienda::load('TiendaHelperOrder', 'helpers.order');
        Tienda::load('TiendaHelperCarts', 'helpers.carts');
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load($orderpayment->order_id);
        if (count($errors) or !$response_code) {
            // if an error occurred
            $order->order_state_id = $this->params->get('failed_order_state', '10'); // FAILED
             // save the order
        if (!$order->save()) {
            $errors[] = $order->getError();
        }
        } else {
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

        return count($errors) ? implode("\n", $errors) :  'processed';
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
    function _sendErrorEmails($message, $paymentData) {
        $mainframe = & JFactory::getApplication();

        // grab config settings for sender name and email
        $config = &TiendaConfig::getInstance();
        $mailfrom = $config->get('emails_defaultemail', $mainframe->getCfg('mailfrom'));
        $fromname = $config->get('emails_defaultname', $mainframe->getCfg('fromname'));
        $sitename = $config->get('sitename', $mainframe->getCfg('sitename'));
        $siteurl = $config->get('siteurl', JURI::root());

        $recipients = $this->_getAdmins();
        $mailer = & JFactory::getMailer();

        $subject = JText::sprintf('TIENDA_SIPS_EMAIL_PAYMENT_NOT_VALIDATED_SUBJECT', $sitename);

        foreach ($recipients as $recipient) {
            $mailer = JFactory::getMailer();
            $mailer->addRecipient($recipient->email);

            $mailer->setSubject($subject);
            $mailer->setBody(JText::sprintf('TIENDA_SIPS_EMAIL_PAYMENT_NOT_VALIDATED_BODY', $recipient->name, $sitename, $siteurl, $message, $paymentData));
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
        $db = & JFactory::getDBO();
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
        'german' => 'de',
        'german-f' => 'de',
        'german-i' => 'de',
        'espanol' => 'es',
        'italian' => 'it',
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

        if ($this->os_info['path_separator'] != '/')
            $name = str_replace('/', $this->os_info['path_separator'], $name);
        return $name;
    }

    // ----------------------------------------------------------------
    // _getLanguageCode()
    //
    // Get the language code from the internal representation to the
    // value expected by ATOS/SIPS.
    // The list of supported languages are in the array 'languages;
    // set at the constructor level.
    function _getLanguageCode($language) {
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
            'quote' => "'",
            'path_separator' => "/",
            'bin_suffix' => "",
        );

        if (substr($info['name'], 0, 3) == 'WIN') {
            $info['quote'] = "";
            $info['path_separator'] = "\\";
            $info['bin_suffix'] = ".exe";
        }


        if (ini_get('safe_mode'))
        // Force not using quote with same mode
            $info['quote'] = "";

        return $info;
    }

    function _getPathfileFileName($pathfile) {

        return $this->os_info['quote'] . $pathfile . "pathfile" . $this->os_info['quote'];
    }

    function _getOSName() {

        return $this->os_info['name'];
    }

}
