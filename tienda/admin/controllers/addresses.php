<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerAddresses extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'addresses');
	}
	
	/**
	 * Sets the model's state
	 *
	 * @return array()
	 */
	function _setModelState()
	{
	    $state = parent::_setModelState();
	    $app = JFactory::getApplication();
	    $model = $this->getModel( $this->get('suffix') );
	    $ns = $this->getNamespace();
	
	    $state['filter_userid']         = $app->getUserStateFromRequest($ns.'filter_userid', 'filter_userid', '', '');
	    $state['filter_user']         = $app->getUserStateFromRequest($ns.'filter_user', 'filter_user', '', '');
	    $state['filter_address']         = $app->getUserStateFromRequest($ns.'filter_address', 'filter_address', '', '');
	
	    foreach (@$state as $key=>$value)
	    {
	        $model->setState( $key, $value );
	    }
	    return $state;
	}
	
	/**
     * Returns a selectlist of zones
     * Called via Ajax
     * 
     * @return unknown_type
     */
    function getZones()
    {
        Tienda::load( 'TiendaSelect', 'library.select' );
        $html = '';
        $text = '';
    	
    	$country_id = JRequest::getVar('country_id');
    	$name = JRequest::getVar('name', 'zone_id');
    	if (empty($country_id)) {
    	    $html = JText::_('COM_TIENDA_SELECT_COUNTRY_FIRST');
    	} else {
        	$html = TiendaSelect::zone( '', $name, $country_id );
    	}
    	
        $response = array();
        $response['msg'] = $html;
        $response['error'] = '';        
        
        // encode and echo (need to echo to send back to browser)
        echo ( json_encode($response) );

        return; 
    }
	
	function getAddressData(){
		// set response array
		$html = '';
		
		//get addressid from request
		$addressid = JRequest::getVar( 'addressid', '', 'request', 'int' );		

		//load model: addresses
		$model = $this->getModel( $this->get('suffix') );
		$model->setId($addressid);
		$item = $model->getItem();
		if(!empty($item)){
			$fulladdress = '<br/><ul class=\'addresslist\'>';
			$fulladdress .= '<li><b>'.JText::_('COM_TIENDA_USING_SELECTED_ADDRESS').'</b></li>';
			$fulladdress .= $this->setAddressOption($item->company);
			
			$fulladdress .= '<li>';
			$fulladdress .= $this->setAddressOptionMultiValue($item->title, ' ');
			$fulladdress .= $this->setAddressOptionMultiValue($item->first_name, ' ');
			$fulladdress .= $this->setAddressOptionMultiValue($item->middle_name, ' ');
			$fulladdress .= $this->setAddressOptionMultiValue($item->last_name, '');
			$fulladdress .= "</li>";
			
			$fulladdress .= $this->setAddressOption($item->address_1);
			$fulladdress .= $this->setAddressOption($item->address_2);
			$fulladdress .= $this->setAddressOption($item->city);
			$fulladdress .= $this->setAddressOption($item->postal_code);
			$fulladdress .= $this->setAddressOption($item->zone_name);
			$fulladdress .= $this->setAddressOption($item->country_name);
			$fulladdress .= "<li></li>";
			$fulladdress .= $this->setAddressOption($item->phone_1);
			$fulladdress .= $this->setAddressOption($item->phone_2);
			$fulladdress .= $this->setAddressOption($item->fax);
			$fulladdress .= '</ul>';
			$html = $fulladdress;			
		}

		$response = array();
		$response['msg'] = $html;
		$response['error'] = '';

		echo ( json_encode( $response ) );
		return;			
	}
	
	function setAddressOption($optionValue)
	{
		$optionText = '';
		if (isset($optionValue)){
			$optionText = '<li>'.$optionValue.'</li>'; 
		}			
		return $optionText;
	}
	
	function setAddressOptionMultiValue($optionValue, $separator)
	{
		$optionText = '';
		if (isset($optionValue)){
			$optionText .= $optionValue;
			if (strlen($separator)){
				$optionText .= 	$separator;
			}			
		}			
		return $optionText;
	}	
}

?>