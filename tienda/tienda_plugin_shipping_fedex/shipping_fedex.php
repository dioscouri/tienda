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

class plgTiendaShipping_Fedex extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_fedex'; 
	
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
    
    function getFedexServices()
    {
        $fedexService['EUROPE_FIRST_INTERNATIONAL_PRIORITY'] = JText::_( 'EUROPE_FIRST_INTERNATIONAL_PRIORITY' );
        $fedexService['FEDEX_1_DAY_FREIGHT']    = JText::_( 'FEDEX_1_DAY_FREIGHT' );
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
        
        return $fedexService;
    }

    /**
     * Gets the list of enabled services
     */
    function getServices()
    {
        $fedexServices = $this->getFedexServices();
        $services = array();
        $services_list = @preg_replace( '/\s/', '', $this->params->get( 'services' ) );
        $services_array = explode( ',', $services_list );
        foreach ($services_array as $service)
        {
            if (array_key_exists($service, $fedexServices))
            {
                $services[$service] = $fedexServices[$service];
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
        $vars->list = $this->getFedexServices();
        $vars->services = $this->getServices();
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }
    
    function sendRequest( $address, $orderItems )
    {
        $rates = array();
        
        require_once( dirname( __FILE__ ).DS.'shipping_fedex'.DS."fedex.php" );

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
                $product_totalWeight = $product->product_weight * $item->orderitem_quantity;
                $packageCount = $packageCount + 1;
                
                $weight = array(
                    'Value' => (int) $product_totalWeight ,
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
            $fedex = new TiendaFedexShip;
            
            $fedex->setKey($key);
            $fedex->setPassword($password);
            $fedex->setAccountNumber($billAccount);
            $fedex->setMeterNumber($meter);
            $fedex->setService($service, $serviceName);
            $fedex->setPayorType("SENDER");
            $fedex->setCarrierCode("FDXE");
            $fedex->setDropoffType("REGULAR_PICKUP");
            $fedex->setPackaging("YOUR_PACKAGING");
            
            $fedex->packageLineItems = $packages;
            $fedex->setPackageCount($packageCount);
                        
            $fedex->setOriginAddressLine($this->shopAddress->address_1);
            $fedex->setOriginAddressLine($this->shopAddress->address_2);
            $fedex->setOriginCity($this->shopAddress->city);
            $fedex->setOriginStateOrProvinceCode($this->shopAddress->zone_code);
            $fedex->setOriginPostalCode($this->shopAddress->zip);
            $fedex->setOriginCountryCode($this->shopAddress->country_isocode_2);
            
            $fedex->setDestAddressLine($address->address_1);
            $fedex->setDestAddressLine($address->address_2);
            $fedex->setDestCity($address->city);
            $fedex->setDestStateOrProvinceCode($address->zone_code);
            $fedex->setDestPostalCode($address->postal_code);
            $fedex->setDestCountryCode($address->country_code);
                        
            if ($fedex->getRate())
            {
                $fedex->rate->summary['element'] = $this->_element;
                $rates[] = $fedex->rate->summary;
            }
        }
        
        return $rates;
        
    }

    /**
     * 
     * Writes to the log file
     * @param unknown_type $client
     * @return unknown_type
     */
	protected function writeToLog($client)
	{  
		$file = $this->_element . '.txt';
		
		$text = '';
		if (JFile::exists($file))
		{
		    $text = JFile::read( $file );
		}
		
		$text .= sprintf("\r%s:- %s",date("D M j G:i:s T Y") , $client->__getLastRequest(). "\n\n" . $client->__getLastResponse());
		JFile::write( $file, $text );
	}
    
}
