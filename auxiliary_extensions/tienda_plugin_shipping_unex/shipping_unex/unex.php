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


require_once( dirname( __FILE__ )."/helper.php" );

/**
 * Base class for Unex Transactions
 * @author Daniele Rosario
 * @author Rafael Diaz Tushman
 *
 */
class TiendaUnex extends JObject
{
    var $request    = array(); // the request sent to dhl is stored here
    var $response   = array(); // the response from dhl is stored here
    
    function getClient()
    {
        if (empty($this->client))
        {
            $this->client = new SoapClient(null, array("location" => $this->url, "uri" => $this->uri));
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
    
    function setUrl($server) {
        $this->url = $server;
    }
    
	function setUri($uri) {
        $this->uri = $uri;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function setCustomerContext($cc) {
        $this->customerContext = $cc;
    }
    
    function setPassword($password) {
        $this->password = $password;
    }
    
    
    function setContentType($contentType) {
        $this->contentType = $contentType;
    }
    
    function setShipmentType($shipmentType) {
        $this->shipmentType = $shipmentType;
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
    
	function setDimensionUnits($units) {
        $this->dimensionUnits = $units;
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
 * Class for Asking Prices of Unex Services
 * @author Daniele Rosario
 *
 */
class TiendaUnexPrice extends TiendaUnex 
{
	
	function setGeozoneId($geozone_id)
	{
		$this->geozone_id = $geozone_id;
	}
	
    function createRequest()
    {
    	
		$arrayXml['AccessRequest']['UserId'] = $this->username;
		$arrayXml['AccessRequest']['Password'] = $this->password;
		$arrayXml['Request']['CustomerContext'] = $this->customerContext;
		
		$arrayXml['Request']['RequestAction'] = 'Rate';
		
		$arrayXml['Shipment']['ShipTo']['City'] = $this->destCity;
		$arrayXml['Shipment']['ShipTo']['StateOrProvince'] = $this->destStateOrProvinceCode;
		$arrayXml['Shipment']['ShipTo']['PostalCode'] = $this->destPostalCode;
		$arrayXml['Shipment']['ShipTo']['CountryCode'] = $this->destCountryCode;
		
		$arrayXml['Shipment']['ShipFrom']['City'] = $this->originCity;
		$arrayXml['Shipment']['ShipFrom']['StateOrProvince'] = $this->originStateOrProvinceCode;
		$arrayXml['Shipment']['ShipFrom']['PostalCode'] = $this->originPostalCode;
		$arrayXml['Shipment']['ShipFrom']['CountryCode'] = $this->originCountryCode;
		
		$arrayXml['Shipment']['ConType'] = $this->contentType;
		$arrayXml['Shipment']['ShipType'] = $this->shipmentType;
		
		$i = 0;
		foreach($this->packageLineItems as $package)
		{
			$arrayXml['Shipment']['Package'][$i]['PackagingTypeCode'] = $this->packaging;
			$arrayXml['Shipment']['Package'][$i]['Dimensions']['UnitOfMeasurementCode'] = $package["Dimensions"]["Units"];
			$arrayXml['Shipment']['Package'][$i]['Dimensions']['Length'] = $package["Dimensions"]["Length"];
			$arrayXml['Shipment']['Package'][$i]['Dimensions']['Width'] = $package["Dimensions"]["Width"];
			$arrayXml['Shipment']['Package'][$i]['Dimensions']['Height'] = $package["Dimensions"]["Height"];
	
			$arrayXml['Shipment']['Package'][$i]['PackageWeight']['UnitOfMeasurementCode'] = $package["Weight"]["Units"];
			$arrayXml['Shipment']['Package'][$i]['PackageWeight']['Weight'] = $package["Weight"]["Value"];
			$i++;
		}
		
		
		$attributes = 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="C:\server\priceRequest.xsd"';
		
		// Stupid reference
		$null = null;
		$xml = trim(TiendaArrayToXML::toXml($arrayXml, 'PriceRequest', $null, $attributes));
		
		$this->request = $xml;
    }
    
	function getRates() 
    {
        try 
            {
                $this->response = @simplexml_load_string( $this->getClient()->ElaboraRequestXML( $this->getRequest() ) );
                
                if($this->response)
                {
	                if (@$xmlReader->Response->ResponseStatusCode != "0") 
	                {
	                    $this->processResponse($this->response);
	                    return true;
	                }
	                    else
	                {
	                    $this->setError( $this->response->Response->ResponseStatusCode, $this->response->Error->ErrorDescription );
	                    return false;
	                }
                } 
                
                // $this->writeToLog($client);    // Write to log file   
            
            } catch (SoapFault $exception) {
                $this->response = array();
                $this->setError( 'ERROR', (string) $exception );
                return false; 
            }        
    }
    
	/**
     * Processes the response from Unex
     * @param $response
     * @return boolean 
     */
    protected function processResponse( $response )
    {
        if( property_exists( $response, 'ShipmentResponse' ) )
            $response = $response->ShipmentResponse->RatedShipment;
        else
            return false;
        
        if(!is_array($response))
        {
            $temp = $response;
            $reply_details = array();
            $reply_details[] = $temp;
        }
        else
        {
        	$reply_details = $response;
        }
        
        $i = 0;
        foreach($reply_details as $details)
        {
            $serviceType = $details->Service->Code;            
            $rate_details = $details->EstimatedCharges;
            
            $model = DSCModel::getInstance('UnexServices', 'TiendaModel');
            $model->setState('filter_code', (string)$serviceType );                    	
            $service = $model->getList();
            $service = $service[0];
                    
            $rateName = JText::_($service->service_name);

            // Tax rate
            $tax_class_id = Tienda::getInstance()->get('shipping_tax_class', '1');
            $geozone_id = $this->geozone_id;
	        Tienda::load( 'TiendaQuery', 'library.query' );
	            
	        $taxrate = "0.00000";
	        $db = JFactory::getDBO();
	        
	        $query = new TiendaQuery();
	        $query->select( 'tbl.*' );
	        $query->from('#__tienda_taxrates AS tbl');
	        $query->where("tbl.tax_class_id = '".$tax_class_id."'");
	        $query->where("tbl.geozone_id = '".$geozone_id."'");
	        
	        $db->setQuery( (string) $query );
	        if ($data = $db->loadObject())
	        {
	            $taxrate = $data->tax_rate;
	        }
            
			$summary = array();                        
			$summary['name']    =  $rateName;
			$summary['code']    =  $serviceType;
			$summary['price']   = (double)$rate_details->BaseCharge;
			$summary['extra']   = (double)$rate_details->OptionsCharges + (double)$rate_details->SupplementsCharges;
			$summary['tax']     = (double)$rate_details->TotalCharges * ($taxrate / 100);
			$summary['total']   = (double)$rate_details->TotalCharges + $summary['tax'];
			$this->rates[] = $summary;
        }
        
        return true;
    }
}


/**
 * Class for Shipment Request
 * @author Daniele Rosario
 *
 */
class TiendaUnexShipment extends TiendaUnex 
{
	
	function setOriginAttentionName($name)
	{
		$this->originAttentionName = $name;
	}
	
	function setOriginSurname($name)
	{
		$this->originSurname = $name;
	}
	
	function setOriginName($name)
	{
		$this->originName = $name;
	}
	
	function setOriginPhone($name)
	{
		$this->originPhone = $name;
	}
	
	function setDestAttentionName($name)
	{
		$this->destAttentionName = $name;
	}
	
	function setDestSurname($name)
	{
		$this->destSurname = $name;
	}
	
	function setDestName($name)
	{
		$this->destName = $name;
	}
	
	function setDestPhone($name)
	{
		$this->destPhone = $name;
	}
	
	function setValue($value)
	{
		$this->value = $value;
	}
	
	function setOrderId($id)
	{
		$this->order_id = $id;
	}
	
	function setNote($note)
	{
		$this->note = $note;
	}
	
	function setService($service)
	{
		$this->service = $service;
	}
	
        
    /**
     * Creates the request array for sending to Unex
     * @return array
     */
    function createRequest()
    {
       	$arrayXml = array();
		
		$arrayXml['AccessRequest']['UserId'] = $this->username;
		$arrayXml['AccessRequest']['Password'] = $this->password;
		$arrayXml['Request']['CustomerContext'] = $this->customerContext;
		
		$arrayXml['Request']['RequestAction'] = '';
		
		$arrayXml['Shipment']['ShipType'] = $this->shipmentType;
		
		$arrayXml['Shipment']['ShipFrom']['Surname'] = $this->originSurname;
		$arrayXml['Shipment']['ShipFrom']['Name'] = $this->originName;
		
		$arrayXml['Shipment']['ShipFrom']['AttentionName'] = $this->originAttentionName;
			
		$arrayXml['Shipment']['ShipFrom']['PhoneNumber'] = $this->originPhone;
		
		$arrayXml['Shipment']['ShipFrom']['Address']['AddressLine1'] = @$this->originAddressLines[0];
		$arrayXml['Shipment']['ShipFrom']['Address']['AddressLine2'] = @$this->originAddressLines[1];
		$arrayXml['Shipment']['ShipFrom']['Address']['City'] = $this->originCity;
		$arrayXml['Shipment']['ShipFrom']['Address']['StateOrProvince'] = $this->originStateOrProvinceCode;
		$arrayXml['Shipment']['ShipFrom']['Address']['PostalCode'] = $this->originPostalCode;
		$arrayXml['Shipment']['ShipFrom']['Address']['CountryCode'] = $this->originCountryCode;
		
		
		$arrayXml['Shipment']['ShipTo']['Surname'] = $this->destSurname;
		$arrayXml['Shipment']['ShipTo']['Name'] = $this->destName;
		$arrayXml['Shipment']['ShipTo']['AttentionName'] = $this->destAttentionName;
			
		$arrayXml['Shipment']['ShipTo']['PhoneNumber'] = $this->destPhone;
		
		$arrayXml['Shipment']['ShipTo']['Address']['AddressLine1'] = @$this->destAddressLines[0];
		$arrayXml['Shipment']['ShipTo']['Address']['AddressLine2'] = @$this->destAddressLines[0];
		$arrayXml['Shipment']['ShipTo']['Address']['City'] = $this->destCity;
		$arrayXml['Shipment']['ShipTo']['Address']['StateOrProvince'] = $this->destStateOrProvinceCode;
		$arrayXml['Shipment']['ShipTo']['Address']['PostalCode'] = $this->destPostalCode;
		$arrayXml['Shipment']['ShipTo']['Address']['CountryCode'] = $this->destCountryCode;
		
		
		$arrayXml['Shipment']['Service']['Code'] = $this->service;
		$arrayXml['Shipment']['Service']['ServiceOptions']['Code'] = '01';
		
    	$i = 0;
		foreach($this->packageLineItems as $package)
		{
			$arrayXml['Shipment']['Package'][$i]['ConType'] = $this->contentType;
			if( $this->contentType == 'D' )
			{
				$arrayXml['Shipment']['Package'][$i]['PackagingTypeCode'] = '02';
			}
			else
			{
				$arrayXml['Shipment']['Package'][$i]['PackagingTypeCode'] = '01';
			}
			
			$arrayXml['Shipment']['Package'][$i]['Description'] = 'Shipping Package';
			
			$arrayXml['Shipment']['Package'][$i]['PackagingTypeCode'] = $this->packaging;
			$arrayXml['Shipment']['Package'][$i]['Dimensions']['UnitOfMeasurementCode'] = $package["Dimensions"]["Units"];
			$arrayXml['Shipment']['Package'][$i]['Dimensions']['Length'] = $package["Dimensions"]["Length"];
			$arrayXml['Shipment']['Package'][$i]['Dimensions']['Width'] = $package["Dimensions"]["Width"];
			$arrayXml['Shipment']['Package'][$i]['Dimensions']['Height'] = $package["Dimensions"]["Height"];
	
			$arrayXml['Shipment']['Package'][$i]['PackageWeight']['UnitOfMeasurementCode'] = $package["Weight"]["Units"];
			$arrayXml['Shipment']['Package'][$i]['PackageWeight']['Weight'] = $package["Weight"]["Value"];
			$i++;
		}
		
		
		$arrayXml['Shipment']['SpecialInstructions']['CustomsValue']['CurrencyCode'] = 'EUR';
		$arrayXml['Shipment']['SpecialInstructions']['CustomsValue']['MonetaryValue'] = $this->value;
		$arrayXml['Shipment']['SpecialInstructions']['BillShipper']['TypeBill'] = 'R';
		$arrayXml['Shipment']['SpecialInstructions']['BillShipper']['AccountNumber'] = '';
		$arrayXml['Shipment']['SpecialInstructions']['InsuredValue'] = '';
		$arrayXml['Shipment']['SpecialInstructions']['Note'] = $this->note;
		
		$arrayXml['Shipment']['SpecialInstructions']['VsReference'] = $this->order_id;
		
		$xml = trim(TiendaArrayToXML::toXml($arrayXml, 'ShipmentRequest'));
		
        $this->request = $xml;
    }
    
	function sendRequest($ordershipping_id) 
    {
        try 
            {
                $this->response = @simplexml_load_string( $this->getClient()->ElaboraRequestXML( $this->getRequest() ) );
                
                if($this->response)
                {
	                if (@$xmlReader->Response->ResponseStatusCode != "0") 
	                {
	                    $tracking_numbers = $this->processResponse($this->response);
	                    
	                    if($tracking_numbers)
	                    {
	                    	foreach($tracking_numbers as $t)
	                    	{
	                    		$row = DSCTable::getInstance('OrderShippings', 'TiendaTable');
	                    		$row->load($ordershipping_id);
	                    		
	                    		$row->ordershipping_tracking_id = $row->ordershipping_tracking_id . $t->TrackingNumber . "\n";
	                    		return $row->save(); 
	                    	}
	                    }
	                    else
	                    	return false;
	                }
	                    else
	                {
	                    $this->setError( $this->response->Response->ResponseStatusCode, $this->response->Error->ErrorDescription );
	                    return false;
	                }
                } 
                
                // $this->writeToLog($client);    // Write to log file   
            
            } catch (SoapFault $exception) {
                $this->response = array();
                $this->setError( 'ERROR', (string) $exception );
                return false; 
            }        
    }

    /**
     * Processes the response from Unex
     * @param $response
     * @return boolean 
     */
    protected function processResponse( $response )
    {
        if( property_exists( $response, 'ShipmentResults' ) )
        {
        	$tracking_numbers = array();
        	if(!is_array($response->ShipmentResults->PackageResults))
            	$tracking_numbers[] = $response->ShipmentResults->PackageResults;
            else
            {
            	foreach($response->ShipmentResults->PackageResults as $res)
            	{
            		$tracking_numbers[] = $res;
            	}
            }
        
        }
        else
            return false;
        
            
        return $tracking_numbers;
    }
}


/**
 * Class for Printing Unex Labels
 * @author Daniele Rosario
 *
 */
class TiendaUnexLabel extends TiendaUnex
{
	
	function setTrackingNumber($track)
	{
		$this->track = $track;	
	}
	
	function setPath($path)
	{
		$this->path = $path;	
	}
	
	function createRequest()
    {
    	$arrayXml['AccessRequest']['UserId'] = $this->username;
		$arrayXml['AccessRequest']['Password'] = $this->password;
		
		$arrayXml['TrackingNumber'] = $this->track;
				
		$xml = trim(TiendaArrayToXML::toXml($arrayXml, 'DocumentRequest'));
		
		$this->request = $xml;
    }
    
	function sendRequest() 
    {
        try 
            {
                $this->response = @simplexml_load_string( $this->getClient()->ElaboraRequestXML( $this->getRequest() ) );
                
                if($this->response)
                {
	                if (!@$this->response->Error) 
	                {
	                    $files = $this->processResponse($this->response);
	                    
	                    return $files;
	                }
	                    else
	                {
	                    $this->setError( $this->response->Error);
	                    return false;
	                }
                } 
                
                // $this->writeToLog($client);    // Write to log file   
            
            } catch (SoapFault $exception) {
                $this->response = array();
                $this->setError( 'ERROR', (string) $exception );
                return false; 
            }        
    }

    /**
     * Processes the response from Unex
     * @param $response
     * @return boolean 
     */
    protected function processResponse( $response )
    {    	
    	
    	$response = @$response->Response;

        if( property_exists( $response, 'Document' ) )
        {
			foreach($response->Document as $d)
			{
				$document = base64_decode( $d->DataDocument );
				
				$filename = strtolower( $d->Type.'.'.$d->FileType );
				JFile::write( $this->path.DS.$filename, $document );
				$files[] = $filename;
			}
			
			return $files;
        
        }
        else
            return false;

    }
}

?> 

