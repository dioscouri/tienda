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

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgTiendaRequireTaxNumber extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'requiretaxnumber';
    
	function plgTiendaRequireTaxNumber(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
	function onAfterDisplayAddressDetails($state, $prefix)
	{
		 $vars = new JObject();
		 $vars->state = $state;
		 $vars->prefix = $prefix;
		 $html = $this->_getLayout('default', $vars);
		 echo $html;
	}
    
    /**
     * 
     * @param array $values     The input values from the form
     * @return unknown_type
     */
    function onValidateSelectShipping( $values )
    {
        $return = new JObject();
        $return->error = null; // boolean
        $return->message = null; // string
        
        $params = $this->params;
        
        if (!empty($values['billing_address_id']))
        {
            // Load the address from the database
            $model = JModel::getInstance( 'Addresses', 'TiendaModel' );
            $model->setId( $values['billing_address_id'] );
            $address = $model->getItem();
            
            // if not, what do you want to do?
            if (!empty($address->company) &&  empty($address->tax_number)  && $params->get('require_tax', 0))
            {
                $return->error = true; // boolean
                $return->message = JText::_( "Stored Address Missing Tax Number" ); // string                
            }
            
        	// if not, what do you want to do?
            if (empty($address->personal_id_number)  && $params->get('require_personal_id', 0))
            {
                $return->error = true; // boolean
                $return->message = JText::_( "Stored Address Missing Personal Id Number" ); // string                
            }

        }
        else
        {
	        if (!empty($values['billing_input_company']) &&  empty($values['billing_input_tax_number']) && $params->get('require_tax', 0))
	        {
	            $return->error = true; // boolean
	            $return->message = JText::_( "INCLUDE_BILLING_TAX_NUMBER" ); // string
	        }
	        
	   		if ( empty($values['billing_input_personal_id_number']) && $params->get('require_personal_id', 0))
	        {
	            $return->error = true; // boolean
	            $return->message = JText::_( "INCLUDE_BILLING_PERSONAL_ID" ); // string
	        }
        }
        
        return $return;
    }
}
