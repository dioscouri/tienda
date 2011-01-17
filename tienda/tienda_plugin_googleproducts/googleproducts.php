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

class plgTiendaGoogleProducts extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'googleproducts';
    
    var $account_id = '';
    
    var $helper = null;
    
	function plgTiendaGoogleProducts(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		
		$this->account_id = $this->params->get('account_id', '');
		
		// Load helper
		$this->helper = Tienda::getClass( 'TiendaHelperGoogle', 'googleproducts.helper', array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ) );
		
		// Params
		$this->helper->setUsername($this->params->get('username', ''));
		$this->helper->setPassword($this->params->get('password', ''));
		$this->helper->service = 'structuredcontent';
		$this->helper->source = JURI::base();
		
		
		$this->helper->source = 'http://www.weble.it';
	}
    
	/**
	 * If enabled, insert or update product on google
	 * @param $product
	 */
	function onAfterStoreProducts($product)
	{
		// Check if it's enabled automatic syncing and the user has defined the account id for google
		if(!$this->params->get('auto_sync', '1') || !$this->account_id)
		{
			return false;
		}
		
		// Now let's get serious: was this product already saved on google?
		$params = new JParameter(trim($product->product_params));
		$upgrade = $params->get('sent_to_google', '0');
		
		// Do an upgrade request
		if($upgrade)
		{
			if($this->upgradeProduct($product))
			{
				return true;
			}
			else
			{
				// Something went wrong
				JError::raiseWarning('GOOGLE_UPDATE_ERR', JText::_('Error while updating on google products: ') . $this->getError());
				return false;
			}
		}
		// new insertion
		else
		{
			if($this->insertProduct($product))
			{
				$params->set('sent_to_google', '1');
				$product->product_params = trim($params->toString());
				$product->save();
				return true;
			}
			else
			{
				// Something went wrong
				JError::raiseWarning('GOOGLE_INSERT_ERR', JText::_('Error while inserting in google products: ') . $this->getError());
				return false;
			}
		}
		
	}
	
	/**
	 * If enabled, delete product on google
	 * @param $product
	 */
	function onAfterDeleteProducts($product, $product_id)
	{
		// Check if it's enabled automatic syncing and the user has defined the account id for google
		if(!$this->params->get('auto_sync', '1') || !$this->account_id)
		{
			return false;
		}
		
		// Now let's get serious: was this product already saved on google?
		$params = new JParameter(trim($product->product_params));
		$enabled = $params->get('sent_to_google', '0');
		
		if($enabled)
		{
			// Delete also on google
			if($this->deleteProduct($product))
			{
				return true;
			}
			else
			{
				// Something went wrong
				JError::raiseWarning('GOOGLE_DELETE_ERR', JText::_('Error while deleting in google products: ') . $this->getError());
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Sends a product to google base
	 * @param TiendaTableProducts $product
	 */
	protected function insertProduct($product)
	{
		// Get the request
		$xml = $this->getInsertXML($product);
		
		// Google API url
		$url = $this->getAPIUrl('insert');
		
		// Header with authentication
		$header = $this->getHeader();
		
		// Error
		if(!$header)
		{
			JError::raiseWarning('ERR', JText::_('Authentication Error: ' . $this->getError()));
			return false;
		}
		
		echo Tienda::dump($xml);
		
		// Send the request
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_POST, true);;
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
		
		$result = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		// Parse the result
		$errors = array();
		if($this->parseErrors($code, $result, $errors))
		{
			// Error!
			$this->setError(implode("\n", $errors));
			return false;
		}
		
		return true;
	} 
	
	/**
	 * Generates the xml for the insert request
	 * @param unknown_type $product
	 */
	protected function getInsertXML($product)
	{
		// perform the insertion
		Tienda::load('TiendaArrayToXML', 'library.xml');
		
		// Populate the xml request
		$xml = array();
		$xml['app:control']['sc:required_destination']['attributes']['dest'] = 'ProductSearch';
		
		// Title, id and description
		$xml['title'] = $product->product_name;
		$xml['content']['attributes']['type'] = 'text/html';
		$xml['content'] = $product->product_description;
		$xml['sc:id'] = $product->product_id;
		
		// Link to the product
		Tienda::load('TiendaHelperRoute', 'helpers.route');
		$xml['link']['attributes']['rel'] = 'alternate';
		$xml['link']['attributes']['type'] = 'text/html';
		//$xml['link']['attributes']['href'] = TiendaHelperRoute::product($product->product_id);
		$xml['link']['attributes']['href'] = 'http://www.weble.it/products/'.$product->product_id;
		
		// Condition
		$xml['scp:condition'] = 'new';
		
		// Price
		$currency_id = TiendaConfig::getInstance()->get('default_currencyid', '1');
		Tienda::load('TiendaTableCurrencies', 'tables.currencies');
        $currency = JTable::getInstance('Currencies', 'TiendaTable');
        $currency->load( (int) $currency_id );
        
		$xml['scp:price']['attributes']['unit'] = trim(strtoupper($currency->currency_code));
		$xml['scp:price']['@value'] = TiendaHelperBase::number(TiendaHelperProduct::getPrice($product->product_id)->product_price, array('num_decimals', '0'));
		
		// Manufacturer
		Tienda::load('TiendaTableManufacturers', 'tables.manufacturers');
		$manufacturer = JTable::getInstance('Manufacturers', 'TiendaTable');
		if($manufacturer->load($product->manufacturer_id))
		{
			$xml['scp:brand'] = $manufacturer->manufacturer_name;
		}
		
		// Create the request
		$null = null;
		$helper = new TiendaArrayToXML();
		$ns = array( array('name' => 'app', 'url' => "http://www.w3.org/2007/app"), array('name' =>'gd', 'url' => "http://schemas.google.com/g/2005"), array('name' => 'sc', 'url' => "http://schemas.google.com/structuredcontent/2009"), array( 'name' => 'scp', 'url' => "http://schemas.google.com/structuredcontent/2009/products"));
		$xml = $helper->toXml($xml, 'entry', $null, $ns, "http://www.w3.org/2005/Atom" );
		
		return $xml;
	}
	
	/**
	 * update a product on google base
	 * @param TiendaTableProducts $product
	 */
	protected function updateProduct($product)
	{
		// Google API url
		$url = $this->getAPIUrl('update', $product);
		
		// perform the update
	} 
	
	/**
	 * delete a product on google base
	 * @param TiendaTableProducts $product
	 */
	protected function deleteProduct($product)
	{
		// Google API url
		$url = $this->getAPIUrl('delete', $product);
		
		// perform the delete
	}
	
	/**
	 * Get the API url needed for the given operation
	 * @param enum(insert, update, delete) $type
	 * @param TiendaTableProduct $product
	 */
	protected function getAPIUrl($type, $product = null)
	{
		// automatic language from joomla
   		jimport('joomla.language.helper');
   		$lang = JLanguageHelper::detectLanguage();
   		// We need this for insert and update
   		$lang = explode("-", $lang);
		
		switch($type)
		{
			case 'delete':
				return 'https://content.googleapis.com/content/v1/'.$this->account_id.'/items/products/generic/online:'.$lang[0].':'.$lang[1].':'.$product->product_id;
				break;
			case 'update':
				return 'https://content.googleapis.com/content/v1/'.$this->account_id.'/items/products/generic/online::'.$lang[0].':'.$lang[1].':'.$product->product_id;
				break;
			default:
			case 'insert': 
				return 'https://content.googleapis.com/content/v1/'.$this->account_id.'/items/products/generic'; 
				break;
		}
	}
	
	/**
	 * Get the header data for a request
	 * @param string $type
	 */
	protected function getHeader()
	{
		// Auth Token
		$auth = $this->helper->authenticate();
		
		// Error?
		if(!$auth)
		{
			$this->setError($this->helper->getError());
			return false;
		}
		
		$header = Array("Content-Type: application/atom+xml", "Authorization:GoogleLogin Auth=".$auth);
		
		return $header;
	}
	
	protected function parseErrors($code, $response, &$errors)
	{
		// Error!
		if($code != '200' || $code != '201')
		{
			$result = simplexml_load_string($response);
			
			foreach($result as $r)
			{
				$errors[] = $r->internalReason . ' (' . $r->code.' : ' . $r->location . ' )';
			}
			
			echo Tienda::dump($errors);die();
			return false;
		}
		
		return true;
	}
}
