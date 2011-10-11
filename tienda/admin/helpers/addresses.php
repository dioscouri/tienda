<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaHelperAddresses extends TiendaHelperBase
{
	/*
	 * Gets data about which address fields should be visible and validable on a form
	 * 
	 * @params $address_type	Address type
	 * 
	 * @return 2-dimensional associative array with data
	 */
	static function getAddressElementsData( $address_type )
	{
		$config = TiendaConfig::getInstance();
		$elements = array();
		$elements['address_name'] = array( 
				$config->get('show_field_title', '3') == '3' || $config->get('show_field_title', '3') == $address_type,
		 		$config->get('validate_field_title', '3') == '3' || $config->get('validate_field_title', '3') == $address_type
																			);
		$elements['first_name'] = array( 
				$config->get('show_field_name', '3') == '3' || $config->get('show_field_name', '3') == $address_type,
		 		$config->get('validate_field_name', '3') == '3' || $config->get('validate_field_name', '3') == $address_type
																			);
	
		$elements['middle_name'] = array( 
				$config->get('show_field_middle', '3') == '3' || $config->get('show_field_middle', '3') == $address_type,
		 		$config->get('validate_field_middle', '3') == '3' || $config->get('validate_field_middle', '3') == $address_type
																			);
		$elements['last_name'] = array( 
				$config->get('show_field_last', '3') == '3' || $config->get('show_field_last', '3') == $address_type,
		 		$config->get('validate_field_last', '3') == '3' || $config->get('validate_field_last', '3') == $address_type
																			);
		$elements['address1'] = array( 
				$config->get('show_field_address1', '3') == '3' || $config->get('show_field_address1', '3') == $address_type,
		 		$config->get('validate_field_address1', '3') == '3' || $config->get('validate_field_address1', '3') == $address_type
																			);
		$elements['address2'] = array( 
				$config->get('show_field_address2', '3') == '3' || $config->get('show_field_address2', '3') == $address_type,
		 		$config->get('validate_field_address2', '3') == '3' || $config->get('validate_field_address2', '3') == $address_type
																			);
		$elements['country'] = array( 
				$config->get('show_field_country', '3') == '3' || $config->get('show_field_country', '3') == $address_type,
		 		$config->get('validate_field_country', '3') == '3' || $config->get('validate_field_country', '3') == $address_type
																			);
		$elements['city'] = array( 
				$config->get('show_field_city', '3') == '3' || $config->get('show_field_city', '3') == $address_type,
		 		$config->get('validate_field_city', '3') == '3' || $config->get('validate_field_city', '3') == $address_type
																			);
		$elements['zip'] = array( 
				$config->get('show_field_zip', '3') == '3' || $config->get('show_field_zip', '3') == $address_type,
		 		$config->get('validate_field_zip', '3') == '3' || $config->get('validate_field_zip', '3') == $address_type
																			);
		$elements['zone'] = array( 
				$config->get('show_field_zone', '3') == '3' || $config->get('show_field_zone', '3') == $address_type,
		 		$config->get('validate_field_zone', '3') == '3' || $config->get('validate_field_zone', '3') == $address_type
																			);
		$elements['phone'] = array( 
				$config->get('show_field_phone', '3') == '3' || $config->get('show_field_phone', '3') == $address_type,
		 		$config->get('validate_field_phone', '3') == '3' || $config->get('validate_field_phone', '3') == $address_type
																			);
		$elements['company'] = array( 
				$config->get('show_field_company', '3') == '3' || $config->get('show_field_company', '3') == $address_type,
		 		$config->get('validate_field_company', '3') == '3' || $config->get('validate_field_company', '3') == $address_type
																			);
		$elements['tax_number'] = array( 
				$config->get('show_field_tax_number', '3') == '3' || $config->get('show_field_tax_number', '3') == $address_type,
		 		$config->get('validate_field_tax_number', '3') == '3' || $config->get('validate_field_tax_number', '3') == $address_type
																			);

		return $elements;
	}
}
