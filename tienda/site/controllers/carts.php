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

    /**
     * Adds an item to a User's shopping cart
     * whether in the session or the db 
     * 
     */
    function addToCart()
    {
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
        
        $suffix = strtolower(TiendaHelperCarts::getSuffix());
        $model = $this->getModel($suffix);
        
        switch ($suffix) 
        {
	        case 'sessioncarts':
	        case 'carts':
	        default:
	            $item = new JObject;
	            $item->user_id     = JFactory::getUser()->id;
	            $item->product_id  = $product_id;
	            $item->product_qty = $product_qty;
	            $item->product_attributes = $attributes_csv;
	            $item->vendor_id   = '0'; // vendors only in enterprise version
	            $cart = array();
	            $cart[] = $item;
	            TiendaHelperCarts::updateCart($cart);
	            break;
        }
        
        // fire plugin event
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger( 'onAddToCart', array( $item ) );

        // TODO Do we want to do this?  Or use a back-order system?
        // TiendaHelperCarts::fixQuantities();
        
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
                $vals['product_qty'] = $value;
                $vals['product_attributes'] = $product_attributes[$key];
                $row = $model->getTable();
                $row->bind($vals);
                $row->save();
            }
        }

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