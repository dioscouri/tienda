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
 * Base class for DHL Transactions
 * @author Rafael Diaz-Tushman
 *
 */
class TiendaDhl extends JObject
{
    var $request    = array(); // the request sent to dhl is stored here
    var $response   = array(); // the response from dhl is stored here
    var $wsdl       = null;
    var $accountNumber;
    var $meterNumber;
    
    function __construct()
    {
        $this->wsdl = dirname( __FILE__ ).'/dhl.wsdl';        
    }
    
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
}

/**
 * Class for Shipping  Packages
 * @author Rafael Diaz-Tushman
 *
 */
class TiendaDhlShip extends TiendaDhl 
{
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
    
    function getRate() 
    {
        try 
            {
                $this->response = $this->getClient()->getRates( $this->getRequest() );
                
                if ($this->response->HighestSeverity != 'FAILURE' && $this->response->HighestSeverity != 'ERROR')
                {
                    $this->processResponse($this->response);
                    return true;
                }
                    else
                {
                    $this->setError( Tienda::dump($this->response) );
                    return false;
                } 
            
            } catch (SoapFault $exception) {
                $this->response = array();
                $this->setError( 'E2', (string) $exception );
                return false; 
            }        
    }
    
    /**
     * Creates the request array for sending to dhl
     * @return array
     */
    function createRequest()
    {
        /* Credentials */
        $request['WebAuthenticationDetail'] = array(
            'UserCredential' => array('Key' => $this->key, 'Password' => $this->password)
        ); 
        $request['ClientDetail'] = array('AccountNumber' => $this->accountNumber, 'MeterNumber' => $this->meterNumber);
        $request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request v8 using PHP ***');
        $request['Version'] = array('ServiceId' => 'crs', 'Major' => '8', 'Intermediate' => '0', 'Minor' => '0');
        $request['ReturnTransitAndCommit'] = false;
        $request['RequestedShipment']['ShipTimestamp'] = date('c');
        
        /* Configurable Values */
        $request['RequestedShipment']['DropoffType'] = $this->dropoffType; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
        $request['RequestedShipment']['ServiceType'] =  $this->service; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, DHL_GROUND, ...
        $request['RequestedShipment']['PackagingType'] = $this->packaging; // valid values DHL_BOX, DHL_PAK, DHL_TUBE, YOUR_PACKAGING, ...

        /* addresses */
        $request['RequestedShipment']['Shipper'] = array(
            'Address' => 
                array (
                    'StreetLines' => $this->originAddressLines, // Origin details
                    'City' => $this->originCity,
                    'StateOrProvinceCode' => $this->originStateOrProvinceCode,
                    'PostalCode' => $this->originPostalCode,
                    'CountryCode' => $this->originCountryCode
                )
            );        
        $request['RequestedShipment']['Recipient'] = array(
            'Address' => 
                array(
                    'StreetLines' => $this->destAddressLines, // Destination details
                    'City' => $this->destCity,
                    'StateOrProvinceCode' => $this->destStateOrProvinceCode,
                    'PostalCode' => $this->destPostalCode,
                    'CountryCode' => $this->destCountryCode
                )
            );
            
        $request['RequestedShipment']['ShippingChargesPayment'] = array(
            'PaymentType' => $this->payorType,
            'Payor' => 
                array('AccountNumber' => $this->accountNumber, 'CountryCode' => $this->originCountryCode )
            );
            
        $request['RequestedShipment']['RateRequestTypes'] = $this->rateRequestTypes; // 'ACCOUNT'; // or LIST
        $request['RequestedShipment']['PackageDetail'] = $this->packageDetail; // 'INDIVIDUAL_PACKAGES';  //  Or PACKAGE_SUMMARY
        
        $request['RequestedShipment']['PackageCount'] = $this->packageCount;
        
        $this->request = $request;
    }

    /**
     * Processes the response frm dhl
     * @param $response
     * @return boolean 
     */
    protected function processResponse( $response )
    {
        if( property_exists( $response, 'RateReplyDetails' ) )
            $reply_details = $response->RateReplyDetails;
        else
            return false;
        
        if(!is_array($reply_details))
        {
            $temp = $reply_details;
            $reply_details = array();
            $reply_details[] = $temp;
        }
        
        $i = 0;
        foreach($reply_details as $details)
        {
            $serviceType = $details->ServiceType;            
            $rate_details = $details->RatedShipmentDetails;
                    
            if (is_array($rate_details))
            {
                foreach($rate_details as $rate)
                {
                    $rate = $rate->ShipmentRateDetail;
                    if ( $rate->RateType == $details->ActualRateType )
                    {
                        $this->rate = $rate;
                        $this->rate->summary = array();                        
                        $this->rate->summary['name']    = $this->serviceName;
                        $this->rate->summary['code']    = $serviceType;
                        $this->rate->summary['price']   = $rate->TotalBaseCharge->Amount;
                        $this->rate->summary['extra']   = $rate->TotalSurcharges->Amount;
                        $this->rate->summary['total']   = $rate->TotalNetDhlCharge->Amount;
                        $this->rate->summary['tax']     = $rate->TotalTaxes->Amount;
                        
                        return true;
                    }
                }
            }
                elseif (is_object($rate_details))
            {
                $rate = $rate_details->ShipmentRateDetail;
                if ( $rate->RateType == $details->ActualRateType )
                {
                    $this->rate = $rate;
                    $this->rate->summary = array();
                    $this->rate->summary['name']    = $this->serviceName;
                    $this->rate->summary['code']    = $serviceType;
                    $this->rate->summary['price']   = $rate->TotalBaseCharge->Amount;
                    $this->rate->summary['extra']   = $rate->TotalSurcharges->Amount;
                    $this->rate->summary['total']   = $rate->TotalNetDhlCharge->Amount;
                    $this->rate->summary['tax']     = $rate->TotalTaxes->Amount;
                    
                    return true;
                }
            }
        }
        return false;
    }
}

/**
 * Class for Tracking DHL Pacakges
 * @author Rafael Diaz-Tushman
 *
 */
class TiendaDhlTrack extends TiendaDhl 
{
    
}

/**
 * Class for Printing Dhl Labels
 * @author Rafael Diaz-Tushman
 *
 */
class TiendaDhlPrint extends TiendaDhl 
{
    
}

?> 

