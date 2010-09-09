<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * 
 * Derivative of work by:
 * @author  Algirdas Varnagiris <algirdas@varnagiris.net>
 * @link    http://www.varnagiris.net
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

/**
 * Base class for UPS Transactions
 * @author Rafael Diaz-Tushman
 * @author Daniele Rosario
 *
 */
class TiendaUps extends JObject
{
    var $request    = array(); // the request sent to dhl is stored here
    var $response   = array(); // the response from dhl is stored here
    var $wsdl       = null;
    var $accountNumber;
    var $meterNumber;
    
    function getClient()
    {
        if (empty($this->client))
        {
            @ini_set("soap.wsdl_cache_enabled", "0");
            $this->client = new SoapClient($this->wsdl, array('trace' => 1));
        }
        
        return $this->client;
    }
    
    /**
     * Creates an appropriately formatted request array
     * always overridden by child classes
     */
    function createRequest()
    {
         /* Credentials */
        $request['UsernameToken'] = array('Username' => $this->accountNumber, 'Password' => $this->password); 
        $request['ServiceAccessToken'] = array('AccessLicenseNumber' => $this->key); 
        
        $ns = 'http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0'; //Namespace of the WS. 

		//Body of the Soap Header. 
		$headerbody = array('ServiceAccessToken'=> array('AccessLicenseNumber' => $this->key), 
		                      'UsernameToken'=>array('Username'=>$this->accountNumber, 
		                                             'Password'=>$this->password)); 
		
		//Create Soap Header.        
		$header = new SOAPHeader($ns, 'UPSSecurity', $headerbody);        
		        
		//set the Headers of Soap Client. 
		$this->getClient()->__setSoapHeaders($header);        
    }
    
    function getRequest()
    {
        if (empty($this->request))
        {
            $this->createRequest();
        }
        return $this->request;
    }
    
    function setServer($server) {
        $this->server = $server;
    }

    function setAccountNumber($accountNumber) {
        $this->accountNumber = $accountNumber;
    }

    function setMeterNumber($meterNumber) {
        $this->meterNumber = $meterNumber;
    }
    
    function setPassword($password) {
        $this->password = $password;
    }
    
    function setKey($key) {
        $this->key = $key;
    }
    
 function setRateRequestTypes($type) {
        $this->rateRequestTypes = $type;
    }
    
    function setCarrierCode($carrierCode) {
        $this->carrierCode = $carrierCode;
    }
    
    function setDropoffType($dropoffType) {
        $this->dropoffType = $dropoffType;
    }

    function setService($service, $name) {
        $this->service = $service;
        $this->serviceName = $name;
    }

    function setPackaging($packaging) {
        $this->packaging = $packaging;
    }
    
    function setPackageLineItem($package) {
        $this->packageLineItems[] = $package;
    }
    
    function setPackageCount($count) {
        $this->packageCount = $count;
    }
    
    function setWeightUnits($units) {
        $this->weightUnits = $units;
    }

    function setOriginAddressLine($line) {
        $this->originAddressLines[] = $line;
    }
    
    function setOriginCity($code) {
        $this->originCity = $code;
    }
    
    function setOriginStateOrProvinceCode($code) {
        $this->originStateOrProvinceCode = $code;
    }
    
    function setOriginPostalCode($code) {
        $this->originPostalCode = $code;
    }
    
    function setOriginCountryCode($code) {
        $this->originCountryCode = $code;
    }
    
    function setDestAddressLine($line) {
        $this->destAddressLines[] = $line;
    }
    
    function setDestCity($code) {
        $this->destCity = $code;
    }
            
    function setDestStateOrProvinceCode($code) {
        $this->destStateOrProvinceCode = $code;
    }
    
    function setDestPostalCode($code) {
        $this->destPostalCode = $code;
    }
    
    function setDestCountryCode($code) {
        $this->destCountryCode = $code;
    }
    
    function setPayorType($type) {
        $this->payorType = $type;
    }
}

/**
 * Class for Rating  Packages
 * @author Daniele Rosario
 *
 */
class TiendaUpsRate extends TiendaUps 
{
	function __construct()
    {
        $this->wsdl = dirname( __FILE__ ).DS.'ups_rate.wsdl';        
    }
	
    var $rate           = null;
    var $payorType      = "SENDER";
    var $carrierCode    = "FDXG";
    var $dropoffType    = "REGULARPICKUP";
    var $service;
    var $serviceName;
    var $packaging      = "YOURPACKAGING";
    var $weightUnits    = "LBS";
    var $weight;
    var $rateRequestTypes = 'ACCOUNT'; // or LIST
    var $packageDetail  = 'INDIVIDUAL_PACKAGES'; // Or  PACKAGE_SUMMARY
    var $packageCount   = '1';
    var $packageLineItems = array();
    
    // Origin Address
    var $tin            = null; // Tax ID necessary when shipping internationally
    var $originAddressLines = array();
    var $originStateOrProvinceCode;
    var $originPostalCode;
    var $originCountryCode;
    
    // Destination Address
    var $destAddressLines = array();
    var $destStateOrProvinceCode;
    var $destPostalCode;
    var $destCountryCode;

    
   
    
    function getRate() 
    {
        try 
            {
                $this->response = $this->getClient()->ProcessRate( $this->getRequest() );
                
                if ($this->response->Response->ResponseStatus->Code != '0')
                {
                    $this->processResponse($this->response);
                    return true;
                }
                    else
                {
                    $this->setError( 'E1', JText::_('UPS_ERRORCODE1') );
                    return false;
                } 
                
                // $this->writeToLog($client);    // Write to log file   
            
            } catch (SoapFault $exception) {
                $this->response = array();
                $this->setError(  (string) $exception.$this->getClient()->__getLastRequest() );
                return false; 
            }        
    }
    
    /**
     * Creates the request array for sending to ups
     * @return array
     */
    function createRequest()
    {
		parent::createRequest();
        
        $request['Request']['RequestOption'] = 'Rate';
        
        $request['Shipment']['Service']['Code'] = $this->service;

        /* addresses */
        $request['Shipment']['Shipper'] = array(
            'Address' => 
                array (
                    'AddressLine' => $this->originAddressLines, // Origin details
                    'City' => $this->originCity,
                    'StateProvinceCode' => $this->originStateOrProvinceCode,
                    'PostalCode' => $this->originPostalCode,
                    'CountryCode' => $this->originCountryCode
                )
            );        
        $request['Shipment']['ShipTo'] = array(
            'Address' => 
                array(
                    'AddressLine' => $this->destAddressLines, // Destination details
                    'City' => $this->destCity,
                    'StateProvinceCode' => $this->destStateOrProvinceCode,
                    'PostalCode' => $this->destPostalCode,
                    'CountryCode' => $this->destCountryCode
                )
            );
            
        $request['Shipment']['Package'] = $this->packageLineItems;
        
        $this->request = $request;
    }

    /**
     * Processes the response from ups
     * @param $response
     * @return boolean 
     */
    protected function processResponse( $response )
    {    	
        if( property_exists( $response, 'RatedShipment' ) )
            $rate = $response->RatedShipment;
        else
            return false;
        
		$this->rate = $rate;
		$this->rate->summary = array();                        
        $this->rate->summary['name']    = $this->serviceName;
        $this->rate->summary['code']    = $this->service;
        $this->rate->summary['price']   = $rate->TransportationCharges->MonetaryValue;
        $this->rate->summary['extra']   = $rate->ServiceOptionsCharges->MonetaryValue;
        $this->rate->summary['total']   = $rate->TotalCharges->MonetaryValue;
        $this->rate->summary['tax']     = 0;
                        
        return true;
    }
}

/**
 * Class for Shipping UPS Packages
 * @author Daniele Rosario
 *
*/ 
class TiendaUpsShipment extends TiendaUps
{
	
	function __construct()
    {
        $this->wsdl = dirname( __FILE__ ).DS.'ups_ship.wsdl';        
    }
    
    function setShipperNumber($number)
    {
    	$this->shipperNumber = $number;
    }
 	function setOriginName($name)
    {
    	$this->originName = $name;
    }
	function setDestName($name)
    {
    	$this->destName = $name;
    }
    
	/**
     * Creates the request array for sending to ups
     * @return array
     */
    function createRequest()
    {
		parent::createRequest();
        
        $request['Request']['RequestOption'] = 'validate';
        
        $request['Shipment']['Service']['Code'] = $this->service;
        
        /* addresses */
        $request['Shipment']['Shipper'] = array(
            'Address' => 
                array (
                    'AddressLine' => $this->originAddressLines, // Origin details
                    'City' => $this->originCity,
                    'StateProvinceCode' => $this->originStateOrProvinceCode,
                    'PostalCode' => $this->originPostalCode,
                    'CountryCode' => $this->originCountryCode
                )
            );        
        $request['Shipment']['ShipTo'] = array(
            'Address' => 
                array(
                    'AddressLine' => $this->destAddressLines, // Destination details
                    'City' => $this->destCity,
                    'StateProvinceCode' => $this->destStateOrProvinceCode,
                    'PostalCode' => $this->destPostalCode,
                    'CountryCode' => $this->destCountryCode
                )
            );
            
        $request['Shipment']['Shipper']['Name'] = $this->originName;
        $request['Shipment']['Shipper']['ShipperNumber'] = $this->shipperNumber;
        $request['Shipment']['ShipTo']['Name'] = $this->destName;
            
        $request['Shipment']['PaymentInformation']['ShipmentCharge']['Type'] = '01';
        $request['Shipment']['PaymentInformation']['ShipmentCharge']['BillShipper']['AccountNumber'] = $this->shipperNumber;    
        
        $request['LabelSpecification']['LabelImageFormat']['Code'] = 'GIF';
        
        $request['Shipment']['Package'] = $this->packageLineItems;
        
        
        $this->request = $request;
    }
    
	function sendShipment($ordershipping_id) 
    {
    	
        try 
            {
                $this->response = $this->getClient()->ProcessShipment( $this->getRequest() );
                
                if ($this->response->Response->ResponseStatus->Code != '0')
                {                    
                	$results = $this->processResponse($this->response);
                	
                    // Save Tracking Numbers & Images
                    if($results)
                    {
                    	$row = JTable::getInstance('OrderShippings', 'TiendaTable');
                    	$row->load($ordershipping_id);
                    	
                    	// Structure: Shipment_id\nTracking_id_1\nTracking_id_2\n....
                    	$row->ordershipping_tracking_id = $row->ordershipping_tracking_id . $results['shipment_id'] . "\n";
                    	
                    	
                    	
                    	foreach($results['tracking_numbers'] as $t => $image)
                    	{
                    		$row->ordershipping_tracking_id = $row->ordershipping_tracking_id . $t . "\n";
                    		$row->save();
							
      
                  			JFile::write(Tienda::getPath('order_files').DS.$row->order_id.DS.$t.'.gif', $image);

                    	}
                    }
                    else
                    {
                    	$this->setError( JText::_('UPS_ERRORCODE2') );
                    	return false;
                    }
                    	
	                    	
                    return true;
                }
                    else
                {
                    $this->setError( 'E1', JText::_('UPS_ERRORCODE1') );
                    return false;
                } 
                
                // $this->writeToLog($client);    // Write to log file   
            
            } catch (SoapFault $exception) {
                $this->response = array();
                $this->setError(  (string) $exception.$this->getClient()->__getLastRequest() );
                return false; 
            }        
    }
    
	/**
     * Processes the response from UPS
     * @param $response
     * @return boolean 
     */
    protected function processResponse( $response )
    {
        if( property_exists( $response, 'ShipmentResults' ) )
        {
        	$results = array();

        	$shipment_id = $response->ShipmentResults->ShipmentIdentificationNumber;
        	$results['shipment_id'] = $shipment_id;
        	$results['tracking_numbers'] = array();
        	
        	if(!is_array($response->ShipmentResults->PackageResults))
        		$response->ShipmentResults->PackageResults = array($response->ShipmentResults->PackageResults);
        		
        	foreach($response->ShipmentResults->PackageResults as $res)
            {
            	$results['tracking_numbers'][$res->TrackingNumber] = base64_decode($res->ShippingLabel->GraphicImage);
            }
        
        }
        else
            return false;
        
            
        return $results;
    }
}


?> 

