<?php
/**
 * @package	Tienda
 * @author 	Dioscouri
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaShippingPlugin', 'library.plugins.shipping' );

class plgTiendaShipping_Fedex extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
	var $_element = 'shipping_fedex';
	
	/**
	 * Overriding 
	 * 
	 * @param $options
	 * @return unknown_type
	 */
	function onGetShippingView( $row )
	{
		if ( !$this->_isMe( $row ) )
		{
			return null;
		}
		
		$html = $this->viewConfig( );
		
		return $html;
	}
	
	function onGetShippingRates( $element, $order )
	{
		// Check if this is the right plugin
		if ( !$this->_isMe( $element ) )
		{
			return null;
		}
		
		$address = $order->getShippingAddress( );
		$address = $this->checkAddress( $address );
		$orderItems = $order->getItems( );
		
		$rates = $this->sendRequest( $address, $orderItems );	
        
		//TODO: need to used the FEDEX DutiesAndTaxes API
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
			
			if(!empty($shipping_tax_rates))
			{
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
        }        		
	
		return $rates;		
	}
	
	function getFedexServices( )
	{
		$fedexService['EUROPE_FIRST_INTERNATIONAL_PRIORITY'] = JText::_('COM_TIENDA_EUROPE_FIRST_INTERNATIONAL_PRIORITY');
		$fedexService['FEDEX_1_DAY_FREIGHT'] = JText::_('COM_TIENDA_FEDEX_1_DAY_FREIGHT');
		$fedexService['FEDEX_2_DAY'] = JText::_('COM_TIENDA_FEDEX_2_DAY');
		$fedexService['FEDEX_2_DAY_FREIGHT'] = JText::_('COM_TIENDA_FEDEX_2_DAY_FREIGHT');
		$fedexService['FEDEX_3_DAY_FREIGHT'] = JText::_('COM_TIENDA_FEDEX_3_DAY_FREIGHT');
		$fedexService['FEDEX_EXPRESS_SAVER'] = JText::_('COM_TIENDA_FEDEX_EXPRESS_SAVER');
		$fedexService['FEDEX_GROUND'] = JText::_('COM_TIENDA_FEDEX_GROUND');
		$fedexService['FIRST_OVERNIGHT'] = JText::_('COM_TIENDA_FIRST_OVERNIGHT');
		$fedexService['GROUND_HOME_DELIVERY'] = JText::_('COM_TIENDA_GROUND_HOME_DELIVERY');
		$fedexService['INTERNATIONAL_ECONOMY'] = JText::_('COM_TIENDA_INTERNATIONAL_ECONOMY');
		$fedexService['INTERNATIONAL_ECONOMY_FREIGHT'] = JText::_('COM_TIENDA_INTERNATIONAL_ECONOMY_FREIGHT');
		$fedexService['INTERNATIONAL_FIRST'] = JText::_('COM_TIENDA_INTERNATIONAL_FIRST');
		$fedexService['INTERNATIONAL_PRIORITY'] = JText::_('COM_TIENDA_INTERNATIONAL_PRIORITY');
		$fedexService['INTERNATIONAL_PRIORITY_FREIGHT'] = JText::_('COM_TIENDA_INTERNATIONAL_PRIORITY_FREIGHT');
		$fedexService['PRIORITY_OVERNIGHT'] = JText::_('COM_TIENDA_PRIORITY_OVERNIGHT');
		$fedexService['SMART_POST'] = JText::_('COM_TIENDA_SMART_POST');
		$fedexService['STANDARD_OVERNIGHT'] = JText::_('COM_TIENDA_STANDARD_OVERNIGHT');
		$fedexService['FEDEX_FREIGHT'] = JText::_('COM_TIENDA_FEDEX_FREIGHT');
		$fedexService['FEDEX_NATIONAL_FREIGHT'] = JText::_('COM_TIENDA_FEDEX_NATIONAL_FREIGHT');
		$fedexService['INTERNATIONAL_GROUND'] = JText::_('COM_TIENDA_INTERNATIONAL_GROUND');
		
		return $fedexService;
	}
	
	/**
	 * Gets the list of enabled services
	 */
	function getServices( )
	{
		$fedexServices = $this->getFedexServices( );
		$services = array( );
		$services_list = @preg_replace( '/\s/', '', $this->params->get( 'services' ) );
		$services_array = explode( ',', $services_list );
		foreach ( $services_array as $service )
		{
			if ( array_key_exists( $service, $fedexServices ) )
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
	function viewConfig( )
	{
		JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR . DS . 'components' );
		// TODO Finish this
		//        TiendaToolBarHelper::custom( 'enabled.enable', 'publish', 'publish', JText::_('Enable'), true, 'shippingTask' );
		//        TiendaToolBarHelper::custom( 'enabled.disable', 'unpublish', 'unpublish', JText::_('Disable'), true, 'shippingTask' );
		TiendaToolBarHelper::cancel( 'close', 'Close' );
		
		$vars = new JObject( );
		$vars->state = $this->_getState( );
		$id = JRequest::getInt( 'id', '0' );
		$form = array( );
		$form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
		$vars->form = $form;
		
		$plugin = $this->_getMe( );
		$plugin_id = $plugin->id;
		
		$vars = new JObject( );
		$vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
		$vars->id = $plugin_id;
		$vars->list = $this->getFedexServices( );
		$vars->services = $this->getServices( );
		$html = $this->_getLayout( 'default', $vars );
		
		return $html;
	}
	
	function sendRequest( $address, $orderItems )
	{
		$rates = array( );
		
		if ( empty( $address->postal_code ) )
		{
			return $rates;
		}
		
		require_once ( dirname( __FILE__ ) . DS . 'shipping_fedex' . DS . "fedex.php" );
		
		// Use params to determine which of these is enabled
		$services = $this->getServices( );
		
		$shipAccount = $this->params->get( 'account' );
		$meter = $this->params->get( 'meter' );
		$billAccount = $this->params->get( 'account' );
		$key = $this->params->get( 'key' );
		$password = $this->params->get( 'password' );
		
		$packageCount = 0;
		$packages = array( );
		
		foreach ( $orderItems as $item )
		{
			$product = JTable::getInstance( 'Products', 'TiendaTable' );
			$product->load( $item->product_id );
			if ( $product->product_ships )
			{
				$product_totalWeight = $product->product_weight * $item->orderitem_quantity;
				$packageCount = $packageCount + 1;
				
				$weight = array(
					'Value' => round( $product_totalWeight, 1 ), 
					'Units' => $this->params->get( 'weight_unit', 'KG' ) // get this from product?
				);
				
				$dimensions = array(
						'Length' => $product->product_length, 'Width' => $product->product_width, 'Height' => $product->product_height,
						'Units' => $this->params->get( 'dimension_unit', 'CM' ) // get this from product?
				);
				
				$packages[] = array(
					'Weight' => $weight, 'Dimensions' => $dimensions
				);
			}
		}
		
		foreach ( $services as $service => $serviceName )
		{
			$options['live'] = $this->params->get( 'site_mode' );   
			$fedex = new TiendaFedexShip($options);
			
			$fedex->setKey( $key );
			$fedex->setPassword( $password );
			$fedex->setAccountNumber( $billAccount );
			$fedex->setMeterNumber( $meter );
			$fedex->setService( $service, $serviceName );
			$fedex->setPayorType( "SENDER" );
			$fedex->setCarrierCode( "FDXE" );
			$fedex->setDropoffType( "REGULAR_PICKUP" );
			$fedex->setPackaging( "YOUR_PACKAGING" );
			
			$fedex->packageLineItems = $packages;
			$fedex->setPackageCount( $packageCount );
			
			$fedex->setOriginAddressLine( $this->shopAddress->address_1 );
			$fedex->setOriginAddressLine( $this->shopAddress->address_2 );
			$fedex->setOriginCity( $this->shopAddress->city );
			$fedex->setOriginStateOrProvinceCode( $this->shopAddress->zone_code );
			$fedex->setOriginPostalCode( $this->shopAddress->zip );
			$fedex->setOriginCountryCode( $this->shopAddress->country_isocode_2 );
			
			$fedex->setDestAddressLine( $address->address_1 );
			$fedex->setDestAddressLine( $address->address_2 );
			$fedex->setDestCity( $address->city );
			$fedex->setDestStateOrProvinceCode( $address->zone_code );
			$fedex->setDestPostalCode( $address->postal_code );
			$fedex->setDestCountryCode( $address->country_code );
			
			if ( $fedex->getRate( ) )
			{
				$fedex->rate->summary['element'] = $this->_element;
				$rates[] = $fedex->rate->summary;
			}
			else
			{
				$this->writeToLog( implode( "\n", $fedex->getErrors( ) ) );
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
