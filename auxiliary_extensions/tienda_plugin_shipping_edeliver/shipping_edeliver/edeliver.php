<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class EDeliver 
{
	protected $url = 'http://drc.edeliver.com.au/ratecalc.asp';

	public function setDestPostalCode($code)
	{
		$this->destPostalCode = $code;
	}
	
	public function setOriginPostalCode($code)
	{
		$this->originPostalCode = $code;
	}
	
	public function setDestCountryCode($code)
	{
		$this->destCountryCode = $code;
	}
	
	public function setWeight($weight)
	{
		$this->weight = (int)$weight * 1000;
	}
	
	public function setLength($length)
	{
		$this->length = (int)$length * 10;
	}
	
	public function setWidth($width)
	{
		$this->width = (int)$width * 10;
	}
	
	public function setHeight($height)
	{
		$this->height = (int)$height * 10;
	}
	
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
	}
	
	public function setServiceType($code)
	{
		$this->serviceType = $code;
	}
	
	/**
	 * Sends the request
	 */
	public function sendRequest()
	{
		$vars = array();
		$vars['Height'] 				= $this->height;
		$vars['Length'] 				= $this->length;
		$vars['Width'] 					= $this->width;
		$vars['Height'] 				= $this->height;
		$vars['Weight'] 				= $this->weight;
		$vars['Pickup_Postcode'] 		= $this->originPostalCode;
		$vars['Destination_Postcode']	= $this->destPostalCode;
		$vars['Country'] 				= $this->destCountryCode;
		$vars['Service_Type'] 			= $this->serviceType;
		$vars['Quantity'] 			 	= $this->quantity;
		
		$query = "";
		foreach($vars as $key => $value)
		{
			$query .= $key . "=" . $value. "&";
		}
		
		// Reads the reply into array
		$reply = file( $this->url . '?' . $query );
		
		$rate = array();
		foreach($reply as $detail)
		{
			// Get detail and value
			$temp = explode("=", $detail);
			$key = $temp[0];
			$value = $temp[1];
			
			$rate[$key] = $value;
		}
		
		return $rate;
		
	}
}