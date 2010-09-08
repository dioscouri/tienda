<?php
/**
 * @package	Tienda
 * @author 	Dioscouri
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load('TiendaShippingPlugin', 'library.plugins.shipping');

class plgTiendaShipping_ups extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_ups'; 
	
    /**
     * Overriding 
     * 
     * @param $options
     * @return unknown_type
     */
    function onGetShippingView( $row )
    {
        if (!$this->_isMe($row)) 
        {
            return null;
        }
        
        $html = $this->viewConfig();       

        return $html;
    }
    
    function onGetShippingRates($element, $order)
    {    	
    	// Check if this is the right plugin
    	if (!$this->_isMe($element)) 
        {
            return null;
        }
        
	    $address = $order->getShippingAddress();
	    $address = $this->checkAddress( $address );
	    $orderItems = $order->getItems();
	    
        $rates = $this->sendRequest($address, $orderItems);
		return $rates;
        
    }
    
    function getUpsServices()
    {
        $services["14"]= JText::_('Next Day Air Early AM');
        $services["59"]= JText::_('Next Day Air Saver');
        $services["04"]= JText::_('2nd Day Air AM');
        $services["12"]= JText::_('3 Day Select');
        $services["03"]= JText::_('Ground');
        $services["11"]= JText::_('Standard');
        $services["07"]= JText::_('Worldwide Express');
        $services["08"]= JText::_('Worldwide Expedited');
        $services["54"]= JText::_('Worldwide Express Plus');
        $services["65"]= JText::_('UPS Saver');

        return $services;
    }

    /**
     * Gets the list of enabled services
     */
    function getServices()
    {
        $upsServices = $this->getUpsServices();
        $services = array(); 
        $services_list = $this->params->get( 'services' );
        foreach ($services_list as $service)
        {
            if (array_key_exists($service, $upsServices))
            {
                $services[$service] = $upsServices[$service];
            }
        }
        return $services;
    }
    
    /**
     * Displays the admin-side configuration form for the plugin
     * 
     */
    function viewConfig()
    {
        JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR.DS.'components' );
        // TODO Finish this
        //        TiendaToolBarHelper::custom( 'enabled.enable', 'publish', 'publish', JText::_('Enable'), true, 'shippingTask' );
        //        TiendaToolBarHelper::custom( 'enabled.disable', 'unpublish', 'unpublish', JText::_('Disable'), true, 'shippingTask' );
        TiendaToolBarHelper::cancel( 'close', 'Close' );
        
        $vars = new JObject();
        $vars->state = $this->_getState();
        $id = JRequest::getInt('id', '0');
        $form = array();
        $form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
        $vars->form = $form;
        
        $plugin = $this->_getMe(); 
        $plugin_id = $plugin->id;
        
        $vars = new JObject();
        $vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
        $vars->id = $plugin_id;
        $vars->list = $this->getUPSServices();
        $vars->services = $this->getServices();
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }
    
    function sendRequest( $address, $orderItems )
    {
        $rates = array();
        
        require_once( dirname( __FILE__ ).DS.'shipping_ups'.DS."ups.php" );

        // Use params to determine which of these is enabled
        $services = $this->getServices();

        $shipAccount = $this->params->get('account');
        $meter = $this->params->get('meter');
        $billAccount = $this->params->get('account');
        $key = $this->params->get('key');
        $password = $this->params->get('password');
        
        $packageCount = 0;
        $packages = array();
        
        foreach ( $orderItems as $item )
        {
            $product = JTable::getInstance('Products', 'TiendaTable');
            $product->load($item->product_id);
            if ($product->product_ships)
            {
                $packageCount = $packageCount + 1;
                $weight = array(
                    'Weight' => (int)$product->product_weight,
                    'UnitOfMeasurement' => array('Code' => $this->params->get('weight_unit', 'KGS') ) // get this from product?
                );
                
                $dimensions = array(
                    'Length' => (int)$product->product_length,
                    'Width' => (int)$product->product_width,
                    'Height' => (int)$product->product_height,
                    'UnitOfMeasurement' => array('Code' => $this->params->get('dimension_unit', 'CM') ) // get this from product?
                );
                
                $packages[] = array( 'PackageWeight' => $weight, 'Dimensions' => $dimensions, 'PackagingType' => array('Code' => $this->params->get('packaging', '02')) );            }            
        }
        
        foreach($services as $service => $name)
        {
	        $ups = new TiendaUpsRate;
	            
	        $ups->setKey($key);
	        $ups->setPassword($password);
	        $ups->setAccountNumber($billAccount);
	            
	        $ups->packageLineItems = $packages;
	        $ups->setPackageCount($packageCount);
	        $ups->setService($service, $name);
	        $ups->setPackaging($this->params->get('packaging', '02'));
	            
		    $ups->setOriginAddressLine($this->shopAddress->address_1);
		    $ups->setOriginAddressLine($this->shopAddress->address_2);
	        $ups->setOriginCity($this->shopAddress->city);
	        $ups->setOriginStateOrProvinceCode($this->shopAddress->zone_code);
	        $ups->setOriginPostalCode($this->shopAddress->zip);
	        $ups->setOriginCountryCode($this->shopAddress->country_isocode_2);
	            
	        $ups->setDestAddressLine($address->address_1);
	        $ups->setDestAddressLine($address->address_2);
	        $ups->setDestCity($address->city);
	        $ups->setDestStateOrProvinceCode($address->zone_code);
	        $ups->setDestPostalCode($address->postal_code);
	        $ups->setDestCountryCode($address->country_code);
	        
	            
	        if ($ups->getRate())
	        {
	        		$rate = $ups->rate;
	        	   	$rate->summary['element'] = $this->_element;
	            	$rates[] = $rate->summary;
	        }
	        
        }

        return $rates;
        
    }
    
	protected function writeToLog($client)
	{  
		$file = '';
		JFile::write( $file,  sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $client->__getLastRequest(). "\n\n" . $client->__getLastResponse()) );
	}
    
}
