<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPaymentPlugin', 'library.plugins.payment' );

class plgTiendaPayment_paypal_rest extends TiendaPaymentPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    public $_element    = 'payment_paypal_rest';

    public function plgTiendaPayment_paypal_rest(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $language = JFactory::getLanguage();
        $language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, 'en-GB', true);
        $language -> load('plg_tienda_'.$this->_element, JPATH_ADMINISTRATOR, null, true);
    }
}