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

        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }
    
    function display()
    {
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
        
        $model  = $this->getModel( $this->get('suffix') );
        $this->_setModelState();

        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('default');
        $view->assign( 'return', $redirect );
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

        $remove = JRequest::getVar('remove');
        if ($remove) 
        {
            foreach ($cids as $key=>$product_id)
            {
                $row = $model->getTable();
                $ids = array('user_id'=>$user->id, 'product_id'=>$product_id, 'product_attributes'=>$product_attributes[$key] );
                if (empty($user->id))
                {
                    $ids['session_id'] = $session->getId();
                }
                
                if ($return = $row->delete($ids))
                {
	                $item = new JObject;
	                $item->product_id = $product_id;
	                $item->product_attributes = $product_attributes[$key];
	                $item->vendor_id = '0'; // vendors only in enterprise version
	               
	                // fire plugin event
	                $dispatcher = JDispatcher::getInstance();
	                $dispatcher->trigger( 'onRemoveFromCart', array( $item ) );
                }
			}
        } 
            else 
        {
            $vals = array();            
            foreach($quantities as $key=>$value) 
            {
            	$keynames = explode('.', $key);
            	$product_id = $keynames[0];
                $vals['user_id'] = $user->id;
                $vals['session_id'] = $session->getId();
                $vals['product_id'] = $product_id;
               
                // using a helper file,To determine the product's information related to inventory     
                $availableQuantity = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getAvailableQuantity ( $product_id, $product_attributes[$key] );	
                if ( $availableQuantity->product_check_inventory && $value > $availableQuantity->quantity ) 
                {
                	JFactory::getApplication()->enqueueMessage( JText::sprintf( 'NOT_AVAILABLE_QUANTITY', $availableQuantity->product_name, $value ));
                    continue;
                }
                
                if ($value > 1)
                {
                    $product = JTable::getInstance( 'Products', 'TiendaTable' );
                    $product->load( array( 'product_id'=>$product_id) );
                    if ($product->product_recurs)
                    {
                        $value = 1;
                    }
                }

                $row = $model->getTable();
                $vals['product_attributes'] = $product_attributes[$key];
                $vals['product_qty'] = $value;
                if (empty($vals['product_qty']))
                {
                    // remove it
                    if ($return = $row->delete($vals))
                    {
                        $item = new JObject;
                        $item->product_id = $product_id;
                        $item->product_attributes = $product_attributes[$key];
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
        
        TiendaHelperCarts::fixQuantities();       
        $this->setRedirect( 'index.php?option=com_tienda&view=carts' );
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