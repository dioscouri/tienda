<?php
/**
 * @version	1.5
 * @package	Ambrasubs
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/**
 * Ambrasubs PayPalPro API caller
 * 
 * Sends NVP requests to PayPal server and receives responses
 *
 * @package		Joomla 
 * @since 		1.5
 */
class plgTiendaPayment_Paypalpro_Caller extends JObject
{
	/**
	 * Class constructor
	 * 
	 * @return void
	 * @access public
	 */
	function __construct() {}
	
	/**
	 * Encodes array data and convert it to the URL-string
	 * 
	 * @param array $data
	 * @return string
	 * @access protected
	 */
	function _getEncodedData($data) 
	{
		$url_items = array();
		
		foreach ($data as $field => $item) {
			$url_items[] = strtoupper($field) . '=' . urlencode($item);
		}		
				
		$url_str = implode('&', $url_items);
		return $url_str;
	}
	
	/**
	 * Decodes URL-string to an array
	 * 
	 * @param string $data_str
	 * @return array
	 * @access protected
	 */
	function _getDecodedData($data_str)
	{
		$data = array();
		$data_str_items = explode('&', $data_str);
		
		if (count($data_str_items)) {
			foreach ($data_str_items as $data_item) {
				list($key, $value) = explode('=', $data_item);
				
				$data[$key] = urldecode($value);
			}
		}
		
		return $data;
	}
	
	/**
	 * Sends a request to a server
	 * 
	 * This method uses cURL library
	 * 
	 * @param string $url
	 * @param array|object $data
	 * @return array
	 */
	function request($url, $data)
	{
		// convert data to one format
		if (is_object($data)) {
			$data = get_object_vars($data);
		}
		
		$data_str = $this->_getEncodedData($data);		
		
		// set the curl parameters
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		// turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		
		// set the $data_str as POST FIELD to cURL
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);

		//	getting response from server
		$response = curl_exec($ch);
		
		if (curl_errno($ch)) {
		 	$response['curl_error_no']	= curl_errno($ch);
		 	$response['curl_error_msg']	= curl_error($ch);
		}
		else {
			$response = $this->_getDecodedData($response);
		}		
		
		curl_close($ch);
		
		return $response;		
	}
        
}
