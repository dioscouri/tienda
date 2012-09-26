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
    
    var $_order = null;
	
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
	    
	    $this->_order = $order;
	    
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
    		$labels = $this->getStickersFiles($order->order_id);
    		
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
	        $vars->order_id = $order->order_id;
	        $vars->labels = $labels;
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
    	$stickers = $this->getStickersFiles($order_id);
    	
    	if(!count($stickers))
    	{
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
    	}
    	
    	$vars = new JObject();
        $vars->labels = $stickers;
        $vars->debug = $this->getError();
        $vars->order_id = $order_id;
        $html = $this->_getLayout('stickers', $vars);
        
        return $html;
    	
    }
    
	function fetchStickers($tracking_number, $path)
	{
		require_once( dirname( __FILE__ ).'/shipping_unex/unex.php' );

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
		
		$this->setError($unex->getError());
		
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
			$dir = Tienda::getPath( 'order_files' );
			
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
     * downloads a file
     * 
     * @return void
     */
    function downloadFile() 
    {
        $user =& JFactory::getUser();
        $order_id = intval( JRequest::getvar( 'id', '', 'request', 'int' ) );
        $filename = JRequest::getvar( 'filename', '', 'request', 'string' );
        $path = $this->getStickerPath($order_id, false);
        
        // log and download
        JLoader::import( 'com_tienda.library.file', JPATH_ADMINISTRATOR.'/components' );
        if ($downloadFile = $this->download( $filename, $path )) 
        {
           	echo '<h3>'.JText::_('Download Ok').'</h3>';  
        }
        echo '<h3>'.JText::_('Download Failed').'</h3>';
        return;
    }
    
/**
     * Downloads file
     * 
     * @param object Valid productfile object
     * @param mixed Boolean
     * @return array
     */
    function download( $file, $path ) 
    {
        $success = false;
        
        //$file->productfile_path = JPath::clean($file->productfile_path);
        
        $ext = substr($file, strlen($file) - 3);
        
        // This will set the Content-Type to the appropriate setting for the file
        switch( $ext ) {
             case "pdf": $ctype="application/pdf"; break;
             case "exe": $ctype="application/octet-stream"; break;
             case "zip": $ctype="application/zip"; break;
             case "doc": $ctype="application/msword"; break;
             case "xls": $ctype="application/vnd.ms-excel"; break;
             case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
             case "gif": $ctype="image/gif"; break;
             case "png": $ctype="image/png"; break;
             case "jpeg":
             case "jpg": $ctype="image/jpg"; break;
             case "mp3": $ctype="audio/mpeg"; break;
             case "wav": $ctype="audio/x-wav"; break;
             case "mpeg":
             case "mpg":
             case "mpe": $ctype="video/mpeg"; break;
             case "mov": $ctype="video/quicktime"; break;
             case "avi": $ctype="video/x-msvideo"; break;
        
             // The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
             case "php":
             case "htm":
             case "html": if ($path) die("<b>Cannot be used for ". $ext ." files!</b>");
        
             default: $ctype="application/octet-stream";
        }
        
        // If requested file exists
        if (JFile::exists($path.DS.$file)) {
        
            
            
            // Fix IE bugs
            if (isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
                $header_file = preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1);
                
                if (ini_get('zlib.output_compression'))  {
                    ini_set('zlib.output_compression', 'Off');
                }               
            }
            else {
                $header_file = $file;
            }
            
			while(@ob_end_clean());
            
            // Prepare headers
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public", false);
            
            header("Content-Description: File Transfer");
            header("Content-Type: $ctype" );
            header("Accept-Ranges: bytes");
            header("Content-Disposition: attachment; filename=\"" . $header_file . "\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . filesize($path.'/'.$file));
            
            // Output file by chunks
            error_reporting(0);
            if ( ! ini_get('safe_mode') ) {
                set_time_limit(0);
            }
            
            readfile($path.'/'.$file);
            
            $success = true;            
            exit;
        }
        
        return $success;        
    }
    
    /**
     * Reads the file by chunks
     * 
     * @param string $filename
     * @param int $chunksize
     * @param boolean $retbytes 
     * @access public
     * @return boolean|int Depending on the $retbytes param returns either the the bytes delivered or boolean status
     */ 
    function readfileChunked($filename, $chunksize = 1024, $retbytes = true)
    {
        $buffer = '';
        $cnt =0;
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            echo $buffer;
            @ob_flush();
            flush();
            if ($retbytes) {
                $cnt += strlen($buffer);
            }
        }
       $status = fclose($handle);
       if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }
        return $status;
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
			
            JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
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
 			return JText::_('COM_TIENDA_SHIPMENT_SENT');
 		}	
 		else
 		{
 			$html  = JText::_('COM_TIENDA_SHIPMENT_FAILED');
 			$html .= '<br />'.$this->getError(); 
 			return $html;
 		}
    }
    
    function sendShipment( $order )
    {        
        require_once( dirname( __FILE__ ).'/shipping_unex/unex.php' );

        $username = $this->params->get('username');
        $customerContext = $this->params->get('customer_context');
        $password = $this->params->get('password');
        $url = $this->params->get('url');
        $uri = $this->params->get('uri');
        $packaging = $this->params->get('packaging');
        
        $packageCount = 0;
        $packages = array();
        
        $orderItems = $order->orderitems;
        $orderinfo = $order->orderinfo;
        
        // one package per each orderitem
        if($packaging == 'per_item')
        {
        
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
        }
   	    // one package for the whole order
        else
        {
        		$packageCount = 1;
                $weight = array(
                    'Value' => $this->params->get('weight', '1'),
                    'Units' => $this->params->get('weight_unit', 'KG') // get this from product?
                );
                
                $dimensions = array(
                    'Length' => $this->params->get('length', '1'),
                    'Width' => $this->params->get('width', '1'),
                    'Height' => $this->params->get('height', '1'),
                    'Units' => $this->params->get('dimension_unit', 'CM') // get this from product?
                );
                
                $packages[] = array( 'Weight' => $weight, 'Dimensions' => $dimensions );
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
                        
		$unex->setOriginName(Tienda::getInstance()->get('shop_name', ''));
		$unex->setOriginSurname(Tienda::getInstance()->get('shop_company_name', ''));
		$unex->setOriginAttentionName(Tienda::getInstance()->get('shop_name', ''));
		$unex->setOriginPhone(Tienda::getInstance()->get('shop_phone', ''));
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
		
		$zone = JTable::getInstance('Zones', 'TiendaTable');
		$zone->load($orderinfo->shipping_zone_id);
		
		
		$unex->setDestStateOrProvinceCode($zone->code);
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
		
		$result = $unex->sendRequest($ordershipping_id);
		
		if(!$result)
		{
			$this->setError($unex->getError());
		}
            
        return $result;   
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
        JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR.'/components' );
        // TODO Finish this
        //        TiendaToolBarHelper::custom( 'enabled.enable', 'publish', 'publish', JText::_('Enable'), true, 'shippingTask' );
        //        TiendaToolBarHelper::custom( 'enabled.disable', 'unpublish', 'unpublish', JText::_('Disable'), true, 'shippingTask' );
        TiendaToolBarHelper::custom( 'edit', 'new', 'new', 'COM_TIENDA_NEW', false, 'shippingTask' );
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
        
        require_once( dirname( __FILE__ ).'/shipping_unex/unex.php' );

        // Use params to determine which of these is enabled
        $services = $this->getServices();

        $username = $this->params->get('username');
        $customerContext = $this->params->get('customer_context');
        $password = $this->params->get('password');
        $url = $this->params->get('url');
        $uri = $this->params->get('uri');
        $packaging = $this->params->get('packaging');
        
        $geozones = $this->_order->getBillingGeoZones();
        if($geozones)
        {
        	$geozone_id = $geozones[0]->geozone_id;
        }
        else
        {
        	$geozone_id = 0;
        }
        
        $packageCount = 0;
        $packages = array();
        
        // one package per each orderitem
        if($packaging == 'per_item')
        {
        
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
        }
        // one package for the whole order
        else
        {
        		$packageCount = 1;
                $weight = array(
                    'Value' => $this->params->get('weight', '1'),
                    'Units' => $this->params->get('weight_unit', 'KG') // get this from product?
                );
                
                $dimensions = array(
                    'Length' => $this->params->get('length', '1'),
                    'Width' => $this->params->get('width', '1'),
                    'Height' => $this->params->get('height', '1'),
                    'Units' => $this->params->get('dimension_unit', 'CM') // get this from product?
                );
                
                $packages[] = array( 'Weight' => $weight, 'Dimensions' => $dimensions );
        }
        
        
		$unex = new TiendaUnexPrice();
        $unex->setUsername($username);
        $unex->setPassword($password);
        $unex->setCustomerContext($customerContext);
        $unex->setUrl($url);
        $unex->setUri($uri);
        $unex->setGeozoneId($geozone_id);
        
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
        
        if($unex->rates)
        {
	        foreach( $unex->rates as &$rate )
	        {
	            $rate['element'] = $this->_element;
	        }
        }
        else
        {
        	return array();
        }
        
        
        return $unex->rates;
        
    }
    
 	private function checkInstallation()
        {
	        // if this has already been done, don't repeat
	        if (Tienda::getInstance()->get('checkTableUnexServices', '0'))
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
	            JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
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
