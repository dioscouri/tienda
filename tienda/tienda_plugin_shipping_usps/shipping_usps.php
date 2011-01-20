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

class plgTiendaShipping_Usps extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_usps'; 
	
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
    
    /**
     * (non-PHPdoc)
     * @see tienda/admin/library/plugins/TiendaShippingPlugin::onGetShippingRates()
     */
    function onGetShippingRates($element, $order)
    {    	
    	// Check if this is the right plugin
    	if (!$this->_isMe($element)) 
        {
            return null;
        }
                            
        $this->getShopAddress();
	    $address = $order->getShippingAddress();
	    $address = $this->checkAddress( $address );
	    $orderItems = $order->getItems();
	    
        $rates = $this->getRates($address, $orderItems);
		return $rates;
        
    }
    
    /**
     * Returns the list of valid USPS Services
     * @return unknown_type
     */
    function getUSPSServices()
    {
        $uspsService['FIRST CLASS']         = JText::_( 'FIRST CLASS' );
        $uspsService['PRIORITY']            = JText::_( 'PRIORITY' );
        $uspsService['EXPRESS']             = JText::_( 'EXPRESS' );
        $uspsService['PARCEL']              = JText::_( 'PARCEL' );
        
        return $uspsService;
    }

    /**
     * Gets the list of enabled services
     */
    function getServices()
    {
        $uspsServices = $this->getUSPSServices();
        $services = array();
        $services_list = $this->params->get( 'services' );
        $services_array = explode( ',', $services_list );
        foreach ($services_array as $service)
        {
            $service = trim($service);
            if (array_key_exists($service, $uspsServices))
            {
                $services[$service] = $uspsServices[$service];
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
        $vars->list = $this->getUSPSServices();
        $vars->services = $this->getServices();
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }
    
    /**
     * 
     * Enter description here ...
     * @param $address
     * @param $orderItems
     * @return unknown_type
     */
    function getRates( $address, $orderItems )
    {
        $rates = array();
        
        require_once( dirname( __FILE__ ).DS.'shipping_usps'.DS."usps.php" );

        // Use params to determine which of these is enabled
        $services = $this->getServices();
        
        $server_test = "http://testing.shippingapis.com/ShippingAPITest.dll";
        $server = "http://production.shippingapis.com/ShippingAPI.dll";
        $username = $this->params->get('username');
        $password = $this->params->get('password');
        $origin_zip = $this->shopAddress->zip;
        $container = $this->params->get('packaging');
        $country = $address->country_name;
        
        $totalWeight = 0;
        $packageCount = 0;
        $packages = array();
        
        foreach ( $orderItems as $item )
        {
            $product = JTable::getInstance('Products', 'TiendaTable');
            $product->load($item->product_id);
            if ($product->product_ships)
            {
                $totalWeight = $totalWeight + ( $product->product_weight * $item->orderitem_quantity );
                $packageCount = $packageCount + ( 1 * $item->orderitem_quantity );
            }            
        }
        
        //tienda bug 3207: the USPS API v2 only takes whole figures for the pounds field.
        // fix: calculate the pounds and ounces based on the total weight in pounds (LB)
        $totalPounds = floor($totalWeight);
        $totalOunces = ceil(($totalWeight-$totalPounds)*16);
                                    
        foreach ($services as $service=>$serviceName)
        {
            $usps = new TiendaUSPS;
            $usps->address = $address;
            $usps->setServer($server);
            $usps->setUserName($username);
            $usps->setPass($password);
            $usps->setService($service);
            $usps->setDestZip($address->postal_code);
            $usps->setOrigZip($origin_zip);
            $usps->setWeight($totalPounds, $totalOunces);
            $usps->setContainer($container);
            $usps->setCountry($country);
            $price = $usps->getPrice();
                        
            if (!empty($price->error) && is_object($price->error))
            {
                // return a nulled array
            }
                else
            {
                if (!empty($price->list))
                {
                    foreach ($price->list as $p)
                    {
                        if (get_class($p) == 'TiendaUSPSIntPrice')
                        {
                            if ($totalWeight < $p->maxweight)
                            {
                                $rate = array();
                                $rate['name']    = htmlspecialchars_decode( $p->svcdescription . " (" . $p->svccommitments . ")" );
                                $rate['code']    = $service;
                                $rate['price']   = $p->rate;
                                $rate['extra']   = "0.00";
                                $rate['total']   = $p->rate;
                                $rate['tax']     = "0.00";
                                $rate['element'] = $this->_element;
                                $rates[] = $rate;                                                            
                            }
                        }
                            else
                        {
                            $rate = array();
                            $rate['name']    = htmlspecialchars_decode( $p->mailservice );
                            $rate['code']    = $service;
                            $rate['price']   = $p->rate;
                            $rate['extra']   = "0.00";
                            $rate['total']   = $p->rate;
                            $rate['tax']     = "0.00";
                            $rate['element'] = $this->_element;
                            $rates[] = $rate;
                        }

                        
                    }                    
                }
            }
        }
                                    
        return $rates;
        
    }
}