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
        
        $charge_tax = $this->params->get( 'charge_tax' );   
        //check params if we charge shipping tax      
        if($charge_tax)
        {
	        $geozones = $order->getShippingGeoZones();	
			$shipping_tax_rates = array();
			foreach($geozones as $geozone)
			{
				$shipping_tax_rates[$geozone->geozone_id] = $this->getTaxRate($geozone->geozone_id);
			}
			
			$newRates = array();
			foreach($rates as $rate)
			{
				$newRate = array();
				$newRate['name'] = $rate['name'];
				$newRate['code'] = $rate['code'];
				$newRate['price'] = $rate['price'];
				$newRate['extra'] = $rate['extra'];
				$shipping_method_tax_total = 0;
				foreach($shipping_tax_rates as $shipping_tax_rate)
				{				
					$shipping_method_tax_total += ($shipping_tax_rate/100) * ($newRate['price'] + $newRate['extra']);			    
				}
				$newRate['tax'] = $shipping_method_tax_total;
				$newRate['total'] =  $rate['total'] + $newRate['tax'];
				$newRate['element'] = $rate['element'];
				$newRates[] = $newRate;				
			}  
			
			unset($rates);
			$rates = $newRates;
        }        
	
		return $rates;        
    }
    
    /**
     * Returns the list of valid USPS Services
     * @return unknown_type
     */
    function getUSPSServices()
    {
        $uspsService['FIRST CLASS']         = JText::_('FIRST CLASS');
        $uspsService['PRIORITY']            = JText::_('PRIORITY');
        $uspsService['EXPRESS']             = JText::_('EXPRESS');
        $uspsService['PARCEL']              = JText::_('PARCEL');
        
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
  		if(empty($address->postal_code)) return $rates;
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
            $usps->setDebug($this->params->get( 'show_debug' ));
            $price = $usps->getPrice();
               
            if (!empty($price->error) && is_object($price->error))
            {            	
            	if($this->params->get( 'show_debug' ))
            	{
            		echo Tienda::dump($price->error);          
            	}            	    
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
     
	/**
     * Returns the tax rate for an item   
     * @param int $geozone_id
     * @return int
     */
    protected function getTaxRate( $geozone_id )
    {    	  	
    	$tax_class_id = $this->params->get( 'taxclass' );            
        $taxrate = "0.00000";
        
        $db = JFactory::getDBO();        
        Tienda::load( 'TiendaQuery', 'library.query' );  
        $query = new TiendaQuery();
        $query->select( 'tbl.tax_rate' );
        $query->from('#__tienda_taxrates AS tbl');       
        $query->where('tbl.tax_class_id = '.$tax_class_id);
        $query->where('tbl.geozone_id = '.$geozone_id);
        
        $db->setQuery( (string) $query );
        if ($data = $db->loadResult())
        {
            $taxrate = $data;
        }
        
        return $taxrate;
    }
}

//Notice: Undefined property: TiendaUpsRate::$shipperNumber in /var/www/dioscouri/plugins/tienda/shipping_ups/ups.php on line 311