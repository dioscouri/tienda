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

Tienda::load('TiendaHelperBase', 'helpers._base');

class TiendaHelperGoogle extends TiendaHelperBase
{
	
	var $source = 'Tienda';
	var $service = 'structuredcontent';
	protected $username = '';
	protected $password = '';
	
	public function setUsername($u)
	{
		$this->username = $u;
	}
	
	public function setPassword($p)
	{
		$this->password = $p;
	}
	
	
	/**
	 * Get an authorization token for google services
	 * @param unknown_type $username
	 * @param unknown_type $password
	 */
	public function authenticate($username = '', $password = '')
	{
		if(strlen($username))
		{
			$this->username = $username;
		}

		if(strlen($password))
		{
			$this->password = $password;
		}
		
		// Programmatic Login
		$curl = curl_init('https://www.google.com/accounts/ClientLogin');
		curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type: application/x-www-form-urlencoded"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, "Email=".$this->username."&Passwd=".$this->password."&service=".$this->service."&source=".$this->source);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
		
		$result = curl_exec($curl);
		curl_close($curl);
		
		$result = explode("\n", $result);
		
		// return only the auth var
		$return = array();
		foreach($result as $r)
		{
			if(strlen($r))
			{
				$values = explode("=", $r);
				$k = $values[0];
				$v = $values[1];
				
				$return[strtolower($k)] = $v;
			}
		}
		
		// Error?
		if(array_key_exists("Error", $return))
		{
			$this->setError($return["Error"]);
			return false;
		}
		
		// Auth!
		return $return["auth"];
		
	}
}