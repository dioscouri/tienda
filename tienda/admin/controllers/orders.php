<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerOrders extends TiendaController 
{
	var $_order                = null; // a TableOrders() object
	var $initial_order_state   = 1; // Set in constructor
	var $billing_input_prefix  = 'billing_input_';
	var $shipping_input_prefix = 'shipping_input_';
	
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		$this->set('suffix', 'orders');
		$this->registerTask( 'edit', 'view' );
		$this->registerTask( 'prev', 'jump' );
		$this->registerTask( 'next', 'jump' );
		$this->registerTask( 'print', 'view' );
		$this->registerTask( 'new', 'selectUser' );
		$this->registerTask( 'add', 'selectUser' );
		$this->registerTask( 'update_status', 'updateStatus' );
		
		// create the order object
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $this->_order = JTable::getInstance('Orders', 'TiendaTable');
        $this->initial_order_state = TiendaConfig::getInstance()->get('pending_order_state', '1'); //pending
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

        $state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.created_date', 'cmd');
        $state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'DESC', 'word');
      	$state['filter_orderstate'] 	= $app->getUserStateFromRequest($ns.'orderstate', 'filter_orderstate', '', '');
      	$state['filter_user'] 	      = $app->getUserStateFromRequest($ns.'user', 'filter_user', '', '');
    	$state['filter_id_from'] 	= $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
    	$state['filter_id_to'] 		= $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_date_from'] = $app->getUserStateFromRequest($ns.'date_from', 'filter_date_from', '', '');
        $state['filter_date_to'] = $app->getUserStateFromRequest($ns.'date_to', 'filter_date_to', '', '');
        $state['filter_datetype']   = $app->getUserStateFromRequest($ns.'datetype', 'filter_datetype', '', '');
        $state['filter_total_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_total_from', '', '');
        $state['filter_total_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_total_to', '', '');
        		
    	foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );	
		}
  		return $state;
    }

    /**
     * Displays item
     * @return void
     */
    function view()
    {
        Tienda::load( 'TiendaUrl', 'library.url' );
        
        $model = $this->getModel( $this->get('suffix') );
        $order = $model->getTable( 'orders' );
        $order->load( $model->getId() );
        $row = $model->getItem();
        
        // Get the shop country name
        $row->shop_country_name = "";
        $countryModel = JModel::getInstance('Countries', 'TiendaModel');
        $countryModel->setId(TiendaConfig::getInstance()->get('shop_country'));
        $countryItem = $countryModel->getItem();
        if ($countryItem && TiendaConfig::getInstance()->get('shop_country'))
        {
            $row->shop_country_name = $countryItem->country_name;
        }
        
        // Get the shop zone name
        $row->shop_zone_name = "";
        $zoneModel = JModel::getInstance('Zones', 'TiendaModel');
        $zoneModel->setId(TiendaConfig::getInstance()->get('shop_zone'));
        $zoneItem = $zoneModel->getItem();
        if ($zoneItem && TiendaConfig::getInstance()->get('shop_zone'))
        {
            $row->shop_zone_name = $zoneItem->zone_name;
        }

        //retrieve user information and make available to page
        if (!empty($row->user_id))
        {
            //get the user information from jos_users and jos_tienda_userinfo
            $userModel  = JModel::getInstance( 'Users', 'TiendaModel' );
            $userModel->setId($row->user_id);
            $userItem = $userModel->getItem();
            if ($userItem)
            {
                $row->userinfo = $userItem;
            }       
        }
        
        $view   = $this->getView( 'orders', 'html' );
        $view->set( '_controller', 'orders' );
        $view->set( '_view', 'orders' );
        $view->setModel( $model, true );
        $view->assign( 'state', $model->getState() );
        $view->assign( 'row', $row );
        $view->assign( 'order', $order );
        
        if ($this->getTask() == 'print')
        {
            $view->setLayout( 'print' );
        }
            else
        {
            $view->setLayout( 'view' );    
        }        
        $view->display();
    }
    
    /**
     * Checks in the current item and displays the previous/next one in the list
     * @return unknown_type
     */
    function jump() 
    {
        $model  = $this->getModel( $this->get('suffix') );
        $row = $model->getTable();
        $row->load( $model->getId() );
        if (isset($row->checked_out) && !JTable::isCheckedOut( JFactory::getUser()->id, $row->checked_out) )
        {
            $row->checkin();
        }
        $task = JRequest::getVar( "task" );
        $redirect = "index.php?option=com_tienda&view=orders";
        Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
        $surrounding = TiendaHelperOrder::getSurrounding( $model->getId() );
        switch ($task)
        {
        	case "prev":
        		if (!empty($surrounding['prev']))
        		{
        			$redirect .= "&task=view&id=".$surrounding['prev'];
        		}
        		break;
        	case "next":
                if (!empty($surrounding['next']))
                {
                    $redirect .= "&task=view&id=".$surrounding['next'];
                }
        		break;
        }
        $redirect = JRoute::_( $redirect, false );
        $this->setRedirect( $redirect, $this->message, $this->messagetype );        
    }
	
	/**
	 * 
	 * Redirect to users view if no user selected 
	 * 
	 * @return unknown_type
	 */
    function selectUser()
    {
        // clear the session variables
        $this->setSessionVariable('order_products', array());
        $this->setSessionVariable('order_quantities', array());
        
    	$user_id = JRequest::getVar( 'userid', '', 'get', 'string' );
    	if (empty($user_id))
    	{
    		$redirect = 'index.php?option=com_tienda&view=users';
		  	$redirect = JRoute::_( $redirect, false );
			$this->setRedirect( $redirect, JText::_( 'Please select a user then click \'Create Order\'' ), 'message' );
    	}
    	else
    	{
            JRequest::setVar( 'hidemainmenu', '1' );
            JRequest::setVar( 'layout', 'form' );
            parent::display();
    	}
    }
    
    /**
     * Loads view for assigning product to order
     * 
     * @return unknown_type
     */
    function selectProducts()
    {
        $this->set('suffix', 'products');
        $state = parent::_setModelState();
        $app = JFactory::getApplication();
        $model = $this->getModel( $this->get('suffix') );
        $ns = $this->getNamespace();

        $state['filter_category']   = $app->getUserStateFromRequest($ns.'category', 'filter_category', '', '');

        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }

        $view = $this->getView( 'orders', 'html' );
        $view->set( '_controller', 'orders' );
        $view->set( '_view', 'orders' );
        $view->set( '_action', "index.php?option=com_tienda&controller=orders&task=selectproducts&tmpl=component" );
        $view->setModel( $model, true );
        $view->assign( 'state', $model->getState() );
        $view->setLayout( 'selectproducts' );
        $view->display();
    }
    
    /**
     * Adds products to the session variable
     * and closes the select products lightbox
     * 
     * @return unknown_type
     */
    function addProducts()
    {
    	// get the posted variables
        $cids = JRequest::getVar('cid', array(0), 'request', 'array');
        $quantity = JRequest::getVar('quantity', array(0), 'request', 'array');
        
        // get the session variables
        $order_products = $this->getSessionVariable('order_products', array());
        $order_quantities = $this->getSessionVariable('order_quantities', array());
        
        jimport('joomla.utilities.arrayhelper'); //joomla/utilities/arrayhelper.php (line 23) 
        $order_products = JArrayHelper::fromObject( $order_products );
        $order_quantities = JArrayHelper::fromObject( $order_quantities );
        
        if (empty($order_products)) { $order_products = array(); }
        if (empty($order_quantities)) { $order_quantities = array(); }
    
        foreach (@$cids as $cid)
        {
        	if (empty($cid)) { continue; }
        	
            if (!in_array($cid, $order_products))
            {
                // Add the posted products to the session variable if it doesn't exist
            	$order_products[$cid] = $cid;
            	$order_quantities[$cid] = $quantity[$cid];
            }
            else
            {
            	$order_products[$cid] = $cid;
                // If it exists, update quantity
                if (!array_key_exists($cid, $order_quantities))
                {
                	$order_quantities[$cid] = $quantity[$cid];
                }
                else
                {
                    $order_quantities[$cid] += $quantity[$cid];	
                }
            }
        }
        
        // save to the session
        $this->setSessionVariable('order_products', $order_products);
        $this->setSessionVariable('order_quantities', $order_quantities);
        
        // set the close window variable so the view closes the lightbox
        JRequest::setVar('windowtask', 'close');
        
        $model = $this->getModel( $this->get('suffix') );        
        $view = $this->getView( 'orders', 'html' );
        $view->set( '_controller', 'orders' );
        $view->set( '_view', 'orders' );
        $view->set( '_action', "index.php?option=com_tienda&controller=orders&task=selectproducts&tmpl=component" );
        $view->setModel( $model, true );
        $view->setLayout( 'close' );
        $view->display();
    }
    
    /**
     * Gets the products from the session variable
     * and returns formatted HTML
     * 
     * @return unknown_type
     */
    function getProducts()
    {
        $row = new JObject();
        // TODO This needs to reflect the order's selected currency
        $row->orderitems = $this->getProductsInfo();
    	
        $this->set('suffix', 'orders');
        $model = $this->getModel( $this->get('suffix') );
        $view   = $this->getView( 'orders', 'html' );
        $view->set( '_controller', 'orders' );
        $view->set( '_view', 'orders' );
        $view->setModel( $model, true );
        $view->assign( 'row', $row );
        $view->assign( 'state', $model->getState() );
        $view->setLayout( 'orderproducts' );
    	
        ob_start();
        $view->display();
        $html = ob_get_contents(); 
        ob_end_clean();
        
        $response = array();
        $response['msg'] = $html;
        $response['error'] = '';        
        
        echo ( json_encode( $response ) );
        
        return;
    }
    
    /**
     * Updates product quantities in session based on inputs from form
     * and returns HTML
     * 
     * @return unknown_type
     */
    function updateProductQuantities()
    {
        // get elements from post
        $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
        // convert elements to array that can be binded             
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $values = TiendaHelperBase::elementsToArray( $elements );

        $cids = $values['products'];
        $quantities = $values['quantity'];
        $checked = $values['_checked'];
        
        // get the session variables
        $order_products = $this->getSessionVariable('order_products', array());
        $order_quantities = $this->getSessionVariable('order_quantities', array());
        
        jimport('joomla.utilities.arrayhelper'); //joomla/utilities/arrayhelper.php (line 23) 
        $order_products = JArrayHelper::fromObject( $order_products );
        $order_quantities = JArrayHelper::fromObject( $order_quantities );
        
        foreach (@$cids as $cid)
        {
            if (empty($cid)) { continue; }
            
            $order_quantities[$cid] = $quantities[$cid];
        }
        
        // save to the session
        $this->setSessionVariable('order_products', $order_products);
        $this->setSessionVariable('order_quantities', $order_quantities);
        
        return $this->getProducts();
    }
    
    /**
     * Removes selected products from session
     * and returns HTML
     * 
     * @return unknown_type
     */
    function removeProducts()
    {
        // get elements from post
        $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
        // convert elements to array that can be binded             
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $values = TiendaHelperBase::elementsToArray( $elements );

        $cids = $values['products'];
        $checked = $values['_checked'];
        
        // get the session variables
        $order_products = $this->getSessionVariable('order_products', array());
        $order_quantities = $this->getSessionVariable('order_quantities', array());
        
        jimport('joomla.utilities.arrayhelper'); //joomla/utilities/arrayhelper.php (line 23) 
        $order_products = JArrayHelper::fromObject( $order_products );
        $order_quantities = JArrayHelper::fromObject( $order_quantities );
        
        foreach (@$cids as $cid)
        {
            if (empty($cid)) { continue; }
            
            if (in_array($cid, $checked['products']))
            {
                // remove the product
                unset($order_products[$cid]);
                unset($order_quantities[$cid]);
            }
        }
        
        // save to the session
        $this->setSessionVariable('order_products', $order_products);
        $this->setSessionVariable('order_quantities', $order_quantities);
        
        return $this->getProducts();
    }
    
    /**
     * Gets the products from the session variable
     * 
     * @return unknown_type
     */
    function getProductsInfo( $currency_id=0, $geozone_id=0 )
    {
        // get the session variables
        $order_products = $this->getSessionVariable('order_products', array());
        $order_quantities = $this->getSessionVariable('order_quantities', array());
        
        jimport('joomla.utilities.arrayhelper'); //joomla/utilities/arrayhelper.php (line 23) 
        $order_products = JArrayHelper::fromObject( $order_products );
        $order_quantities = JArrayHelper::fromObject( $order_quantities );
        
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $items = array();
        
        if (empty($order_products)) { $order_products = array(); }
        if (empty($order_quantities)) { $order_quantities = array(); }
        
        foreach ($order_products as $product)
        {
        	unset($productModel);
            $productModel = JModel::getInstance('Products', 'TiendaModel');
            $productModel->setId( $product );
            if ($productItem = $productModel->getItem())
            {
            	// TODO Push this into the orders object->addItem() method?
	            $orderItem = JTable::getInstance('OrderItems', 'TiendaTable');
	            $orderItem->product_id             = $productItem->product_id;
	            $orderItem->orderitem_sku          = $productItem->product_sku;
	            $orderItem->orderitem_name         = $productItem->product_name;
	            $orderItem->orderitem_quantity     = $order_quantities[$product];
	            $orderItem->orderitem_price        = $productItem->price;               
	            $orderItem->orderitem_final_price  = $productItem->price * $orderItem->orderitem_quantity;
	            // TODO When do attributes for selected item get set during admin-side order creation?
	            array_push($items, $orderItem);            	
            }
        }
        
        return $items;
    }
    
    /**
     * Calculates the order total line items
     * and returns formatted HTML
     * Expects to be called by Ajax
     * 
     * @return unknown_type
     */
    
    function getOrderTotals()
    {
        // get elements from post
        $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

        // convert elements to array that can be binded             
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $values = TiendaHelperBase::elementsToArray( $elements );

        // get the order object so we can populate it
        $order = $this->_order; // a TableOrders object (see constructor)

        // bind what you can from the post
        $order->bind( $values );
        
        // set the currency
        //TODO: Change this to currency_id in the form
        $order->currency_id = $values['order_currency_id'];
        
        // set the shipping method
        $order->shipping_method_id = @$values['_checked']['shipping_method_id'];
        
        // set the order's addresses based on the form inputs
        $this->setAddresses( $values );
        
        // get the items and add them to the order
        $items = $this->getProductsInfo( $order->currency_id, $order->getBillingGeozone() );
        foreach ($items as $item)
        {
            $order->addItem( $item );
        }
        
        // get the order totals
        $order->calculateTotals();
        
        $model = $this->getModel( $this->get('suffix') );
        $view = $this->getView( 'orders', 'html' );
        $view->set( '_controller', 'orders' );
        $view->set( '_view', 'orders' );
        $view->setModel( $model, true );
        $view->assign( 'state', $model->getState() );
        $view->assign( 'row', $order );
        $view->assign( 'shipping_total', $order->getShippingTotal() );
        $view->setLayout( 'ordertotals' );

        ob_start();
        $view->display();
        $html = ob_get_contents(); 
        ob_end_clean();

        $response = array();
        $response['msg'] = $html;
        $response['error'] = '';        
        
        echo ( json_encode( $response ) );
        
        return;
    }
    
    /**
     * Sets a json_encoded session variable to value
     * 
     * @param unknown_type $key
     * @param unknown_type $value
     * @return void
     */
    function setSessionVariable($key, $value)
    {
        $session =& JFactory::getSession();
        $session->set($key, json_encode($value));
    }
    
    /**
     * Gets json_encoded session variable
     * 
     * @param str $key
     * @return mixed
     */
    function getSessionVariable($key, $default=null)
    {
        $session =& JFactory::getSession();
        $sessionvalue = $default;
        if ($session->has($key))
        {
            $sessionvalue = $session->get($key);
            if (!empty($sessionvalue))
            {
                $sessionvalue = json_decode($sessionvalue); 
            }
        }
        return $sessionvalue;
    }
    
    /**
     * Sets the order object's address values based on the form inputs
     * 
     * @param $values
     * @return unknown_type
     */
    function setAddresses( $values )
    {
    	$order = $this->_order; // a TableOrders object (see constructor)

        $currency_id            = $values['order_currency_id'];
        $billing_address_id     = $values['billing_address_id'];
        $shipping_address_id    = $values['shipping_address_id'];
        $shipping_method_id     = @$values['_checked']['shipping_method_id'];
        $same_as_billing        = @$values['_checked']['sameasbilling'];
        $user_id                = $values['user_id'];
        $billing_input_prefix   = $this->billing_input_prefix;
        $shipping_input_prefix  = $this->shipping_input_prefix;
            	    	
        $billing_zone_id = 0;
        $billingAddressArray = $this->getAddress( $billing_address_id, $billing_input_prefix, $values );
        if (array_key_exists('zone_id', $billingAddressArray)) 
        {
            $billing_zone_id = $billingAddressArray['zone_id'];
        }
        
        //SAME AS BILLING
        if ($same_as_billing) //if shipping should be the same as billing
        {
            $shipping_address_id = $billing_address_id;
            $shipping_input_prefix = $billing_input_prefix;
        }       

        //SHIPPING ADDRESS: get shipping address from dropdown or form (depending on selection)
        $shipping_zone_id = 0;      
        $shippingAddressArray = $this->getAddress($shipping_address_id, $shipping_input_prefix, $values);
        if (array_key_exists('zone_id', $shippingAddressArray)) 
        {
            $shipping_zone_id = $shippingAddressArray['zone_id'];
        }

        // keep the array for binding during the save process
        $this->_billingAddressArray = $this->filterArrayUsingPrefix($billingAddressArray, '', 'billing_', true);
        $this->_shippingAddressArray = $this->filterArrayUsingPrefix($shippingAddressArray, '', 'shipping_', true);
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $billingAddress = JTable::getInstance('Addresses', 'TiendaTable');
        $shippingAddress = JTable::getInstance('Addresses', 'TiendaTable');

        // set the order billing address
        $billingAddress->bind( $billingAddressArray );
        $billingAddress->user_id = $user_id;
        $order->setAddress( $billingAddress, 'billing' );
        
        // set the order shipping address
        $shippingAddress->bind( $shippingAddressArray );
        $shippingAddress->user_id = $user_id;
        $order->setAddress( $shippingAddress, 'shipping' );
    }

	/**
	 * Gets the address from the form
	 * using the address from the dropdown if selected
	 * otherwise using the address from the inputs
	 * 
	 * @param int $address_id
	 * @param string $input_prefix
	 * @param array $form_input_array
	 * @return array
	 */
	function getAddress( $address_id, $input_prefix, $form_input_array )
	{
		$addressArray = array();
		if (!empty($address_id))
		{
			$addressArray = $this->retrieveAddressIntoArray($address_id);
		}
		else 
		{
			$addressArray = $this->filterArrayUsingPrefix($form_input_array, $input_prefix, '', false );
		}		
		return $addressArray;	
	}	
	
	/**
	 * Loads an address row and converts it to an array
	 * 
	 * @param unknown_type $address_id
	 * @return unknown_type
	 */
	function retrieveAddressIntoArray( $address_id )
	{
		$model = JModel::getInstance( 'Addresses', 'TiendaModel' );
		$model->setId($address_id);
		$item = $model->getItem();		
		return get_object_vars( $item );

	}
    
	/**
	 * Parses array from order form using prefix.
	 * Optionally appends prefix to each key in array.
	 * 
	 * @param $oldArray
	 * @param $old_prefix
	 * @param $new_prefix
	 * @param $append
	 * @return unknown_type
	 */
	function filterArrayUsingPrefix( $oldArray, $old_prefix, $new_prefix, $append )
	{
    	// create array with input form keys and values
    	$address_input = array();

    	foreach ($oldArray as $key => $value)
    	{
    		if (($append) || (strpos($key, $old_prefix) !== false)) 
    		{
    			$new_key = '';
    			if ($append){$new_key = $new_prefix.$key;}
    			else{
    				$new_key = str_replace($old_prefix, $new_prefix, $key);
    			}
    			if (strlen($new_key)>0){
    				$address_input[$new_key] = $value;
    			}
    		}	
    	}
		return $address_input;		
	}

    /**
     * Saves an item and redirects based on task
     * @return void
     */
    function save() 
    {
    	$values = JRequest::get('post');

        // get the order object so we can populate it
        $order =& $this->_order; // a TableOrders object (see constructor)

        // bind what you can from the post
        $order->bind( $values );
        
        // set the currency
        $order->currency_id = $values['order_currency_id'];
        
        // set the shipping method
        $order->shipping_method_id = @$values['shipping_method_id'];
        
        $order->order_state_id    = $this->initial_order_state;
        $order->ip_address        = $_SERVER['REMOTE_ADDR'];
                
        // set the order's addresses based on the form inputs
        $this->setAddresses( $values );
        
        // get the items and add them to the order
        $items = $this->getProductsInfo( $order->currency_id, $order->getBillingGeozone() );
        foreach ($items as $item)
        {
            $order->addItem( $item );
        }
        
        // get the order totals
        $order->calculateTotals();
        $order->calculateVendorTotals();
        
        // get the ordernumber
        // $order->generateOrderNumber();
        
        // TODO Since is all one transaction, is there some way to preserve the ability to rollback if one part of it fails?
        // Can we perform a $row->check() on each individual part (orderinfo, orderitems, etc)
        // before saving *any* of them?
        
        $error = false;
        // then save the order to the orders table, one for each vendor
        // TODO add each part of this transaction to the orders->save() method?
        $model  = $this->getModel( $this->get('suffix') );

        // TODO Fix each $this->saveOrderSomething to use the orders object
        if ( $order->save() ) 
        {
            $model->setId( $order->order_id );
            
            // save the order items
            if (!$this->saveOrderItems())
            {
                // TODO What to do if saving order items fails?
                $error = true;
            }
            
            // save the order vendors
            if (!$this->saveOrderVendors())
            {
                // TODO What to do if saving order vendors fails?
                $error = true;
            }
            
            // save the order info
            if (!$this->saveOrderInfo())
            {
                // TODO What to do if saving order info fails?
                $error = true;
            }

            // save the order history
            if (!$this->saveOrderHistory())
            {
                // TODO What to do if saving order history fails?
                $error = true;
            }
            
            // if the billing address is new & user requests save, then save it
            if (empty($values['billing_address_id']) && $values['_checked']['billing_save_to_address_book'])
            {
            	$billingAddress = $order->getBillingAddress();
                if (!$billingAddress->save())
                {
                    // TODO What to do if saving address fails?
                    $error = true;
                    $this->setError( $billingAddress->getError() );
                }
            }
        
            // the shipping address is new && !same as the billing address, 
            // and the user requests save, then save it
            if (empty($values['_checked']['sameasbilling']) && empty($values['shipping_address_id']) && !empty($values['_checked']['shipping_save_to_address_book']))
            {
            	$shippingAddress = $order->getShippingAddress();
                if (!$shippingAddress->save())
                {
                    // TODO What to do if saving address fails?
                    $error = true;
                    $this->setError( $shippingAddress->getError() );
                }
            }
        
            $this->messagetype  = 'message';
            $this->message      = JText::_( 'Saved' );
            if ($error)
            {
                $this->messagetype  = 'notice';
                $this->message .= " :: ".$this->getError(); 
            }
            
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $order ) );
        } 
            else 
        {
            $this->messagetype  = 'notice';         
            $this->message      = JText::_( 'Save Failed' )." - ".$order->getError();
        }  

        // clear the session of the stored items
        $this->setSessionVariable('order_products', array());
        $this->setSessionVariable('order_quantities', array());
        
        $redirect = "index.php?option=com_tienda";
        $task = JRequest::getVar('task');
        switch ($task)
        {
            case "savenew":
                $redirect .= '&view='.$this->get('suffix').'&task=add';
              break;
            case "apply":
                $redirect .= '&view='.$this->get('suffix').'&task=edit&id='.$model->getId();
              break;
            case "save":
            default:
                $redirect .= "&view=".$this->get('suffix');
              break;
        }

        $redirect = JRoute::_( $redirect, false );
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
    
    /**
     * Saves the Order Info 
     * 
     * @return boolean
     */
    function saveOrderInfo()
    {
    	$order =& $this->_order;
    	
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $row = JTable::getInstance('OrderInfo', 'TiendaTable');
        $row->order_id = $order->order_id;
        $row->bind( $this->_billingAddressArray );  
        $row->bind( $this->_shippingAddressArray );                
        
        if (!$row->save())
        {
            $this->setError( $row->getError() );
            return false;
        }
        return true;
    }

    /**
     * Saves the order history and emails the customer if requested
     * 
     * @return boolean
     */
    function saveOrderHistory()
    {
    	$order =& $this->_order;
    	
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $row = JTable::getInstance('OrderHistory', 'TiendaTable');
        $row->order_id = $order->order_id;
        $row->order_state_id = $order->order_state_id;
        
        $row->notify_customer = '1'; 
        $row->comments = JRequest::getVar('order_history_comments', '', 'post');
        
        if (!$row->save())
        {
            $this->setError( $row->getError() );
            return false;
        }
        return true;            
    }
    
    /**
     * Inserts a record in orderitems for each item in the order
     * 
     * @return boolean
     */
    function saveOrderItems()
    {
    	$order =& $this->_order;
    	$items = $order->getItems();
    	
    	if (empty($items) || !is_array($items))
    	{
    		$this->setError( "saveOrderItems:: ".JText::_( "Items Array is Invalid" ) );
    		return false;
    	}
    	
        $error = false;
        $errorMsg = "";
        foreach ($items as $item)
        {
            $item->order_id = $order->order_id;
            // since they are always the same, why store it twice?
            // $item->order_item_currency_id = $order->currency_id;
            if (!$item->save())
            {
                // track error
            	$error = true;
            	$errorMsg .= $item->getError();
            }
        }
        
        if ($error)
        {
        	$this->setError( $errorMsg );
        	return false;
        }
        return true;
    }
    
    /**
     * Inserts a record in ordervendors for each vendor associated with the order
     * 
     * @return boolean
     */
    function saveOrderVendors()
    {
        $order =& $this->_order;
        $items = $order->getVendors();
        
        if (empty($items) || !is_array($items))
        {
        	// No vendors other than store owner, so just skip this
            //$this->setError( "saveOrderVendors:: ".JText::_( "Vendors Array is Invalid" ) );
            //return false;
            return true;    
        }
        
        $error = false;
        $errorMsg = "";
        foreach ($items as $item)
        {
        	if (empty($item->vendor_id))
        	{
        		continue;
        	}
            $item->order_id = $order->order_id;
            if (!$item->save())
            {
                // track error
                $error = true;
                $errorMsg .= $item->getError();
            }
        }
        
        if ($error)
        {
            $this->setError( $errorMsg );
            return false;
        }
        return true;
    }
    
    /**
     * Updates an order's status, adding a record to its hitory
     * and redirects back to the view order page
     * 
     * @return void
     */
    function updateStatus()
    {
    	$model  = $this->getModel( $this->get('suffix') );
        $row = $model->getTable();
        $row->load( $model->getId() );
        $row->order_state_id = JRequest::getVar('new_orderstate_id');
      
        if ( $row->save() )
        {
            $model->setId( $row->order_id );
            $this->messagetype  = 'message';
            $this->message      = JText::_( 'Order Saved' );
            
	        $history = JTable::getInstance('OrderHistory', 'TiendaTable');
	        $history->order_id             = $row->order_id;
	        $history->order_state_id       = $row->order_state_id;
	        $history->notify_customer      = JRequest::getVar('new_orderstate_notify'); 
	        $history->comments             = JRequest::getVar('new_orderstate_comments');
	        
	        if (!$history->save())
	        {
	            $this->setError( $history->getError() );
	            $this->messagetype  = 'notice';
	            $this->message      .= " :: ".JText::_( 'OrderHistory Save Failed' );
	        }

            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterUpdateStatus'.$this->get('suffix'), array( $row ) );
        }
            else
        {
            $this->messagetype  = 'notice';
            $this->message      = JText::_( 'Save Failed' )." - ".$row->getError();
        }

        $redirect = "index.php?option=com_tienda";
        $redirect .= '&view='.$this->get('suffix').'&task=view&id='.$model->getId();
        $redirect = JRoute::_( $redirect, false );
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
}
?>