<?php
/**
 * @package	Tienda
 * @author 	Dioscouri
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaShippingPlugin', 'library.plugins.shipping' );

class plgTiendaShipping_ups extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
	var $_element = 'shipping_ups';
	
	/**
	 * Overriding 
	 * 
	 * @param $options
	 * @return unknown_type
	 */
	function onGetShippingView( $row )
	{
		if ( !$this->_isMe( $row ) )
		{
			return null;
		}
		
		$html = $this->viewConfig( );
		
		return $html;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/library/plugins/TiendaShippingPlugin::onGetShippingRates()
	 */
	function onGetShippingRates( $element, $order )
	{
		// Check if this is the right plugin
		if ( !$this->_isMe( $element ) )
		{
			return null;
		}
		
		$address = $order->getShippingAddress( );
		$address = $this->checkAddress( $address );
		$orderItems = $order->getItems( );
		
		$rates = $this->sendRequest( $address, $orderItems );
		
		return $rates;
		
	}
	
	/**
	 * Display the "Ship it!" Button on the order page
	 * @param $order
	 */
	function onAfterDisplayOrderViewOrderHistory( $order )
	{
		foreach ( $order->ordershippings as $ship )
		{
			$ordershipping_id = $ship->ordershipping_id;
		}
		
		$row = JTable::getInstance( 'OrderShippings', 'TiendaTable' );
		$row->load( $ordershipping_id );
		
		if ( $row->ordershipping_tracking_id )
		{
			$path = Tienda::getPath( 'order_files' ) . DS . $order->order_id;
			
			$helper = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' );
			$labels = $helper->getServerFiles( $path );
			
			$plugin = $this->_getMe( );
			$plugin_id = $plugin->id;
			
			$vars = new JObject( );
			$vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
			$vars->id = $plugin_id;
			$vars->labels = $labels;
			$vars->order_id = $order->order_id;
			$html = $this->_getLayout( 'labels', $vars );
			
			// Tracking
			// Last one is empty. Skip!
			$tracking_numbers = explode( "\n", $row->ordershipping_tracking_id, -1 );
			// The first one is the shipment id
			if ( is_array( $tracking_numbers ) && count( $tracking_numbers ) ) $shipping_id = array_shift( $tracking_numbers );
			
			$vars = new JObject( );
			$vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
			$vars->id = $plugin_id;
			$vars->shipping_id = $shipping_id;
			$vars->tracking_numbers = $tracking_numbers;
			$vars->order_id = $order->order_id;
			$html .= $this->_getLayout( 'tracking', $vars );
		}
		else
		{
			$vars = new JObject( );
			$vars->state = $this->_getState( );
			$id = JRequest::getInt( 'id', '0' );
			$form = array( );
			$form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
			$vars->form = $form;
			
			$plugin = $this->_getMe( );
			$plugin_id = $plugin->id;
			
			$vars = new JObject( );
			$vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
			$vars->id = $plugin_id;
			$vars->order = $order;
			$html = $this->_getLayout( 'ship_it', $vars );
		}
		
		echo $html;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return unknown_type
	 */
	function tracking( )
	{
		$tracking_id = JRequest::getVar( 'tracking_id' );
		//$tracking_id = '3251026119'; <-- ?
		
		require_once ( dirname( __FILE__ ) . DS . 'shipping_ups' . DS . "ups.php" );
		
		$shipAccount = $this->params->get( 'account' );
		$key = $this->params->get( 'key' );
		$password = $this->params->get( 'password' );
		
		$ups = new TiendaUpsTracking;
		
		$ups->setKey( $key );
		$ups->setPassword( $password );
		$ups->setAccountNumber( $shipAccount );
		
		$ups->setTrack( $tracking_id );
		$results = $ups->track( );
		
		$vars = new JObject( );
		$vars->state = $this->_getState( );
		$id = JRequest::getInt( 'id', '0' );
		$form = array( );
		$form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
		$vars->form = $form;
		
		$plugin = $this->_getMe( );
		$plugin_id = $plugin->id;
		
		$vars = new JObject( );
		$vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
		$vars->id = $plugin_id;
		$vars->results = $results;
		$html = $this->_getLayout( 'do_tracking', $vars );
		
		echo $html;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return unknown_type
	 */
	function sendShipmentAjax( )
	{
		$model = JModel::getInstance( 'Orders', 'TiendaModel' );
		$model->setId( JRequest::getInt( 'order_id' ) );
		$order = $model->getItem( );
		
		if ( $this->sendShipment( $order ) )
		{
			$html = JText::_( 'Shipment Sent' ) . '<br />';
			$path = Tienda::getPath( 'order_files' ) . DS . $order->order_id;
			
			$helper = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' );
			$labels = $helper->getServerFiles( $path );
			
			$plugin = $this->_getMe( );
			$plugin_id = $plugin->id;
			
			$vars = new JObject( );
			$vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
			$vars->id = $plugin_id;
			$vars->labels = $labels;
			$vars->order_id = $order->order_id;
			$html .= $this->_getLayout( 'labels', $vars );
			return $html;
		}
		else
		{
			return JText::_( 'Shipment Failed!' ) . '<br />' . $this->getError( );
		}
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param $order
	 * @return unknown_type
	 */
	function sendShipment( $order )
	{
		require_once ( dirname( __FILE__ ) . DS . 'shipping_ups' . DS . "ups.php" );
		
		$shipAccount = $this->params->get( 'account' );
		$meter = $this->params->get( 'meter' );
		$billAccount = $this->params->get( 'account' );
		$key = $this->params->get( 'key' );
		$password = $this->params->get( 'password' );
		$shipperNumber = $this->params->get( 'shipper_number' );
		
		$packageCount = 0;
		$packages = array( );
		
		$orderItems = $order->orderitems;
		$orderinfo = $order->orderinfo;
		
		foreach ( $orderItems as $item )
		{
			$product = JTable::getInstance( 'Products', 'TiendaTable' );
			$product->load( $item->product_id );
			if ( $product->product_ships )
			{
				$packageCount = $packageCount + 1;
				$weight = array(
					'Weight' => round( $product->product_weight, 1 ), 'UnitOfMeasurement' => array(
						'Code' => $this->params->get( 'weight_unit', 'KGS' )
					) // get this from product?
				);
				
				$dimensions = array(
						'Length' => ( int ) $product->product_length, 'Width' => ( int ) $product->product_width,
						'Height' => ( int ) $product->product_height,
						'UnitOfMeasurement' => array(
							'Code' => $this->params->get( 'dimension_unit', 'CM' )
						) // get this from product?
				);
				
				$packages[] = array(
					'PackageWeight' => $weight, 'Dimensions' => $dimensions, 'Packaging' => array(
						'Code' => $this->params->get( 'packaging', '02' )
					)
				);
			}
		}
		
		$ups = new TiendaUpsShipment;
		
		$ups->setKey( $key );
		$ups->setPassword( $password );
		$ups->setAccountNumber( $billAccount );
		$ups->setShipperNumber( $shipperNumber );
		
		$ups->packageLineItems = $packages;
		$ups->setPackaging( $this->params->get( 'packaging', '02' ) );
		
		$ups->setOriginName( TiendaConfig::getInstance( )->get( 'shop_name' ) );
		$ups->setOriginAddressLine( $this->shopAddress->address_1 );
		$ups->setOriginAddressLine( $this->shopAddress->address_2 );
		$ups->setOriginCity( $this->shopAddress->city );
		$ups->setOriginStateOrProvinceCode( $this->shopAddress->zone_code );
		$ups->setOriginPostalCode( $this->shopAddress->zip );
		$ups->setOriginCountryCode( $this->shopAddress->country_isocode_2 );
		
		$ups->setDestName( $orderinfo->shipping_first_name . ' ' . $orderinfo->shipping_last_name );
		$ups->setDestAddressLine( $orderinfo->shipping_address_1 );
		$ups->setDestAddressLine( $orderinfo->shipping_address_2 );
		$ups->setDestCity( $orderinfo->shipping_city );
		$ups->setDestStateOrProvinceCode( $orderinfo->shipping_zone_code );
		$ups->setDestPostalCode( $orderinfo->shipping_postal_code );
		
		$country = JTable::getInstance( 'Countries', 'TiendaTable' );
		$country->load( $orderinfo->shipping_country_id );
		
		$ups->setDestCountryCode( $country->country_isocode_2 );
		
		$code = "";
		foreach ( $order->ordershippings as $ship )
		{
			$code = $ship->ordershipping_code;
			$ordershipping_id = $ship->ordershipping_id;
		}
		
		$ups->setService( $code, $code );
		
		if ( $ups->sendShipment( $ordershipping_id ) )
		{
			return true;
		}
		else
		{
			$this->setError( $ups->getError( ) . $ups->getClient( )->__getLastRequest( ) );
			return false;
		}
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return unknown_type
	 */
	function getUpsServices( )
	{
		$services = array(
				'01' => JText::_( 'UPS Next Day Air' ), '02' => JText::_( 'UPS Second Day Air' ), '03' => JText::_( 'UPS Ground' ),
				'07' => JText::_( 'UPS Worldwide Express' ), '08' => JText::_( 'UPS Worldwide Expedited' ), '11' => JText::_( 'UPS Standard' ),
				'12' => JText::_( 'UPS Three-Day Select' ), '13' => JText::_( 'UPS Next Day Air Saver' ),
				'14' => JText::_( 'UPS Next Day Air Early AM' ), '54' => JText::_( 'UPS Worldwide Express Plus' ),
				'59' => JText::_( 'UPS Second Day Air AM' ), '65' => JText::_( 'UPS Saver' ),
		);
		
		return $services;
	}
	
	/**
	 * Gets the list of enabled services
	 */
	function getServices( )
	{
		$upsServices = $this->getUpsServices( );
		$services = array( );
		$services_list = $this->params->get( 'services' );
		
		//check if array
		if ( !is_array( $services_list ) )
		{
			//convert to array
			//it happens when only 1 service is selected
			$services_list = array(
				$services_list
			);
		}
		
		foreach ( $services_list as $service )
		{
			if ( array_key_exists( $service, $upsServices ) )
			{
				$services[$service] = $upsServices[$service];
			}
		}
		return $services;
	}
	
	/**
	 * Displays the admin-side configuration form for the plugin
	 * 
	 */
	function viewConfig( )
	{
		JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR . DS . 'components' );
		TiendaToolBarHelper::cancel( 'close', 'Close' );
		
		$vars = new JObject( );
		$vars->state = $this->_getState( );
		$id = JRequest::getInt( 'id', '0' );
		$form = array( );
		$form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
		$vars->form = $form;
		
		$plugin = $this->_getMe( );
		$plugin_id = $plugin->id;
		
		$vars = new JObject( );
		$vars->link = "index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]={$plugin_id}";
		$vars->id = $plugin_id;
		$vars->list = $this->getUPSServices( );
		$vars->services = $this->getServices( );
		$html = $this->_getLayout( 'default', $vars );
		
		return $html;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param $address
	 * @param $orderItems
	 * @return unknown_type
	 */
	function sendRequest( $address, $orderItems )
	{
		$rates = array( );
		
		//return if empty address to remove error notice 
		//and avoid additional request to ups that will also return empty rates
		if ( empty( $address ) ) return $rates;
		
		require_once ( dirname( __FILE__ ) . DS . 'shipping_ups' . DS . "ups.php" );
		
		// Use params to determine which of these is enabled
		$services = $this->getServices( );
		
		$shipAccount = $this->params->get( 'account' );
		$meter = $this->params->get( 'meter' );
		$billAccount = $this->params->get( 'account' );
		$key = $this->params->get( 'key' );
		$password = $this->params->get( 'password' );
		
		$packageCount = 0;
		$packages = array( );
		
		foreach ( $orderItems as $item )
		{
			$product = JTable::getInstance( 'Products', 'TiendaTable' );
			$product->load( $item->product_id );
			if ( $product->product_ships )
			{
				$product_totalWeight = $product->product_weight * $item->orderitem_quantity;
				$packageCount = $packageCount + 1;
				
				$weight = array(
					'Weight' => round( $product_totalWeight, 1 ), 'UnitOfMeasurement' => array(
						'Code' => $this->params->get( 'weight_unit', 'KGS' )
					) // get this from product?
				);
				
				$dimensions = array(
						'Length' => round( $product->product_length, 2 ), 'Width' => round( $product->product_width, 2 ),
						'Height' => round( $product->product_height, 2 ),
						'UnitOfMeasurement' => array(
							'Code' => $this->params->get( 'dimension_unit', 'CM' )
						) // get this from product?
				);
				
				$package = array(
						'PackageWeight' => $weight, 'Dimensions' => $dimensions,
						'PackagingType' => array(
							'Code' => $this->params->get( 'packaging', '02' )
						),
				);
				
				if ( $this->params->get( 'declare_value', 1 ) )
				{
					$package['PackageServiceOptions']['DeclaredValue'] = array(
						'CurrencyCode' => $this->params->get( 'currency_code', 'USD' ), 'MonetaryValue' => $item->orderitem_final_price,
					);
				}
				
				$packages[] = $package;
			}
		}
		
		foreach ( $services as $service => $name )
		{
			$ups = new TiendaUpsRate;
			
			$ups->setKey( $key );
			$ups->setPassword( $password );
			$ups->setAccountNumber( $billAccount );
			
			$ups->packageLineItems = $packages;
			$ups->setPackageCount( $packageCount );
			$ups->setService( $service, $name );
			$ups->setPackaging( $this->params->get( 'packaging', '02' ) );
			
			$ups->setOriginAddressLine( $this->shopAddress->address_1 );
			$ups->setOriginAddressLine( $this->shopAddress->address_2 );
			$ups->setOriginCity( $this->shopAddress->city );
			$ups->setOriginStateOrProvinceCode( $this->shopAddress->zone_code );
			$ups->setOriginPostalCode( $this->shopAddress->zip );
			$ups->setOriginCountryCode( $this->shopAddress->country_isocode_2 );
			
			$ups->setDestAddressLine( $address->address_1 );
			$ups->setDestAddressLine( $address->address_2 );
			$ups->setDestCity( $address->city );
			$ups->setDestStateOrProvinceCode( $address->zone_code );
			$ups->setDestPostalCode( $address->postal_code );
			$ups->setDestCountryCode( $address->country_code );
			
			$ups->setPickupType( $this->params->get( 'pickup_type', '01' ) );
			$ups->setCustomerClassification( $this->params->get( 'customer_classification', '' ) );
			
			if ( $ups->getRate( ) )
			{
				$rate = $ups->rate;
				$rate->summary['element'] = $this->_element;
				$rates[] = $rate->summary;
			}
			else
			{
				$this->writeToLog( implode( "\n", $ups->getErrors( ) ) );
				
				// useful for debugging
				//$rate = array();
				//$rate['name']    = "Error";
				//$rate['code']    = "ERROR";
				//$rate['price']   = "999.99";
				//$rate['extra']   = "0.00";
				//$rate['total']   = "999.99";
				//$rate['tax']     = "0.00";
				//$rate['error'] = true;
				//$rate['errorMsg'] = $ups->getError();
				//$rate['debug'] = Tienda::dump( $ups );
				//$rate['debug_response'] = Tienda::dump( $ups->response );
				//
				//$rates[] = $rate;
			}
			
		}
		
		return $rates;
		
	}
	
	function downloadLabel( )
	{
		$order_id = JRequest::getInt( 'order_id' );
		$file = JRequest::getVar( 'label' );
		
		$path = Tienda::getPath( 'order_files' ) . DS . $order_id;
		
		$this->download( $file, $path );
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
		
		$ext = substr( $file, strlen( $file ) - 3 );
		
		// This will set the Content-Type to the appropriate setting for the file
		switch ( $ext )
		{
			case "pdf":
				$ctype = "application/pdf";
				break;
			case "exe":
				$ctype = "application/octet-stream";
				break;
			case "zip":
				$ctype = "application/zip";
				break;
			case "doc":
				$ctype = "application/msword";
				break;
			case "xls":
				$ctype = "application/vnd.ms-excel";
				break;
			case "ppt":
				$ctype = "application/vnd.ms-powerpoint";
				break;
			case "gif":
				$ctype = "image/gif";
				break;
			case "png":
				$ctype = "image/png";
				break;
			case "jpeg":
			case "jpg":
				$ctype = "image/jpg";
				break;
			case "mp3":
				$ctype = "audio/mpeg";
				break;
			case "wav":
				$ctype = "audio/x-wav";
				break;
			case "mpeg":
			case "mpg":
			case "mpe":
				$ctype = "video/mpeg";
				break;
			case "mov":
				$ctype = "video/quicktime";
				break;
			case "avi":
				$ctype = "video/x-msvideo";
				break;
			
			// The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
			case "php":
			case "htm":
			case "html":
				if ( $path ) die( "<b>Cannot be used for " . $ext . " files!</b>" );
			
			default:
				$ctype = "application/octet-stream";
		}
		
		// If requested file exists
		if ( JFile::exists( $path . DS . $file ) )
		{
			
			// Fix IE bugs
			if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && strstr( $_SERVER['HTTP_USER_AGENT'], 'MSIE' ) )
			{
				$header_file = preg_replace( '/\./', '%2e', $file, substr_count( $file, '.' ) - 1 );
				
				if ( ini_get( 'zlib.output_compression' ) )
				{
					ini_set( 'zlib.output_compression', 'Off' );
				}
			}
			else
			{
				$header_file = $file;
			}
			
			while ( @ob_end_clean( ) )
				;
			
			// Prepare headers
			header( "Pragma: public" );
			header( "Expires: 0" );
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
			header( "Cache-Control: public", false );
			
			header( "Content-Description: File Transfer" );
			header( "Content-Type: $ctype" );
			header( "Accept-Ranges: bytes" );
			header( "Content-Disposition: attachment; filename=\"" . $header_file . "\";" );
			header( "Content-Transfer-Encoding: binary" );
			header( "Content-Length: " . filesize( $path . DS . $file ) );
			
			// Output file by chunks
			error_reporting( 0 );
			if ( !ini_get( 'safe_mode' ) )
			{
				set_time_limit( 0 );
			}
			
			readfile( $path . DS . $file );
			
			$success = true;
			exit;
		}
		
		return $success;
	}
	
}
