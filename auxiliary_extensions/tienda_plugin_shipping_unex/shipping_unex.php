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

class plgTiendaShipping_Unex extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_unex'; 
	
    function __construct(& $subject, $config)
    {
    	parent::__construct($subject, $config);
    	$this->checkInstallation();
    }
    
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
    
    /**
     * Display the "Ship it!" Button on the order page
     * @param $order
     */
    function onAfterDisplayOrderViewOrderHistory( $order )
    {
    	if( !$this->wasShipped($order) )
    	{
    		if( $this->isToShip($order) )
    		{    		
		    	$vars = new JObject();
		        $vars->state = $this->_getState();
		        $id = JRequest::getInt('id', '0');
		        $form = array();
		        $form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
		        $vars->form = $form;
		        
		        $plugin = $this->_getMe(); 
		        $plugin_id = $plugin->id;
		        
		        $vars = new JObject();
		        $vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
		        $vars->id = $plugin_id;
		        $vars->order = $order;
		        $html = $this->_getLayout('ship_it', $vars);
    		}
    		else
    		{
    			$html = "";
    		}
    	}
    	else
    	{
    		$vars = new JObject();
	        $vars->state = $this->_getState();
	        $id = JRequest::getInt('id', '0');
	        $form = array();
	        $form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
	        $vars->form = $form;
	        
	        $plugin = $this->_getMe(); 
	        $plugin_id = $plugin->id;
	        
	        $labels = $this->getStickersFiles($order->order_id);
	        
	        $vars = new JObject();
	        $vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
	        $vars->id = $plugin_id;
	        $vars->order = $order;
	        $html = $this->_getLayout('labels', $vars);
    	}
        
        echo $html;
        
    }
    
    private function wasShipped($order)
    {
    	if($order->ordershippings)
    	{
    		foreach($order->ordershippings as $ship)
    		{
    			if($ship->ordershipping_tracking_id != "")
    			{
    				return true;
    			}
    		}
    	}
    	
    	return false;
    }
    
 	private function isToShip($order)
    {
    	if($order->ordershippings)
    	{
    		foreach($order->ordershippings as $ship)
    		{
    			if($ship->ordershipping_type == $this->_element)
    			{
    				return true;
    			}
    		}
    	}
    	
    	return false;
    }
    
    function fetchStickersAjax()
    {
    	$order_id = JRequest::getInt('order_id');
    	$model = JModel::getInstance('Orders', 'TiendaModel');
    	$model->setId($order_id);
    	
    	$order = $model->getItem();
    	$stickers = array();
    	
    	foreach(@$order->ordershippings as $ship)
    	{
    		if($ship->ordershipping_tracking_id)
    		{
    			$tracking_numbers = explode("\n", $ship->ordershipping_tracking_id);
    			foreach($tracking_numbers as $number)
    			{
    				if($number)
    				{
		    			$fetched = $this->fetchStickers($number, $this->getStickerPath($order_id, true) );
		    			if(is_array($fetched))
		    				$stickers = array_merge( $stickers, $fetched );
    				}
    			}
    		}
    	}
    	
    	$vars = new JObject();
        $vars->labels = $stickers;
        $vars->debug = $fetched;
        $html = $this->_getLayout('stickers', $vars);
        
        return $html;
    	
    }
    
	function fetchStickers($tracking_number, $path)
	{
		require_once( dirname( __FILE__ ).DS.'shipping_unex'.DS."unex.php" );

        $username = $this->params->get('username');
        $customerContext = $this->params->get('customer_context');
        $password = $this->params->get('password');
        $url = $this->params->get('url');
        $uri = $this->params->get('uri');
        
        $unex = new TiendaUnexLabel();
        $unex->setUsername($username);
        $unex->setPassword($password);
        $unex->setCustomerContext($customerContext);
        $unex->setUrl($url);
        $unex->setUri($uri);
        
        $unex->setTrackingNumber($tracking_number);
		$unex->setPath($path);       

		$files = $unex->sendRequest();
		
		return $files;
		
	}
	
	/**
	 * Returns array of filenames
	 * Array
     * (
     *     [0] => airmac.png
     *     [1] => airportdisk.png
     *     [2] => applepowerbook.png
     *     [3] => cdr.png
     *     [4] => cdrw.png
     *     [5] => cinemadisplay.png
     *     [6] => floppy.png
     *     [7] => macmini.png
     *     [8] => shirt1.jpg
     * )
	 * @param $folder
	 * @return array
	 */
	
	protected function getStickersFiles($id)
	{
		$images = array();
		
		$folder = $this->getStickerPath($id, true);
		
        if (JFolder::exists( $folder ))
        {
        	$extensions = array( 'png', 'gif', 'jpg', 'jpeg', 'pdf' );
        	
        	$files = JFolder::files( $folder );
        	foreach ($files as $file)
        	{
	            $namebits = explode('.', $file);
	            $extension = $namebits[count($namebits)-1];
	            if (in_array($extension, $extensions))
	            {
                        $images[] = $file;
	            }
        	}
        }
        
       	return $images;
	}
	
	/**
	 * Returns the full path to the order sticker file
	 * 
	 * @param int $id
	 * @return string
	 */
	function getStickerPath( $id, $check )
	{
		static $paths;
		
		$id = (int) $id;
		
		if (!is_array($paths)) { $paths = array(); }
		
		if (empty($paths[$id]))
		{
			// Check where we should upload the file
			// This is the default one
			$dir = Tienda::getPath( 'orders_files' );
			
			$helper = TiendaHelperBase::getInstance();
			
		
			// try with the product id
			if($helper->checkDirectory($dir.DS.$id, $check))
			{
				$dir = $dir.DS.$id.DS;
			}
			
			return $dir;
		}
		
		return $paths[$id];
	}
	
    /**
	 * Returns the full path to the order sticker file path
	 * 
	 * @param int $id
	 * @return string
	 
	function getStickerUrl( $id )
	{
		static $urls;
		
		$id = (int) $id;
		
		if (!is_array($urls)) { $urls = array(); }
		
		if (empty($urls[$id]))
		{
			$urls[$id] = '';
			
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $row = JTable::getInstance('Products', 'TiendaTable');
            $row->load( (int) $id );
			if (empty($row->product_id))
			{
				// TODO figure out what to do if the id is invalid 
				return null;
			}

			$urls[$id] = $row->getImageUrl();
		}
		
		return $urls[$id];
	}
    */
    
    
    function sendShipmentAjax()
    {
    	$model = JModel::getInstance('Orders', 'TiendaModel');
    	$model->setId( JRequest::getInt('order_id') );
    	$order = $model->getItem();
    	
 		if($this->sendShipment($order))
 		{
 			return JText::_('Shipment Sent');
 		}	
 		else
 		{
 			return JText::_('Shipment Failed!');
 		}
    }
    
    function sendShipment( $order )
    {        
        require_once( dirname( __FILE__ ).DS.'shipping_unex'.DS."unex.php" );

        $username = $this->params->get('username');
        $customerContext = $this->params->get('customer_context');
        $password = $this->params->get('password');
        $url = $this->params->get('url');
        $uri = $this->params->get('uri');
        
        $packageCount = 0;
        $packages = array();
        
        $orderItems = $order->orderitems;
        $orderinfo = $order->orderinfo;
        
        foreach ( $orderItems as $item )
        {
            $product = JTable::getInstance('Products', 'TiendaTable');
            $product->load($item->product_id);
            if ($product->product_ships)
            {
                $packageCount = $packageCount + 1;
                $weight = array(
                    'Value' => $product->product_weight,
                    'Units' => $this->params->get('weight_unit', 'KG') // get this from product?
                );
                
                $dimensions = array(
                    'Length' => $product->product_length,
                    'Width' => $product->product_width,
                    'Height' => $product->product_height,
                    'Units' => $this->params->get('dimension_unit', 'CM') // get this from product?
                );
                
                $packages[] = array( 'Weight' => $weight, 'Dimensions' => $dimensions );
            }            
        }
        
		$unex = new TiendaUnexShipment();
        $unex->setUsername($username);
        $unex->setPassword($password);
        $unex->setCustomerContext($customerContext);
        $unex->setUrl($url);
        $unex->setUri($uri);
        
        $unex->setPackaging("01");
       	$unex->packageLineItems = $packages;
        $unex->setPackageCount($packageCount);
                        
		$unex->setOriginName(TiendaConfig::getInstance()->get('shop_name', ''));
		$unex->setOriginSurname(TiendaConfig::getInstance()->get('shop_company_name', ''));
		$unex->setOriginAttentionName(TiendaConfig::getInstance()->get('shop_name', ''));
		$unex->setOriginPhone(TiendaConfig::getInstance()->get('shop_phone', ''));
        $unex->setOriginAddressLine($this->shopAddress->address_1);
		$unex->setOriginAddressLine($this->shopAddress->address_2);
		$unex->setOriginCity($this->shopAddress->city);
		$unex->setOriginStateOrProvinceCode($this->shopAddress->zone_code);
		$unex->setOriginPostalCode($this->shopAddress->zip);
		$unex->setOriginCountryCode($this->shopAddress->country_isocode_2);
		            
		$unex->setDestName($orderinfo->shipping_first_name. ' '. $orderinfo->shipping_middle_name);
		$unex->setDestSurname($orderinfo->shipping_last_name);
		$unex->setDestPhone($orderinfo->shipping_phone_1);
		
		if($orderinfo->shipping_company != '')
			$unex->setDestAttentionName($orderinfo->shipping_company);
		else
			$unex->setDestAttentionName($orderinfo->shipping_last_name . ' ' . $orderinfo->shipping_middle_name . ' ' . $orderinfo->shipping_first_name );
			
		$unex->setDestAddressLine($orderinfo->shipping_address_1);
		$unex->setDestAddressLine($orderinfo->shipping_address_2);
		$unex->setDestCity($orderinfo->shipping_city);
		$unex->setDestStateOrProvinceCode($orderinfo->shipping_zone_code);
		$unex->setDestPostalCode($orderinfo->shipping_postal_code);
		
		$country = JTable::getInstance('Countries', 'TiendaTable');
		$country->load($orderinfo->shipping_country_id);
		
		$unex->setDestCountryCode($country->country_isocode_2);
		
		$unex->setContentType("N");
		$unex->setShipmentType("E");
		
		$unex->setOrderId($order->order_id);
		$unex->setNote($order->customer_note);
		$unex->setValue($order->order_subtotal + $order->order_tax);
		
		$code = "";
		foreach($order->ordershippings as $ship)
		{
			$code = $ship->ordershipping_code;
			$ordershipping_id = $ship->ordershipping_id;
		}
		
		$unex->setService($code);
		
		$unex->createRequest();
            
        return $unex->sendRequest($ordershipping_id);   
    }
    
    function getServices()
    {
        $this->includeCustomModel('UnexServices');
        $this->includeCustomTables();
        
        $model = JModel::getInstance('UnexServices', 'TiendaModel');
        $services = $model->getList();
        
        return $services;
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
        TiendaToolBarHelper::custom( 'edit', 'new', 'new', JText::_('New'), false, 'shippingTask' );
        TiendaToolBarHelper::cancel( 'close', 'Close' );
        
        $vars = new JObject();
        $vars->state = $this->_getState();
        $id = JRequest::getInt('id', '0');
        $form = array();
        $form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
        $vars->form = $form;
        
        $plugin = $this->_getMe(); 
        $plugin_id = $plugin->id;
        
        $vars = new JObject();
        $vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
        $vars->id = $plugin_id;
        $vars->list = $this->getServices();
        $html = $this->_getLayout('default', $vars);
		
        return $html;
    }
    
    function sendRequest( $address, $orderItems )
    {
        $rates = array();
        
        $this->includeCustomModel('UnexServices');
        $this->includeCustomTables();
        
        require_once( dirname( __FILE__ ).DS.'shipping_unex'.DS."unex.php" );

        // Use params to determine which of these is enabled
        $services = $this->getServices();

        $username = $this->params->get('username');
        $customerContext = $this->params->get('customer_context');
        $password = $this->params->get('password');
        $url = $this->params->get('url');
        $uri = $this->params->get('uri');
        
        $packageCount = 0;
        $packages = array();
        
        foreach ( $orderItems as $item )
        {
            $product = JTable::getInstance('Products', 'TiendaTable');
            $product->load($item->product_id);
            if ($product->product_ships)
            {
                $packageCount = $packageCount + 1;
                $weight = array(
                    'Value' => $product->product_weight,
                    'Units' => $this->params->get('weight_unit', 'KG') // get this from product?
                );
                
                $dimensions = array(
                    'Length' => $product->product_length,
                    'Width' => $product->product_width,
                    'Height' => $product->product_height,
                    'Units' => $this->params->get('dimension_unit', 'CM') // get this from product?
                );
                
                $packages[] = array( 'Weight' => $weight, 'Dimensions' => $dimensions );
            }            
        }
        
        
		$unex = new TiendaUnexPrice();
        $unex->setUsername($username);
        $unex->setPassword($password);
        $unex->setCustomerContext($customerContext);
        $unex->setUrl($url);
        $unex->setUri($uri);
        
        $unex->setPackaging("01");
       	$unex->packageLineItems = $packages;
        $unex->setPackageCount($packageCount);
                        
		$unex->setOriginAddressLine($this->shopAddress->address_1);
		$unex->setOriginAddressLine($this->shopAddress->address_2);
		$unex->setOriginCity($this->shopAddress->city);
		$unex->setOriginStateOrProvinceCode($this->shopAddress->zone_code);
		$unex->setOriginPostalCode($this->shopAddress->zip);
		$unex->setOriginCountryCode($this->shopAddress->country_isocode_2);
		            
		$unex->setDestAddressLine($address->address_1);
		$unex->setDestAddressLine($address->address_2);
		$unex->setDestCity($address->city);
		$unex->setDestStateOrProvinceCode($address->zone_code);
		$unex->setDestPostalCode($address->postal_code);
		$unex->setDestCountryCode($address->country_code);
		
		$unex->setContentType("N");
		$unex->setShipmentType("E");
            
        $unex->getRates();
        foreach( $unex->rates as &$rate )
        {
            $rate['element'] = $this->_element;
        }
        
        
        return $unex->rates;
        
    }
    
 	private function checkInstallation()
        {
	        // if this has already been done, don't repeat
	        if (TiendaConfig::getInstance()->get('checkTableUnexServices', '0'))
	        {
	            return true;
	        }
	        
	        $sql = "CREATE TABLE IF NOT EXISTS `#__tienda_unexservices` (
					  `service_id` int(11) NOT NULL AUTO_INCREMENT,
					  `service_name` varchar(255) DEFAULT NULL,
					  `ordering` int(11) NOT NULL,
					  `service_code` varchar(255) NOT NULL,
					  PRIMARY KEY (`service_id`),
					  KEY `idx_service_name` (`service_name`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=52";
					
					
	        
	        $db = JFactory::getDBO();
	        $db->setQuery($sql);
	        $result_1 = $db->query();
	        
	        $sql = "INSERT IGNORE INTO `#__tienda_unexservices` (`service_id`, `service_name`, `ordering`, `service_code`) VALUES
					(1, 'UNEX DEFERRED ', 0, '05'),
					(2, 'UNEX ECONOMY FREIGHT ', 0, '07'),
					(3, 'UNEX EUROPA PRIORITY ', 0, '09'),
					(4, 'UNEX NAZIONALE ESPRESSO ', 0, '11'),
					(5, 'DHL - NAZIONALE ESPRESSO ', 0, '28'),
					(6, 'DHL EUROPLUS NAZIONALE ', 0, '34'),
					(7, 'DHL EUROPACK ', 0, '30'),
					(8, 'UPS EXPRESS PLUS ', 0, '24'),
					(9, 'DHL EUROPLUS ', 0, '31'),
					(10, 'DHL ECX - European Express Service ', 0, '29'),
					(11, 'DHL WPX - Worldwide Express Service ', 0, '38'),
					(12, 'UNEXLOG WORLD EXPRESS ', 0, '21'),
					(13, 'UNEX WORLD PRIORITY ', 0, '20'),
					(14, 'TNT IMPORT ECONOMY EXPRESS ', 0, '45'),
					(15, 'TNT EXPRESS EUROPA - PLT ', 0, '44'),
					(16, 'TNT EXPRESS EUROPA ', 0, '43'),
					(17, 'TNT EXPRESS - RESTO DEL MONDO ', 0, '42'),
					(18, 'TNT EXPRESS - DOCS ', 0, '41'),
					(19, 'TNT ECONOMY INTERCONTINENTAL ', 0, '40'),
					(20, 'TNT ECONOMY EXPRESS ', 0, '39'),
					(21, 'UPS EXPRESS SAVER ', 0, '25'),
					(22, 'UPS EXPRESS ITALIA ', 0, '23'),
					(23, 'CESPED-ECONOMY FREIGHT ROAD ', 0, '53'),
					(24, 'GLS Internazionale Parcel ', 0, '52'),
					(25, 'GLS Executive Nazionale Express ', 0, '51'),
					(26, 'SDA STANDARD ', 0, '50'),
					(27, 'SDA GOLDEN SERVICE ', 0, '49'),
					(28, 'SDA EXTRA LARGE ', 0, '48'),
					(29, 'SDA CAPI APPESI SMALL ', 0, '47'),
					(30, 'SDA CAPI APPESI LARGE ', 0, '46'),
					(31, 'UPS EXPEDITED ', 0, '22'),
					(32, 'DHL ITALIA EXPRESS ', 0, '37'),
					(33, 'DHL IMPORT EXPRESS WORLDWIDE - Extra UE ', 0, '36'),
					(34, 'DHL IMPORT EXPRESS WORLDWIDE - Express UE ', 0, '35'),
					(35, 'DHL EUROPLUS IMPORT PARCEL ', 0, '33'),
					(36, 'DHL EUROPLUS IMPORT FREIGHT ', 0, '32'),
					(37, 'DHL - ECX - EXPRESS 09:00 ', 0, '26'),
					(38, 'DHL - ECX - EXPRESS 12:00 ', 0, '27'),
					(39, 'UNEX WORLD EXPRESS ', 0, '19'),
					(40, 'UNEX WORLD ECONOMY ', 0, '18'),
					(41, 'UNEX VICENZA EXPRESS ', 0, '17'),
					(42, 'UNEX NAZIONALE PRIORITY AM ', 0, '16'),
					(43, 'UNEX NAZIONALE PRIORITY ', 0, '15'),
					(44, 'UNEX NAZIONALE EXPRESS - UPS ', 0, '14'),
					(45, 'UNEX NAZIONALE EXPRESS - SDA ', 0, '13'),
					(46, 'UNEX NAZIONALE EXPRESS ', 0, '12'),
					(47, 'UNEX NAZIONALE ECONOMY ', 0, '10'),
					(48, 'UNEX EUROPA EXPRESS ', 0, '08'),
					(49, 'UNEX ECONOMY EXPRESS ', 0, '06'),
					(50, 'FDX INTERNATIONAL PRIORITY ', 0, '03'),
					(51, 'TNT ECONOMY EXPRESS', 0, '04');
					";
	        
			$db->setQuery($sql);
	        $result_2 = $db->query();

	        if ($result_1 && $result_2)
	        {
	            // Update config to say this has been done already
	            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
	            $config = JTable::getInstance( 'Config', 'TiendaTable' );
	            $config->load( array( 'config_name'=>'checkTableUnexServices') );
	            $config->config_name = 'checkTableUnexServices';
	            $config->value = '1';
	            $config->save();
	            return true;
	        }
	
	        return false;        
        }
    
	protected function writeToLog($client)
	{  
		$file = '';
		JFile::write( $file,  sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $client->__getLastRequest(). "\n\n" . $client->__getLastResponse()) );
	}
    
}
