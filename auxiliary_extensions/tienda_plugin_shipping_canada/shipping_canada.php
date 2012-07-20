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
    /*
     * it will call from the check out and send the request to calculate rates 
     */
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
    /*
     * this method will send the request to the Canada Shipping 
     */
  
    function sendRequest( $address, $orderItems )
    {
        $rates = array();
        
        require_once( dirname( __FILE__ ).'/shipping_canada/canadapost.php' );
         $key = $this->params->get('key');
         $canadaPost= new CanadaPost ($key) ;
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
	  // $address->city="Delhi";
	    JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		$zone = JTable::getInstance('Zones', 'TiendaTable');
		$zone->load($address->zone_id);
		$canadaPost->getQuote($address->city, $zone->zone_name, $address->country_name, $address->postal_code);

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
     
     /**
     * Displays the admin-side configuration form for the plugin
     * 
     */
    function viewConfig()
    {
        JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR.'/components' );
        // TODO Finish this
        //        TiendaToolBarHelper::custom( 'enabled.enable', 'publish', 'publish', JText::_('Enable'), true, 'shippingTask' );
        //        TiendaToolBarHelper::custom( 'enabled.disable', 'unpublish', 'unpublish', JText::_('Disable'), true, 'shippingTask' );
        TiendaToolBarHelper::cancel( 'close', 'Close' );
        
        $vars = new JObject();
        $vars->state = $this->_getState();
        $plugin = $this->_getMe(); 
        $plugin_id = $plugin->id;
        $vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }
    
    
}
