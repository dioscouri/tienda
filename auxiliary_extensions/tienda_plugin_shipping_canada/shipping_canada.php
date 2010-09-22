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
            $description=strip_tags($product->product_description);
            if ($product->product_ships)
            {
               $canadaPost->addItem( $item->orderitem_quantity, $product->product_weight , $product->product_length, $product->product_width, $product->product_height, $description );
                
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

        $rates = $canadaPost->shipping_methods;
        
         $i = 0;
        foreach( $rates as $rate )
        {
        	$vars[$i]['element'] = $this->_element;
        	$vars[$i]['name'] = $rate['name'];
          	$vars[$i]['price'] = $rate['rate'];
	// TODO  
          	$vars[$i]['code'] = $rate['packingID'];
        	$vars[$i]['tax'] = 0;
        	$vars[$i]['extra'] = 0;
        	$vars[$i]['total'] =  $rate['rate'];
          	
           	$i++;
        }
      	return $vars;
       
        
    }
    
}
