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

class plgTiendaShipping_Canada extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_canada'; 
	
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
    
  
    function sendRequest( $address, $orderItems )
    {
        $rates = array();
        
        require_once( dirname( __FILE__ ).DS.'shipping_canada'.DS."canadapost.php" );
 	    $canadaPost= new CanadaPost () ;
       
        foreach ( $orderItems as $item )
        {
        	$product = JTable::getInstance('Products', 'TiendaTable');
            $product->load($item->product_id);
            if ($product->product_ships)
            {
               $canadaPost->addItem( $item->orderitem_quantity, $product->product_weight , $product->product_length, $product->product_width, $product->product_height, $product->product_description );
                
            }            
        }
		//  $city, $provstate, $country, $postal_code
		// $address->city $address->zone_id $address->country_name $address->postal_code
		//
	   $address->city="Delhi";
	 $address->zone_id ="Delhi";
	  $address->country_name="India";
	   $address->postal_code ="11002";
        $canadaPost->getQuote($address->city, $address->zone_id, $address->country_name, $address->postal_code);
        
//        foreach ($services as $service=>$serviceName)
//        {
//            $fedex = new TiendaFedexShip;
//            
//            $fedex->setKey($key);
//            $fedex->setPassword($password);
//            $fedex->setAccountNumber($billAccount);
//            $fedex->setMeterNumber($meter);
//            $fedex->setService($service, $serviceName);
//            $fedex->setPayorType("SENDER");
//            $fedex->setCarrierCode("FDXE");
//            $fedex->setDropoffType("REGULAR_PICKUP");
//            $fedex->setPackaging("YOUR_PACKAGING");
//            
//            $fedex->packageLineItems = $packages;
//            $fedex->setPackageCount($packageCount);
//                        
//            $fedex->setOriginAddressLine($this->shopAddress->address_1);
//            $fedex->setOriginAddressLine($this->shopAddress->address_2);
//            $fedex->setOriginCity($this->shopAddress->city);
//            $fedex->setOriginStateOrProvinceCode($this->shopAddress->zone_code);
//            $fedex->setOriginPostalCode($this->shopAddress->zip);
//            $fedex->setOriginCountryCode($this->shopAddress->country_isocode_2);
//            
//            $fedex->setDestAddressLine($address->address_1);
//            $fedex->setDestAddressLine($address->address_2);
//            $fedex->setDestCity($address->city);
//            $fedex->setDestStateOrProvinceCode($address->zone_code);
//            $fedex->setDestPostalCode($address->postal_code);
//            $fedex->setDestCountryCode($address->country_code);
//                        
//            if ($fedex->getRate())
//            {
//                $fedex->rate->summary['element'] = $this->_element;
//                $rates[] = $fedex->rate->summary;
//            }
//        }
        var_dump($canadaPost);
        die();
        return $rates;
        
    }
    
}
