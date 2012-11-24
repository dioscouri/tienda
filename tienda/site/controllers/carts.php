<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaControllerCarts extends TiendaController
{

	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();

        $this->set('suffix', 'carts');
        
        $cart_helper = TiendaHelperBase::getInstance( 'Carts' );
		$items = $cart_helper->getProductsInfo();
		
		// create the order object
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
		$this->_order = JTable::getInstance('Orders', 'TiendaTable');
	}
	
    /**
     * Sets the model's state
     * 
     * @return array()
     */
    function _setModelState()
    {       
        $state = parent::_setModelState();      
        $app = JFactory::getApplication();
        $model = $this->getModel( $this->get('suffix') );
        $ns = $this->getNamespace();

        $session = JFactory::getSession();
        $user = JFactory::getUser();
        
        $state['filter_user'] = $user->id;
        if (empty($user->id))
        {
            $state['filter_session'] = $session->getId();
        }

        Tienda::load('TiendaHelperUser', 'helpers.user');       
        $filter_group = TiendaHelperUser::getUserGroup($user->id);
        $state['filter_group'] = $filter_group;

        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }  
    
    /**
     * (non-PHPdoc)
     * @see tienda/admin/TiendaController::display()
     */
    function display($cachable=false, $urlparams = false)
    {
        Tienda::load('TiendaHelperCarts', 'helpers.carts');
        Tienda::load( "TiendaHelperBase", 'helpers._base' );
        $cart_helper = TiendaHelperBase::getInstance( 'Carts' );
        $cart_helper->fixQuantities();
        
        if ($return = JRequest::getVar('return', '', 'method', 'base64')) 
        {
            $return = base64_decode($return);
            if (!JURI::isInternal($return)) 
            {
                $return = '';
            }
        }
     
        $redirect = $return ? $return : JRoute::_( "index.php?option=com_tienda&view=products" );       		
       
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = TiendaHelperBase::getInstance( 'Route' );
        $checkout_itemid = $router->findItemid( array('view'=>'checkout') );
        if (empty($checkout_itemid)) { $checkout_itemid = JRequest::getInt('Itemid'); }
       
        $submenu = "submenu";
        if (empty(JFactory::getUser()->id)) { $submenu = "submenu_visitor"; }         
      
        $model  = $this->getModel( $this->get('suffix') );
        $this->_setModelState();

        $items = $model->getList();        
        $show_tax = Tienda::getInstance()->get('display_prices_with_tax');  		
        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() ); 
			
        if (!empty($items))
        {
	        //trigger the onDisplayCartItem for each cartitem
	        $dispatcher = JDispatcher::getInstance();
	        $user       = JFactory::getUser();
	        
        	if( !$user->id ) // saves session id (will be needed after logging in)
			{
				$session = JFactory::getSession();
				$session->set( 'old_sessionid', $session->getId() );
			}
        
	        $i=0;
	        $onDisplayCartItem = array();
	        foreach( $items as $item)
	        {
		        ob_start();
		        $dispatcher->trigger( 'onDisplayCartItem', array( $i, $item ) );
		        $cartItemContents = ob_get_contents();		        
		        ob_end_clean();
		        if (!empty($cartItemContents))
		        {
		        	$onDisplayCartItem[$i] = $cartItemContents;
		        } 		        
		        $i++;
	        }
	        $view->assign( 'onDisplayCartItem', $onDisplayCartItem );
	        
	        // are there any enabled coupons?
			$coupons_present = false;
			$model = JModel::getInstance( 'Coupons', 'TiendaModel' );
			$model->setState('filter_enabled', '1');
			if ($coupons = $model->getList())
			{
				$coupons_present = true;
			}
			$view->assign( 'coupons_present', $coupons_present );
        }
        $view->assign( 'return', $redirect );
        $view->assign( 'checkout_itemid', $checkout_itemid );
        $view->assign( 'submenu', $submenu );
        $view->assign( 'show_tax', $show_tax );
        $view->assign( 'using_default_geozone', false );
      	$view->assign('cartobj', $this->checkItems($items, $show_tax));
      	$view->assign( 'items', $items );      
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('default');
        $view->display();        
        $this->footer();
        return;
    }

    /**
     * Adds an item to a User's shopping cart
     * whether in the session or the db 
     * 
     */
    function addToCart()
    {
        if (!Tienda::getInstance()->get('shop_enabled', '1'))
        {
            return false;    
        }
        
        // saving the session id which will use to update the cart
        $session = JFactory::getSession();
        
        // After login, session_id is changed by Joomla, so store this for reference 
    	$session->set( 'old_sessionid', $session->getId() );
    	   	
    	$response = array();
        $response['msg'] = '';
        $response['error'] = '';
        
        // get elements from post
        $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
        
        // convert elements to array that can be binded             
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $values = TiendaHelperBase::elementsToArray( $elements );
        $product_id = !empty( $values['product_id'] ) ? $values['product_id'] : JRequest::getVar( 'product_id' );
        $product_qty = !empty( $values['product_qty'] ) ? $values['product_qty'] : '1';
        
        $attributes = array();
        foreach ($values as $key=>$value)
        {
        	if (substr($key, 0, 10) == 'attribute_')
        	{
        		$attributes[] = $value;
        	}
        }
        $attributes_csv = implode( ',', $attributes );

        // Integrity checks on quantity being added
        if ($product_qty < 0) { $product_qty = '1'; } 

        // using a helper file to determine the product's information related to inventory     
        $availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $attributes_csv );    
        if ( $availableQuantity->product_check_inventory && $product_qty > $availableQuantity->quantity ) 
        {
            JFactory::getApplication()->enqueueMessage( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY_NOTICE", $availableQuantity->product_name, $product_qty ));
            $product_qty = $availableQuantity->quantity;
        }
        
        // create cart object out of item properties
        $item = new JObject;
        $item->user_id     = JFactory::getUser()->id;
        $item->product_id  = (int) $product_id;
        $item->product_qty = (int) $product_qty;
        $item->product_attributes = $attributes_csv;
        $item->vendor_id   = '0'; // vendors only in enterprise version
        
		// onAfterCreateItemForAddToCart: plugin can add values to the item before it is being validated /added
        // once the extra field(s) have been set, they will get automatically saved
        $dispatcher = JDispatcher::getInstance();
        $results = $dispatcher->trigger( "onAfterCreateItemForAddToCart", array( $item, $values ) );
        foreach ($results as $result)
        {
            foreach($result as $key=>$value)
            {
            	$item->set($key,$value);
            }
        }	        
         
        // no matter what, fire this validation plugin event for plugins that extend the checkout workflow
        $results = array();
        $dispatcher = JDispatcher::getInstance();
        $results = $dispatcher->trigger( "onBeforeAddToCart", array( $item, $values ) );

        for ($i=0; $i<count($results); $i++)
        {
            $result = $results[$i];
            if (!empty($result->error))
            {
                Tienda::load( 'TiendaHelperBase', 'helpers._base' );
                $helper = TiendaHelperBase::getInstance();
                $response['msg'] = $helper->generateMessage( $result->message );
                $response['error'] = '1';
                echo ( json_encode( $response ) );
                return;
            }
            else
            {
                // if here, all is OK
                $response['error'] = '0';
            }
        }
        
        // add the item to the cart
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        $cart_helper = new TiendaHelperCarts();
        $cartitem = $cart_helper->addItem( $item );

        // fire plugin event
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger( 'onAfterAddToCart', array( $cartitem, $values ) );
        
        // update the cart module, if it is enabled
        $this->displayCart();
    }    

    /**
     * Displays the cart, expects to be called via ajax
     * 
     * @return unknown_type
     */
    function displayCart()
    {  
    	JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.'/components' );
        
        jimport( 'joomla.application.module.helper' );

        $modules    = JModuleHelper::_load();
        if (empty($modules))
        {
            echo ( json_encode( array('msg'=>'') ) );
            return;
        }
        
        foreach ($modules as $module)
        {
            if ($module->module == 'mod_tienda_cart')
            {
                $mainframe = JFactory::getApplication();
                $mainframe->setUserState( 'mod_usercart.isAjax', '1' );

                echo ( json_encode( array('msg'=>JModuleHelper::renderModule($module)) ) );
                return;    
            }
        }
        
        echo ( json_encode( array('msg'=>'') ) );
        return;
    }
    
    /**
     * 
     * @return unknown_type
     */
    function update()
    {
        $model 	= $this->getModel( strtolower( TiendaHelperCarts::getSuffix() ) );
        $this->_setModelState();

        $user = JFactory::getUser();
        $session = JFactory::getSession();

        $cids = JRequest::getVar('cid', array(0), '', 'ARRAY');
        $product_attributes = JRequest::getVar('product_attributes', array(0), '', 'ARRAY');
        $quantities = JRequest::getVar('quantities', array(0), '', 'ARRAY');        
        $post = JRequest::get('post');
		
        $msg = JText::_('COM_TIENDA_QUANTITIES_UPDATED');
        
        $remove = JRequest::getVar('remove');
        if ($remove) 
        {
            foreach ($cids as $cart_id=>$product_id)
            {
                $row = $model->getTable();
                
                //main cartitem keys
                $ids = array('user_id'=>$user->id, 'cart_id'=>$cart_id);
		
                if (empty($user->id))
                {
                    $ids['session_id'] = $session->getId();
                }

                if ($return = $row->delete(array('cart_id'=>$cart_id)))
                {
                    $item = new JObject;
                    $item->product_id = $product_id;
                    $item->product_attributes = $product_attributes[$cart_id];
                    $item->vendor_id = '0'; // vendors only in enterprise version
                    $item->cart_id = $cart_id;
                    // fire plugin event
                    $dispatcher = JDispatcher::getInstance();
                    $dispatcher->trigger( 'onRemoveFromCart', array( $item ) );
                }
            }
        } 
        else 
        {          
            foreach ($quantities as $cart_id=>$value) 
            {
                $carts = JTable::getInstance( 'Carts', 'TiendaTable' );
                $carts->load( array( 'cart_id'=>$cart_id) );
                $product_id = $carts->product_id;
                $value = (int) $value;
                                
//            	$keynames = explode('.', $key);
//            	$product_id = $keynames[0];
//            	$attributekey = $product_id.'.'.$keynames[1];
//            	$index = $keynames[2];

            	$vals = array();
                $vals['user_id'] = $user->id;
                $vals['session_id'] = $session->getId();
                $vals['product_id'] = $product_id;

              // fire plugin event: onGetAdditionalCartKeyValues
		        	//this event allows plugins to extend the multiple-column primary key of the carts table
//		        	$additionalKeyValues = TiendaHelperCarts::getAdditionalKeyValues( null, $post, $index );
//		        	if (!empty($additionalKeyValues))
//		        	{
//		        		$vals = array_merge($vals, $additionalKeyValues);
//		        	}

              // using a helper file,To determine the product's information related to inventory     
              $availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $product_attributes[$cart_id] );	
              if ( $availableQuantity->product_check_inventory && $value > $availableQuantity->quantity ) 
              {
              	JFactory::getApplication()->enqueueMessage( JText::sprintf("COM_TIENDA_NOT_AVAILABLE_QUANTITY", $availableQuantity->product_name, $value ));
              	$msg = JText::_('COM_TIENDA_QUANTITY_UPDATE_FAILED');
                continue;
              }
                
              if ($value > 1)
              {
	              $product = JTable::getInstance( 'Products', 'TiendaTable' );
	              $product->load( array( 'product_id'=>$product_id) );
	              if( $product->quantity_restriction )
	              {
	                  $min = $product->quantity_min;
	                  $max = $product->quantity_max;
	                   
	                  if( $max )
	                  {
	                      if ($value > $max )
	                      {
	                          $msg = JText::_('COM_TIENDA_REACHED_MAXIMUM_QUANTITY_FOR_THIS_OBJECT').$max;
	                          $value = $max;
	                      }
	                  }
	                  
	                  if( $min )
	                  {
	                      if ($value < $min )
	                      {
	                          $msg = JText::_('COM_TIENDA_REACHED_MAXIMUM_QUANTITY_FOR_THIS_OBJECT').$min;
	                          $value = $min;
	                      }
	                  }
	                  
	                  $remainder = ($value % $product->quantity_step);
	                  if (!empty($product->quantity_step) && !empty($remainder))
	                  {
	                      $msg = JText::sprintf('COM_TIENDA_QUANTITY_MUST_BE_IN_INCREMENTS_OF', $product->quantity_step);
	                      $value = ($value - $remainder) > 0 ? $value - $remainder : $min;
	                  }
	              }
	              
	              if ($product->product_recurs)
	              {
	                  $value = 1;
	              }
              }
                	
              $row = $model->getTable();
              $vals['product_attributes'] = $product_attributes[$cart_id];
              $vals['product_qty'] = $value;
              if (empty($vals['product_qty']) || $vals['product_qty'] < 1)
              {
                  // remove it
                  if ($return = $row->delete($cart_id))
                  {
                      $item = new JObject;
                      $item->product_id = $product_id;
                      $item->product_attributes = $product_attributes[$cart_id];
                      $item->vendor_id = '0'; // vendors only in enterprise version
                       
                      // fire plugin event
                      $dispatcher = JDispatcher::getInstance();
                      $dispatcher->trigger( 'onRemoveFromCart', array( $item ) );
                  }
              }
              else
              {
                  $row->load($cart_id);
                  $row->product_qty = $vals['product_qty'];
                  $row->save();
              }
            }
        }

        $carthelper = new TiendaHelperCarts();
        $carthelper->fixQuantities();
        if (empty($user->id))
        {
        	$carthelper->checkIntegrity($session->getId(), 'session_id');
        }
        else
        {
        	$carthelper->checkIntegrity($user->id);
        }
        
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute();
 
        $redirect = JRoute::_( "index.php?option=com_tienda&view=carts&Itemid=".$router->findItemid( array('view'=>'carts') ), false );
       	$this->setRedirect( $redirect, $msg);
    }
    
    /*
     * 
     */
    function confirmAdd()
    {
        $model  = $this->getModel( $this->get('suffix') );
        $this->_setModelState();
		$items = $model->getList();	
		$show_tax = Tienda::getInstance()->get('display_prices_with_tax');  
		
        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
        $view->assign('cartobj', $this->checkItems($items, $show_tax));
        $view->assign( 'show_tax', $show_tax );
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('confirmadd');
        $view->display();
        $this->footer();
        return;
    }
    
    /**
     * 
     * Method to check config, user group and product state (if recurs).
     * Then get right values accordingly
     * @param array $items - cart items
     * @param boolean - config to show tax or not
     * @return object
     */
    function checkItems( &$items, $show_tax=false )
    {    	
    	if (empty($items)) { return array(); }
    	
      	Tienda::load('TiendaHelperUser', 'helpers.user');
      	Tienda::load('TiendaHelperTax', 'helpers.tax');
      	
				if ( $show_tax )
		    	$taxes = TiendaHelperTax::calculateTax( $items, 2 );
		    	
		    $subtotal = 0;
				foreach( $items as $item )
				{
					if( $show_tax )
					{
           	$item->product_price = $item->product_price + $taxes->product_taxes[$item->product_id];
           	$item->taxtotal = $taxes->product_taxes[$item->product_id];
					}
        	
        	$item->subtotal = $item->product_price * $item->product_qty;	
        	$subtotal = $subtotal + $item->subtotal;		
				}
      	////////////////
        $cartObj = new JObject();
        $cartObj->items = $items;
	    	$cartObj->subtotal = $subtotal;
        return $cartObj;
    }

    /**
	 * Validate Coupon Code
	 *
	 * @return unknown_type
	 */
	function validateCouponCode()
		{
			JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.'/components' );
			$elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
	
			// convert elements to array that can be binded
			Tienda::load( 'TiendaHelperBase', 'helpers._base' );
			$helper = TiendaHelperBase::getInstance();
			$values = $helper->elementsToArray( $elements );
	
			$coupon_code = JRequest::getVar( 'coupon_code', '');
	
			$response = array();
			$response['msg'] = '';
			$response['error'] = '';
	
			// check if coupon code is valid
			$user_id = JFactory::getUser()->id;
			Tienda::load( 'TiendaHelperCoupon', 'helpers.coupon' );
			$helper_coupon = new TiendaHelperCoupon();
			$coupon = $helper_coupon->isValid( $coupon_code, 'code', $user_id );
			if (!$coupon)
			{
				$response['error'] = '1';
				$response['msg'] = $helper->generateMessage( $helper_coupon->getError() );
				echo json_encode($response);
				return;
			}
	
			if (!empty($values['coupons']) && in_array($coupon->coupon_id, $values['coupons']))
			{
				$response['error'] = '1';
				$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_THIS_COUPON_ALREADY_ADDED_TO_THE_ORDER') );
				echo json_encode($response);
				return;
			}
	
			// TODO Check that the user can add this coupon to the order
			$can_add = true;
			if (!$can_add)
			{
				$response['error'] = '1';
				$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_CANNOT_ADD_THIS_COUPON_TO_ORDER') );
				echo json_encode($response);
				return;
			}
	
			// Check per product coupon code
			$ids = array();
			$items = TiendaHelperCarts::getProductsInfo();
			foreach($items as $item)
			{
				$ids[] = $item->product_id;
			}
			if($coupon->coupon_type == '1')
			{
				$check = $helper_coupon->checkByProductIds($coupon->coupon_id, $ids);
				if(!$check)
				{
					$response['error'] = '1';
					$response['msg'] = $helper->generateMessage( JText::_('COM_TIENDA_THIS_COUPON_NOT_RELATED_TO_PRODUCT_IN_YOUR_CART') );
					echo json_encode($response);
					return;
				}
			}
	
			// if valid, return the html for the coupon
			$response['msg'] = " <input type='hidden' name='coupons[]' value='$coupon->coupon_id'>";
	
			echo json_encode($response);
			return;
		}
		
/**
	 * Sets the selected shipping method
	 *
	 * @return unknown_type
	 */
	function setShippingMethod()
	{
		$elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

		// convert elements to array that can be binded
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		$values = $helper->elementsToArray( $elements );

		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		// get the order object so we can populate it
		$order = $this->_order; // a TableOrders object (see constructor)

		// bind what you can from the post
		$order->bind( $values );

		// set the currency
		$order->currency_id = Tienda::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected

		// set the shipping method
		$order->shipping = new JObject();
		$order->shipping->shipping_price      = @$values['shipping_price'];
		$order->shipping->shipping_extra      = @$values['shipping_extra'];
		$order->shipping->shipping_name       = @$values['shipping_name'];
		$order->shipping->shipping_tax        = @$values['shipping_tax'];

		// set the addresses
		$this->setAddresses( $values );

		// get the items and add them to the order
		Tienda::load( "TiendaHelperBase", 'helpers._base' );
		$cart_helper = TiendaHelperBase::getInstance( 'Carts' );
		$items = $cart_helper->getProductsInfo();
		foreach ($items as $item)
		{
			$order->addItem( $item );
		}

		// get all coupons and add them to the order
		if (!empty($values['coupons']))
		{
			foreach ($values['coupons'] as $coupon_id)
			{
				$coupon = JTable::getInstance('Coupons', 'TiendaTable');
				$coupon->load(array('coupon_id'=>$coupon_id));
				$order->addCoupon( $coupon );
			}
		}

		$this->addCoupons($values);

		// get the order totals
		$order->calculateTotals();

		// now get the summary
		$html = $this->getOrderSummary();

		$response = array();
		$response['msg'] = $html;
		$response['error'] = '';

		// encode and echo (need to echo to send back to browser)
		echo json_encode($response);

		return;
	}
	
	private function addCoupons( $values )
	{
		$this->addCouponCodes( $values );
		$this->addAutomaticCoupons();
	}
	
private function addCouponCodes($values)
	{
		$order = &$this->_order;

		// get all coupons and add them to the order
		$coupons_enabled = Tienda::getInstance()->get('coupons_enabled');
		$mult_enabled = Tienda::getInstance()->get('multiple_usercoupons_enabled');
		if (!empty($values['coupons']) && $coupons_enabled)
		{
			foreach ($values['coupons'] as $coupon_id)
			{
				$coupon = JTable::getInstance('Coupons', 'TiendaTable');
				$coupon->load(array('coupon_id'=>$coupon_id));
				$order->addCoupon( $coupon );
				if (empty($mult_enabled))
				{
					// this prevents Firebug users from adding multiple coupons to orders
					break;
				}
			}
		}
	}

	private function addAutomaticCoupons()
	{
		$order = &$this->_order;
		$date = JFactory::getDate();
		$date = $date->toMysql();

		// Per Order Automatic Coupons
		$model = JModel::getInstance('Coupons', 'TiendaModel');
		$model->setState('filter_automatic', '1');
		$model->setState('filter_date_from', $date);
		$model->setState('filter_date_to', $date);
		$model->setState('filter_datetype', 'validity');
		$model->setState('filter_type', '0');
		$model->setState('filter_enabled', '1');

		$coupons = $model->getList();

		// Per Product Automatic Coupons
		$model->setState('filter_type', '1');
		$coupons_2 = $model->getList(true);

		$coupons = array_merge( $coupons, $coupons_2 );

		if($coupons)
		{
			foreach($coupons as $coupon)
			{
				$order->addCoupon($coupon);
			}
		}

	}
	
/**
	 * Prepares data for and returns the html of the order summary layout.
	 * This assumes that $this->_order has already had its properties set
	 *
	 * @return unknown_type
	 */
	function getOrderSummary()
	{
		// get the order object
		$order = $this->_order; // a TableOrders object (see constructor)

		Tienda::load('TiendaHelperCoupon', 'helpers.coupon');

		// Coupons
		$coupons_id = array();
		$coupons = $order->getCoupons();
		foreach($coupons as $cg)
		{
			foreach($cg as $c)
			{
				if($c->coupon_type == '1')
				{
					$coupons_id = array_merge($coupons_id, TiendaHelperCoupon::getCouponProductIds( @$c->coupon_id ) );
				}
			}
		}

		$model = $this->getModel('carts');
		$view = $this->getView( 'checkout', 'html' );
		$view->set( '_controller', 'checkout' );
		$view->set( '_view', 'checkout' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'coupons', $coupons_id);

		$config = Tienda::getInstance();
		$show_tax = $config->get('display_prices_with_tax');
		$view->assign( 'show_tax', $show_tax );
		$view->assign( 'using_default_geozone', false );

		$view->assign( 'order', $order );

		$orderitems = $order->getItems();

		Tienda::load( "TiendaHelperBase", 'helpers._base' );
		$order_helper = TiendaHelperBase::getInstance( 'Order' );
		$orderitems = $order->getItems();
		Tienda::load('TiendaHelperTax', 'helpers.tax');
		if ( $show_tax )
    	$taxes = TiendaHelperTax::calculateTax( $orderitems, 1, $order->getBillingAddress(), $order->getShippingAddress() );

   	$tax_sum = 0;
		foreach( $orderitems as $item )
		{
			$item->price = $item->orderitem_price + floatval( $item->orderitem_attributes_price );
			if( $show_tax )
			{
				$item->price = $item->orderitem_price + floatval( $item->orderitem_attributes_price ) + $taxes->product_taxes[$item->product_id]->amount;
				$item->orderitem_final_price = $item->price * $item->orderitem_quantity;
					
				$tax_sum += ($taxes->product_taxes[$item->product_id]->amount * $item->orderitem_quantity);
			}
		}
		$order->order_subtotal +=$tax_sum;
			
		if (empty($order->user_id))
		{
			//$order->order_total += $tax_sum;
			$order->order_tax += $tax_sum;
		}

		$view->assign( 'orderitems', $orderitems );

		// Checking whether shipping is required
		$showShipping = false;
		$cartsModel = $this->getModel('carts');
		if ($isShippingEnabled = $cartsModel->getShippingIsEnabled())
		{
			$showShipping = true;
			$view->assign( 'shipping_total', $order->getShippingTotal() );
		}
		$view->assign( 'showShipping', $showShipping );

		//START onDisplayOrderItem: trigger plugins for extra orderitem information
		if (!empty($orderitems))
		{
			$onDisplayOrderItem = $order_helper->onDisplayOrderItems($orderitems);
			$view->assign( 'onDisplayOrderItem', $onDisplayOrderItem );
		}
		//END onDisplayOrderItem

		$view->setLayout( 'cart' );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
	
/**
	 * Prepares data for and returns the html of the total amount
	 * This assumes that $this->_order has already had its properties set
	 *
	 * @return unknown_type
	 */
	function getTotalAmountDue()
	{
		// get the order object
		$order = $this->_order; // a TableOrders object (see constructor)

		$model = $this->getModel('carts');
		$view = $this->getView( 'carts', 'html' );
		$view->set( '_controller', 'carts' );
		$view->set( '_view', 'carts' );
		$view->set( '_doTask', true);
		$view->set( 'hidemenu', true);
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'order', $order );
		$orderitems = $order->order_total;
		$view->assign( 'orderitems', $orderitems );

		$view->setLayout( 'total' );

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
	
/**
	 * Returning total amount value
	 *
	 * @return unknown_type
	 */
	function totalAmountDue()
	{
		$elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

		// convert elements to array that can be binded
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );
		$helper = TiendaHelperBase::getInstance();
		$values = $helper->elementsToArray( $elements );

		$response = array();
		$response['msg'] = '';
		$response['error'] = '';

		// get the order object so we can populate it
		$order = $this->_order; // a TableOrders object (see constructor)

		// bind what you can from the post
		$order->bind( $values );

		// set the currency
		$order->currency_id = Tienda::getInstance()->get( 'default_currencyid', '1' ); // USD is default if no currency selected

		// get the items and add them to the order
		Tienda::load( "TiendaHelperBase", 'helpers._base' );
		//$cart_helper = TiendaHelperBase::getInstance( 'Carts' );
		//$items = $cart_helper->getProductsInfo();
		//foreach ($items as $item)
		//{
			//$order->addItem( $item );
		//}

		// get all coupons and add them to the order
		if (!empty($values['coupons']))
		{
			foreach ($values['coupons'] as $coupon_id)
			{
				$coupon = JTable::getInstance('Coupons', 'TiendaTable');
				$coupon->load(array('coupon_id'=>$coupon_id));
				$order->addCoupon( $coupon );
			}
		}

		// get the order totals
		$order->calculateTotals();

		// now get the summary
		$html = $this->getTotalAmountDue();

		$response = array();
		$response['msg'] = $html;
		$response['error'] = '';

		// encode and echo (need to echo to send back to browser)
		echo json_encode($response);
	}
	
/**
	 * Saves the order coupons to the DB
	 * @return unknown_type
	 */
	function saveOrderCoupons()
	{
		$order = $this->_order;
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );

		$error = false;
		$errorMsg = "";
		$ordercoupons = $order->getOrderCoupons();
		foreach ($ordercoupons as $ordercoupon)
		{
			$ordercoupon->order_id = $order->order_id;
			if (!$ordercoupon->save())
			{
				// track error
				$error = true;
				$errorMsg .= $ordercoupon->getError();
			}
		}

		if ($error)
		{
			$this->setError( $errorMsg );
			return false;
		}

		return true;
	}
}