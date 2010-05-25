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

JLoader::import( 'com_tienda.library.plugins.shipping', JPATH_ADMINISTRATOR.DS.'components' );

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
        
        $rates = $this->sendRequest();
        
        $i = 0;
        foreach( $rates['rates'] as $rate )
        {
        	$vars[$i]['name'] = $rates['method']. " - ". $rate['name'];
        	$vars[$i]['price'] = $rate['price'];
        	$vars[$i]['tax'] = $rate['tax'];
        	$vars[$i]['extra'] = $rate['extra'];
        	$i++;
        }
        
		return $vars;
        
    }
    
    function viewConfig()
    {
    	$html = "";
        
        JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR.DS.'components' );
		TiendaToolBarHelper::custom( 'newMethod', 'new', 'new', JText::_('New'), false, 'shippingTask' );
		TiendaToolBarHelper::custom( 'delete', 'delete', 'delete', JText::_('Delete'), false, 'shippingTask' );
		
        $vars = new JObject();
       
		$form = array();
		$form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
		
		$vars->form = $form;
		
		
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }
    
    
	function sendRequest()
    {
    	@ini_set("soap.wsdl_cache_enabled", "0");
 
    	// Start the Soap Client
    	$wsdl = dirname( __FILE__ ).DS.'RateService_v8.wsdl';
		$client = new SoapClient($wsdl, array('trace' => 1));
		
		$request = $this->getRequestData();
		
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
		        echo Tienda::dump($response);
		        return false;
		    } 
		    
		    $this->writeToLog($client);    // Write to log file   
		
		} catch (SoapFault $exception) {
			echo Tienda::dump($exception);
		  	return false;        
		}
    }
    
    protected function getRequestData()
    {
    	$key = 'vIFK9F0wa5wp6qIk';
    	$password = 'ZfoGN25SKlHMb2foixabUXJg6';
    	
    	$shipAccount = '510087160';
    	$meter = '118513664';
    	
    	$billAccount = '510087160';
    	$dutyAccount = '510087160';
    	
    	$request['WebAuthenticationDetail'] = array('UserCredential' =>
                                      array('Key' => $key, 'Password' => $password)); 
		$request['ClientDetail'] = array('AccountNumber' => $shipAccount, 'MeterNumber' => $meter);
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request v8 using PHP ***');
		$request['Version'] = array('ServiceId' => 'crs', 'Major' => '8', 'Intermediate' => '0', 'Minor' => '0');
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		$request['RequestedShipment']['ShipTimestamp'] = date('c');
		$request['RequestedShipment']['ServiceType'] = 'PRIORITY_OVERNIGHT'; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
		$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
		$request['RequestedShipment']['Shipper'] = array('Address' => array(
		                                          'StreetLines' => array('10 Fed Ex Pkwy'), // Origin details
		                                          'City' => 'Memphis',
		                                          'StateOrProvinceCode' => 'TN',
		                                          'PostalCode' => '38115',
		                                          'CountryCode' => 'US'));
		$request['RequestedShipment']['Recipient'] = array('Address' => array (
		                                               'StreetLines' => array('13450 Farmcrest Ct'), // Destination details
		                                               'City' => 'Herndon',
		                                               'StateOrProvinceCode' => 'VA',
		                                               'PostalCode' => '20171',
		                                               'CountryCode' => 'US'));
		$request['RequestedShipment']['ShippingChargesPayment'] = array('PaymentType' => 'SENDER',
		                                                        'Payor' => array('AccountNumber' => $billAccount,
		                                                                     'CountryCode' => 'US'));
		$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
		$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
		$request['RequestedShipment']['PackageCount'] = '2';
		$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';  //  Or PACKAGE_SUMMARY
		$request['RequestedShipment']['RequestedPackageLineItems'] = array('0' => array('Weight' => array('Value' => 2.0,
		                                                                                    'Units' => 'LB'),
		                                                                                    'Dimensions' => array('Length' => 10,
		                                                                                        'Width' => 10,
		                                                                                        'Height' => 3,
		                                                                                        'Units' => 'IN')),
		                                                                   '1' => array('Weight' => array('Value' => 5.0,
		                                                                                    'Units' => 'LB'),
		                                                                                    'Dimensions' => array('Length' => 20,
		                                                                                        'Width' => 20,
		                                                                                        'Height' => 10,
		                                                                                        'Units' => 'IN')));
		return $request;
    }
    
    protected function processResponse( $response )
    {
    	$details = $response->RateReplyDetails;
    	$rates['method'] = $details->ServiceType;
    	
    	$rate_details = $details->RatedShipmentDetails;
    	
    	$i = 0;
    	foreach($rate_details as $rate )
    	{
    		$rate = $rate->ShipmentRateDetail;
    		$rates['rates'][$i]['name'] = $rate->RateType;
    		$rates['rates'][$i]['price'] = $rate->TotalBaseCharge->Amount;
    		$rates['rates'][$i]['extra'] = $rate->TotalSurcharges->Amount;
    		$rates['rates'][$i]['total'] = $rate->TotalNetFedExCharge->Amount;
    		$rates['rates'][$i]['tax'] = $rate->TotalTaxes->Amount;
    		$i++;
    	}
    	
    	return $rates;
    }
    
	protected function writeToLog($client)
	{  
		$file = '';
		JFile::write( $file,  sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $client->__getLastRequest(). "\n\n" . $client->__getLastResponse()) );
	}
    
}
