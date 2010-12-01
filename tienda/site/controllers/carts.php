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
//Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaControllerCarts extends TiendaController
{

	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();

        $this->set('suffix', 'carts');
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

        $session =& JFactory::getSession();
        $user =& JFactory::getUser();
        
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
  
    function display()
    {
        Tienda::load('TiendaHelperCarts', 'helpers.carts');
        TiendaHelperCarts::fixQuantities();
        
        if ($return = JRequest::getVar('return', '', 'method', 'base64')) 
        {
            $return = base64_decode($return);
            if (!JURI::isInternal($return)) 
            {
                $return = '';
            }
        }
        
        if ($return)
        {
            $redirect = $return;
        }
            else 
        {
            $redirect = JRoute::_( "index.php?option=com_tienda&view=products" ); 
        }

        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute();
        $checkout_itemid = $router->findItemid( array('view'=>'checkout') );
        if (empty($checkout_itemid)) { $checkout_itemid = JRequest::getInt('Itemid'); }
        
        $model  = $this->getModel( $this->get('suffix') );
        $this->_setModelState();

        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('default');
        $view->assign( 'return', $redirect );
        $view->assign( 'checkout_itemid', $checkout_itemid );
        
        //get cartitem information from plugins
        $list =& $model->getList();
        
        $config = TiendaConfig::getInstance();
        $show_tax = $config->get('display_prices_with_tax');
        $view->assign( 'show_tax', $show_tax );
        $view->assign( 'using_default_geozone', false );

        if ($show_tax)
        {
            Tienda::load('TiendaHelperUser', 'helpers.user');
            $geozones = TiendaHelperUser::getGeoZones( JFactory::getUser()->id );
            if (empty($geozones))
            {
                // use the default
                $view->assign( 'using_default_geozone', true );
                $table = JTable::getInstance('Geozones', 'TiendaTable');
                $table->load(array('geozone_id'=>TiendaConfig::getInstance()->get('default_tax_geozone')));
                $geozones = array( $table );
            }
            
            Tienda::load( "TiendaHelperProduct", 'helpers.product' );
            foreach ($list as &$item)
            {
                $taxtotal = TiendaHelperProduct::getTaxTotal($item->product_id, $geozones);
                $item->product_price = $item->product_price + $taxtotal->tax_total;
                $item->taxtotal = $taxtotal;
            }
        }
        
        if (!empty($list))
        {
	        //trigger the onDisplayCartItem for each cartitem
	        $dispatcher =& JDispatcher::getInstance();
        
	        $i=0;
	        $onDisplayCartItem = array();
	        foreach( $list as $item)
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
        }
        
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
        if (!TiendaConfig::getInstance()->get('shop_enabled', '1'))
        {
            return false;    
        }
        
        // saving the session id which will use to update the cart
        $session =& JFactory::getSession();
        
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
            JFactory::getApplication()->enqueueMessage( JText::sprintf( 'NOT_AVAILABLE_QUANTITY', $availableQuantity->product_name, $product_qty ));
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
        $dispatcher =& JDispatcher::getInstance();
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
        $dispatcher =& JDispatcher::getInstance();
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
        TiendaHelperCarts::updateCart( array( $item ) );

        // fire plugin event
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger( 'onAfterAddToCart', array( $item, $values ) );
        
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
            foreach ($cids as $key=>$product_id)
            {
            		$keynames = explode('.', $key);
            		$attributekey = $keynames[0].'.'.$keynames[1];
            		$index = $keynames[2];
                $row = $model->getTable();
                
                //main cartitem keys
                $ids = array('user_id'=>$user->id, 'product_id'=>$product_id, 
                				'product_attributes'=>$product_attributes[$attributekey]);

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
		
		                if ($return = $row->delete($ids))
		                {
			                $item = new JObject;
			                $item->product_id = $product_id;
			                $item->product_attributes = $product_attributes[$attributekey];
			                $item->vendor_id = '0'; // vendors only in enterprise version
			               
			                // fire plugin event
			                $dispatcher = JDispatcher::getInstance();
			                $dispatcher->trigger( 'onRemoveFromCart', array( $item ) );
		                }
            }
        } 
        else 
        {          
            foreach($quantities as $key=>$value) 
            {          	
            	$keynames = explode('.', $key);
            	$product_id = $keynames[0];
            	$attributekey = $product_id.'.'.$keynames[1];
            	$index = $keynames[2];

            	$vals = array();
              $vals['user_id'] = $user->id;
              $vals['session_id'] = $session->getId();
              $vals['product_id'] = $product_id;

              // fire plugin event: onGetAdditionalCartKeyValues
		        	//this event allows plugins to extend the multiple-column primary key of the carts table
		        	$additionalKeyValues = TiendaHelperCarts::getAdditionalKeyValues( null, $post, $index );
		        	if (!empty($additionalKeyValues))
		        	{
		        		$vals = array_merge($vals, $additionalKeyValues);
		        	}

              // using a helper file,To determine the product's information related to inventory     
              $availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $product_attributes[$attributekey] );	
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
              $vals['product_attributes'] = $product_attributes[$attributekey];
              $vals['product_qty'] = $value;
              if (empty($vals['product_qty']) || $vals['product_qty'] < 1)
              {
              	unset($vals['product_qty']);
                $vals['user_id'] = $user->id;
              	if(empty($user->id))
              		$vals['session_id'] = $session->getId();              	
              	
              	// remove it
              	if ($return = $row->delete($vals))
              	{
              		$item = new JObject;
              		$item->product_id = $product_id;
                  $item->product_attributes = $product_attributes[$attributekey];
                  $item->vendor_id = '0'; // vendors only in enterprise version
                       
                  // fire plugin event
                  $dispatcher = JDispatcher::getInstance();
                  $dispatcher->trigger( 'onRemoveFromCart', array( $item ) );
              	}
              }
              else
              {
              	$row->bind($vals);
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

        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('confirmadd');
        $view->display();
        $this->footer();
        return;
    }
}