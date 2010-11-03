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

class plgTiendaShipping_eDeliver extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_edeliver'; 
	
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
     * this method will send the request to the eDeliver page
     */
  
    function sendRequest( $address, $orderItems )
    {
        $rates = array();
        
        require_once( dirname( __FILE__ ).DS.'shipping_edeliver'.DS."edeliver.php" );
        
        $service_types = $this->params->get('service_type');
        
        $edeliver= new EDeliver () ;
        
        $weight = 0; 
        $total_volume = 0;
        $quantity = 0;
        foreach ( $orderItems as $item )
        {
        	$product = JTable::getInstance('Products', 'TiendaTable');
            $product->load($item->product_id);
            if($product->product_ships)
            {
	            $weight += $product->product_weight;
	            $volume = $product->product_width * $product->product_length * $product->product_height;
	            $total_volume += $volume;
	            $quantity += 1;
            }
        }
        
        // Cube Root the total volume to get a hypothetical dimension for the package 
        $dim = pow( $total_volume, 1/3 );
        
        $edeliver->setWeight($weight);
        $edeliver->setHeight($dim);
        $edeliver->setWidth($dim);
        $edeliver->setLength($dim);
        $edeliver->setQuantity($quantity);
	
	    JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$zone = JTable::getInstance('Zones', 'TiendaTable');
		$zone->load($address->zone_id);
		
		$edeliver->setDestCountryCode($address->country_isocode_2);
		$edeliver->setDestPostalCode($address->postal_code);
		$edeliver->setOriginPostalCode($this->shopAddress->zip);
		
		$vars = array();
		$i = 0;
		foreach($service_types as $service_type)
		{
			$edeliver->setServiceType($service_type);
	       	$rate = $edeliver->sendRequest();
	       	
	       	if(strpos($rate['err_msg'] , 'OK') !== false)
	       	{
		       	$vars[$i]['element'] = $this->_element;
		        $vars[$i]['name'] = $this->params->get('rate_name'). ' - '. $rate['service_type'];
		
		       	foreach( $rate as $key => $value )
		        {
		        	switch($key)
		        	{
		        		case 'charge':  $vars[$i]['price'] = $value;
		        						$vars[$i]['total'] =  $value;
		        						break;
		        	}
		        }
		        
		        $i++;
	       	}
	       
		}
		        
        return $vars;
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
        $plugin = $this->_getMe(); 
        $plugin_id = $plugin->id;
        $vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }
    
    
}
