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

class plgTiendaShipping_Dhl extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_dhl'; 
	
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
    
    function getDhlServices()
    {
        $dhlService['EUROPE_FIRST_INTERNATIONAL_PRIORITY'] = JText::_( 'EUROPE_FIRST_INTERNATIONAL_PRIORITY' );
        $dhlService['FEDEX_1_DAY_FREIGHT']    = JText::_( 'FEDEX_1_DAY_FREIGHT' );
        $fedexService['FEDEX_2_DAY']            = JText::_( 'FEDEX_2_DAY' );
        $fedexService['FEDEX_2_DAY_FREIGHT']    = JText::_( 'FEDEX_2_DAY_FREIGHT' );
        $fedexService['FEDEX_3_DAY_FREIGHT']    = JText::_( 'FEDEX_3_DAY_FREIGHT' );
        $fedexService['FEDEX_EXPRESS_SAVER']    = JText::_( 'FEDEX_EXPRESS_SAVER' );
        $fedexService['FEDEX_GROUND']           = JText::_( 'FEDEX_GROUND' );
        $fedexService['FIRST_OVERNIGHT']        = JText::_( 'FIRST_OVERNIGHT' );
        $fedexService['GROUND_HOME_DELIVERY']   = JText::_( 'GROUND_HOME_DELIVERY' );
        $fedexService['INTERNATIONAL_ECONOMY']  = JText::_( 'INTERNATIONAL_ECONOMY' );
        $fedexService['INTERNATIONAL_ECONOMY_FREIGHT'] = JText::_( 'INTERNATIONAL_ECONOMY_FREIGHT' );
        $fedexService['INTERNATIONAL_FIRST']    = JText::_( 'INTERNATIONAL_FIRST' );
        $fedexService['INTERNATIONAL_PRIORITY'] = JText::_( 'INTERNATIONAL_PRIORITY' );
        $fedexService['INTERNATIONAL_PRIORITY_FREIGHT'] = JText::_( 'INTERNATIONAL_PRIORITY_FREIGHT' );
        $fedexService['PRIORITY_OVERNIGHT']     = JText::_( 'PRIORITY_OVERNIGHT' );
        $fedexService['SMART_POST']             = JText::_( 'SMART_POST' );
        $fedexService['STANDARD_OVERNIGHT']     = JText::_( 'STANDARD_OVERNIGHT' );
        $fedexService['FEDEX_FREIGHT']          = JText::_( 'FEDEX_FREIGHT' );
        $fedexService['FEDEX_NATIONAL_FREIGHT'] = JText::_( 'FEDEX_NATIONAL_FREIGHT' );
        $fedexService['INTERNATIONAL_GROUND']   = JText::_( 'INTERNATIONAL_GROUND' );
        
        return $dhlService;
    }

    /**
     * Gets the list of enabled services
     */
    function getServices()
    {
        $dhlServices = $this->getDhlServices();
        $services = array();
        $services_list = @preg_replace( '/\s/', '', $this->params->get( 'services' ) );
        $services_array = explode( ',', $services_list );
        foreach ($services_array as $service)
        {
            if (array_key_exists($service, $dhlServices))
            {
                $services[$service] = $dhlServices[$service];
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
        $vars->list = $this->getDhlServices();
        $vars->services = $this->getServices();
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }
    
    function sendRequest( $address, $orderItems )
    {
        $rates = array();
        
        require_once( dirname( __FILE__ ).DS.'shipping_dhl'.DS."dhl.php" );

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
                    'Value' => $product->product_weight,
                    'Units' => $this->params->get('weight_unit', 'KG') // get this from product?
                );
                
                $dimensions = array(
                    'Length' => $product->product_length,
                    'Width' => $product->product_width,
                    'Height' => $product->product_height,
                    'Units' => $this->params->get('dimension_unit', 'CM') // get this from product?
                );
                
                $packages[] = array( 'Weight' => $weight, 'Dimensions' => $dimensions );
            }            
        }
        
        foreach ($services as $service=>$serviceName)
        {
            $dhl = new TiendaDhlShip;
            
            $dhl->setKey($key);
            $dhl->setPassword($password);
            $dhl->setAccountNumber($billAccount);
            $dhl->setMeterNumber($meter);
            $dhl->setService($service, $serviceName);
            $dhl->setPayorType("SENDER");
            $dhl->setCarrierCode("FDXE");
            $dhl->setDropoffType("REGULAR_PICKUP");
            $dhl->setPackaging("YOUR_PACKAGING");
            
            $dhl->packageLineItems = $packages;
            $dhl->setPackageCount($packageCount);
                        
            $dhl->setOriginAddressLine($this->shopAddress->address_1);
            $dhl->setOriginAddressLine($this->shopAddress->address_2);
            $dhl->setOriginCity($this->shopAddress->city);
            $dhl->setOriginStateOrProvinceCode($this->shopAddress->zone_code);
            $dhl->setOriginPostalCode($this->shopAddress->zip);
            $dhl->setOriginCountryCode($this->shopAddress->country_isocode_2);
            
            $dhl->setDestAddressLine($address->address_1);
            $dhl->setDestAddressLine($address->address_2);
            $dhl->setDestCity($address->city);
            $dhl->setDestStateOrProvinceCode($address->zone_code);
            $dhl->setDestPostalCode($address->postal_code);
            $dhl->setDestCountryCode($address->country_code);
                        
            if ($dhl->getRate())
            {
                $dhl->rate->summary['element'] = $this->_element;
                $rates[] = $dhl->rate->summary;
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
