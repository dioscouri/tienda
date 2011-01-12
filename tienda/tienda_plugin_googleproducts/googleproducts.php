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
    
	function plgTiendaGoogleProducts(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		
		$this->account_id = $this->params->get('account_id', '');
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
		// Google API url
		$url = $this->getAPIUrl('insert');
		
		// perform the insertion
		Tienda::load('TiendaArrayToXML', 'library.xml');
		
		// Populate the xml request
		$xml = array();
		$xml['app:control']['sc:required_destination']['attributes']['dest'] = 'ProductSearch';
		$xml['title'] = $product->product_name;
		$xml['content'] = $product->product_description;
		$xml['content']['attributes']['type'] = 'text/html';
		$xml['sc:id'] = $product->product_id;
		
		$xml['link']['attributes']['rel'] = 'alternate';
		$xml['link']['attributes']['type'] = 'text/html';
		$xml['link']['attributes']['href'] = TiendaHelperRoute::product($product->product_id);
		
		// Send the request
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
}
