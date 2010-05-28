<?php
/**
 * @version	1.5
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
    
    function onGetShippingRates($element, $values){
    	
    	// Check if this is the right plugin
    	if (!$this->_isMe($element)) 
        {
            return null;
        }
        
	    $address = $values->getShippingAddress();
	    $orderItems = $values->getItems();
        
        $rates = $this->sendRequest($address, $orderItems);
        
		return $rates;
        
    }
    
    function viewConfig()
    {
        $html = $this->_getLayout('default', new JObject());
		
        return $html;
    }
    
    
	function sendRequest($address, $orderItems)
    {
    	@ini_set("soap.wsdl_cache_enabled", "0");
    	
    	// Start the Soap Client
    	$wsdl = dirname( __FILE__ ).DS.'shipping_fedex'.DS.'RateService_v8.wsdl';
		$client = new SoapClient($wsdl, array('trace' => 1));
		
		$request = $this->getRequestData($address, $orderItems);

	    try 
		{
		    $response = $client->getRates( $request );
		    
		    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR')
		    {
		        $rates = $this->processResponse($response);
		        return $rates;
		    }
		    else
		    {
		        return array();
		    } 
		    
		    $this->writeToLog($client);    // Write to log file   
		
		} catch (SoapFault $exception) {
		  	return array();      
		}
    }
    
    protected function getRequestData($address, $orderItems)
    {
    	$key = $this->params->get('key');
    	$password = $this->params->get('password');
    	
    	$shipAccount = $this->params->get('account');
    	$meter = $this->params->get('meter');
    	
    	$billAccount = $this->params->get('account');
    	$dutyAccount = $this->params->get('account');
    	
    	$config = TiendaConfig::getInstance();
    	$shop_address_1 = $config->get('shop_address_1');
    	$shop_address_2 = $config->get('shop_address_2');
    	$shop_city = $config->get('shop_city');
    	$shop_country = $config->get('shop_country');
    	
    	$this->includeTiendaTables();
    	$table = JTable::getInstance('Countries', 'TiendaTable');
    	$table->load($shop_country);
    	$shop_country = $table->country_isocode_2;
    	
    	$shop_zone = $config->get('shop_zone');
    	
    	$table = JTable::getInstance('Zones', 'TiendaTable');
    	$table->load($shop_zone);
    	$shop_zone = $table->code;
    	
    	$shop_zip = $config->get('shop_zip');
    	
    	/* Credentials */
    	$request['WebAuthenticationDetail'] = array('UserCredential' =>
                                      array('Key' => $key, 'Password' => $password)); 
		$request['ClientDetail'] = array('AccountNumber' => $shipAccount, 'MeterNumber' => $meter);
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request v8 using PHP ***');
		$request['Version'] = array('ServiceId' => 'crs', 'Major' => '8', 'Intermediate' => '0', 'Minor' => '0');
		$request['ReturnTransitAndCommit'] = false;
		$request['RequestedShipment']['ShipTimestamp'] = date('c');
		
		/* Configurable Values */
		if( $this->params->get('dropoff', 0) != 0 )
			$request['RequestedShipment']['DropoffType'] = $this->params->get('dropoff'); // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
			
		if( $this->params->get('service', 0) != 0 )
			$request['RequestedShipment']['ServiceType'] =  $this->params->get('service'); // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
			
			
		$request['RequestedShipment']['PackagingType'] = $this->params->get('packaging', 'YOUR_PACKAGING'); // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
		
		/* Auto Compiled values */
		$request['RequestedShipment']['Shipper'] = array('Address' => array (
		                                               'StreetLines' => array($shop_address_1, $shop_address_2), // Destination details
		                                               'City' => $shop_city,
		                                               'StateOrProvinceCode' => $shop_zone,
		                                               'PostalCode' => $shop_zip,
		                                               'CountryCode' => $shop_country)); 		
		$request['RequestedShipment']['Recipient'] = array('Address' => array(
		                                          'StreetLines' => array($address->address_1,$address->address_2 ), // Origin details
		                                          'City' => $address->city,
		                                          'StateOrProvinceCode' => $address->zone_code,
		                                          'PostalCode' => $address->postal_code,
		                                          'CountryCode' => $address->country_code));
		$request['RequestedShipment']['ShippingChargesPayment'] = array('PaymentType' => 'SENDER',
		                                                        'Payor' => array('AccountNumber' => $billAccount,
		                                                                     'CountryCode' => $shop_country));
		$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
		//$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
		
		$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';  //  Or PACKAGE_SUMMARY
		$request['RequestedShipment']['RequestedPackageLineItems'] = array();
		
		$request['RequestedShipment']['PackageCount'] = 0;
		
		foreach($orderItems as $item)
		{
			$product = JTable::getInstance('Products', 'TiendaTable');
			$product->load($item->product_id);
			if($product->product_ships)
			{
				$request['RequestedShipment']['PackageCount'] = $request['RequestedShipment']['PackageCount']+1;
				
				$request['RequestedShipment']['RequestedPackageLineItems'][] = array('Weight' => array(
																							'Value' => $product->product_weight,
		                                                                                    'Units' => $this->params->get('weight_unit', 'KG')
																								),
		                                                                             'Dimensions' => array(
		                                                                             			'Length' => $product->product_length,
		                                                                                        'Width' => $product->product_width,
		                                                                                        'Height' => $product->product_height,
		                                                                                        'Units' => $this->params->get('dimension_unit', 'CM')
																								)
																			);
																			
			}
			
		}
		
		return $request;
    }
    
    protected function processResponse( $response )
    {
    	if( property_exists( $response, 'RateReplyDetails' ) )
    		$reply_details = $response->RateReplyDetails;
    	else
    		return array();
    	
    	if(!is_array($reply_details))
    	{
    		$temp = $reply_details;
    		$reply_details = array();
    		$reply_details[] = $temp;
    	}
    	
    	$i = 0;
    	foreach($reply_details as $details)
    	{
	    	$method = $details->ServiceType;
	    	
	    	$rate_details = $details->RatedShipmentDetails;
	    	
	    	foreach($rate_details as $rate )
	    	{
	    		$rate = $rate->ShipmentRateDetail;
	    		if( stripos($rate->RateType ,  'PAYOR_ACCOUNT') !== false )
	    		{
		    		$rates[$i]['name'] = $method;
		    		$rates[$i]['element'] = $this->_element;
		    		$rates[$i]['price'] = $rate->TotalBaseCharge->Amount;
		    		$rates[$i]['extra'] = $rate->TotalSurcharges->Amount;
		    		$rates[$i]['total'] = $rate->TotalNetFedExCharge->Amount;
		    		$rates[$i]['tax'] = $rate->TotalTaxes->Amount;
		    		$i++;
	    		}
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
