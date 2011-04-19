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

Tienda::load( 'TiendaHelperProductCompare', 'helpers.compare' );
//Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaControllerProductCompare extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();

        $this->set('suffix', 'productcompare');
	}
	
    /**
     * Sets the model's state
     * 
     * @return array()
     */
    function _setModelState()
    {       
        $state = parent::_setModelState();    
        $model = $this->getModel( $this->get('suffix') );        
        $user =& JFactory::getUser();
                
        $state['filter_user'] = $user->id;
        if (empty($user->id))
        {
        	$session =& JFactory::getSession();
            $state['filter_session'] = $session->getId();
        }

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
    function display()
    {
        JRequest::setVar( 'view', $this->get('suffix') );		
		$view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
		$model  = $this->getModel( $this->get('suffix') );
    	$this->_setModelState();
		$items =& $model->getList();  
		
		Tienda::load( "TiendaHelperProduct", 'helpers.product' );
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
     * Adds an item to a User's Product Compare
     * whether in the session or the db 
     * 
     */
    function addProductToCompare()
    {        
        // saving the session id which will use to update the cart
        $session =& JFactory::getSession();
        $userid = JFactory::getUser()->id;
        
        // After login, session_id is changed by Joomla, so store this for reference 
    	$session->set( 'old_sessionid', $session->getId() );
    	   	
    	$response = array();
        $response['msg'] = '';
        $response['error'] = '';
          
        $product_id = JRequest::getVar( 'product_id' );  
        $add = JRequest::getVar( 'add', 1 );
        
        //deleting product to compare
        if(!$add)
        {
	        $db = JFactory::getDBO();	
	        Tienda::load( 'TiendaQuery', 'library.query' );
	        $query = new TiendaQuery();	        
	        $query->delete();
	        $query->from( "#__tienda_productcompare" );
	        $query->where( "`product_id` = '$product_id' " );
	        $query->where( "`session_id` = '".$session->getId()."' " );
	        $query->where( "`user_id` = '$userid'" );
	        $db->setQuery( (string) $query );
	        if (!$db->query())
	        {
	        	$response['msg'] = $helper->generateMessage($db->getErrorMsg());
        		$response['error'] = '1';	          
	            return false;
	        }
        }
        else
        {
         	Tienda::load( 'TiendaHelperProductCompare', 'helpers.productcompare' );
        	$compare_helper = new TiendaHelperProductCompare();
	        
	        //check limit
	        $compareLimit = $compare_helper->checkLimit();
	        if(!$compareLimit)
	        {
	        	Tienda::load( 'TiendaHelperBase', 'helpers._base' );
				$helper = TiendaHelperBase::getInstance();  			
	  			$limit = TiendaConfig::getInstance()->get('compared_products', '5');     	
	        	$response['msg'] = $helper->generateMessage(JText::_("Only {$limit} products can be added to compare."));
	        	$response['error'] = '1';
	        	echo json_encode($response);
	        	return;
	        }
	        
		    // create cart object out of item properties
	        $item = new JObject;
	        $item->user_id     = $userid;
	        $item->product_id  = (int) $product_id;  
	        
	        // add the item to the product comparison        
	        $compare_item = $compare_helper->addItem( $item );
        }
                
        
        //load user compared items        
        $model  = $this->getModel( $this->get('suffix') );
     	$model->setState('filter_user', $userid );
        if (empty($user->id))
        {
            $model->setState('filter_session', $session->getId() );
        }
        
		$items = $model->getList();
		
		//TODO: make it to call a view
		$response['msg'] .= '<ul>';
		foreach($items as $item)
		{
			$table = JTable::getInstance('Products', 'TiendaTable');
        	$table->load(array('product_id'=> $item->product_id));
        	$response['msg'] .= '<li>';
        	$response['msg'] .= '<a href="'.JRoute::_('index.php?option=com_tienda&view=products&task=view&id='.$item->product_id).'">';
        	$response['msg'] .= $table->product_name;
        	$response['msg'] .= '</a>';
        	$response['msg'] .= '</li>';    
		}		
        $response['msg'] .= '</ul>';
		
		echo json_encode($response);
        return;
    }    

    function remove()
    {
    	$id = JRequest::getInt('id');    	
    	$model = $this->getModel($this->get('suffix'));
    	$table= $model->getTable();
    	$table->delete($id);    	
    	$redirect = JRoute::_( "index.php?option=com_tienda&view=productcompare#tienda-compare", false );
    	$this->setRedirect( $redirect );
    }
    
    
    /**
     * Displays the cart, expects to be called via ajax
     * 
     * @return unknown_type
     */
    function displayCart()
    {  
    	JLoader::import( 'com_tienda.library.json', JPATH_ADMINISTRATOR.DS.'components' );
        
        jimport( 'joomla.application.module.helper' );

        $modules    =& JModuleHelper::_load();
        if (empty($modules))
        {
            echo ( json_encode( array('msg'=>'') ) );
            return;
        }
        
        foreach ($modules as $module)
        {
            if ($module->module == 'mod_tienda_cart')
            {
                $mainframe =& JFactory::getApplication();
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
        $model 	= $this->getModel( strtolower(TiendaHelperCarts::getSuffix()) );
        $this->_setModelState();

        $user =& JFactory::getUser();
        $session =& JFactory::getSession();

        $cids = JRequest::getVar('cid', array(0), '', 'ARRAY');
        $product_attributes = JRequest::getVar('product_attributes', array(0), '', 'ARRAY');
        $quantities = JRequest::getVar('quantities', array(0), '', 'ARRAY');        
        $post = JRequest::get('post');
		
        $msg = JText::_('Quantities Updated');
        
        $remove = JRequest::getVar('remove');
        if ($remove) 
        {
            foreach ($cids as $cart_id=>$product_id)
            {
//            	$keynames = explode('.', $key);
//            	$attributekey = $keynames[0].'.'.$keynames[1];
//            	$index = $keynames[2];
                $row = $model->getTable();
                
                //main cartitem keys
                $ids = array('user_id'=>$user->id, 'cart_id'=>$cart_id);

                // fire plugin event: onGetAdditionalCartKeyValues
				        //this event allows plugins to extend the multiple-column primary key of the carts table
				        $additionalKeyValues = TiendaHelperCarts::getAdditionalKeyValues( null, $post, $index );
				        if (!empty($additionalKeyValues))
				        {
				        	$ids = array_merge($ids, $additionalKeyValues);
				        }
		
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
              	JFactory::getApplication()->enqueueMessage( JText::sprintf( 'NOT_AVAILABLE_QUANTITY', $availableQuantity->product_name, $value ));
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
                  		$msg = JText::_('You have reached the maximum quantity for this object: ').$max;
                  		$value = $max;
                  	}
                  }
                  if( $min )
                  {
                  	if ($value < $min )
                  	{
                  		$msg = JText::_('You have reached the minimum quantity for this object: ').$min;
                  		$value = $min;
                  	}
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
		$items =& $model->getList();	
		$show_tax = TiendaConfig::getInstance()->get('display_prices_with_tax');  
		
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
      	Tienda::load( "TiendaHelperProduct", 'helpers.product' );
         	
        if ($show_tax)
        {            
            $geozones = TiendaHelperUser::getGeoZones( JFactory::getUser()->id );
            if (empty($geozones))
            {
                // use the default
                $table = JTable::getInstance('Geozones', 'TiendaTable');
                $table->load(array('geozone_id'=>TiendaConfig::getInstance()->get('default_tax_geozone')));
                $geozones = array( $table );
            }          
        }        
      	$subtotal = 0;
        foreach ($items as $item)
        {
        	/* This is not necessary! already done in the model! 
        	if(!$item->product_recurs)
        	{
				$filter_group = TiendaHelperUser::getUserGroup(JFactory::getUser()->id, $item->product_id);
				$priceObj = TiendaHelperProduct::getPrice($item->product_id, '1', $filter_group);
				$item->product_price = $priceObj->product_price;
        	}  */
        	
        	if($show_tax)
        	{
        		$cartitem_tax = 0;
		        foreach ($geozones as $geozone)
		        {		           
		            $taxrate = TiendaHelperProduct::getTaxRate($item->product_id, $geozone->geozone_id, true );
		            $product_tax_rate = $taxrate->tax_rate;
		            
		            // track the total tax for this item
		            $cartitem_tax += ($product_tax_rate/100) * $item->product_price;		            
		        }		               		
        		//$taxtotal = TiendaHelperProduct::getTaxTotal($item->product_id, $geozones);        	
           	 	$item->product_price = $item->product_price + $cartitem_tax;
            	$item->taxtotal = $cartitem_tax;
        	}        	
        	
        	$item->subtotal = $item->product_price * $item->product_qty;	
        	$subtotal = $subtotal + $item->subtotal;		
        }
        $cartObj = new JObject();
        $cartObj->items = $items;
    	$cartObj->subtotal = $subtotal;
        return $cartObj;
    }
}