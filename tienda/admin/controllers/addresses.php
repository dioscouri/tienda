<?php
/**
 * @version	1.5
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
			$fulladdress .= '<li><b>'.JText::_('Using Selected Address:').'</b></li>';
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