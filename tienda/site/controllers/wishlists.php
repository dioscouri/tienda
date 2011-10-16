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

Tienda::load( 'TiendaHelperWishlists', 'helpers.wishlists' );
Tienda::load( 'TiendaHelperBase', 'helpers._base' );

class TiendaControllerWishlists extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
        $this->set('suffix', 'wishlists');
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
             // redirect to login
            Tienda::load( "TiendaHelperRoute", 'helpers.route' );
            $router = new TiendaHelperRoute(); 
            $url = JRoute::_( "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') ), false );
            $redirect = "index.php?option=com_user&view=login&return=".base64_encode( $url );
            $redirect = JRoute::_( $redirect, false );
            JFactory::getApplication()->redirect( $redirect );
            return; 
        }
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }  

    /**
     * (non-PHPdoc)
     * @see TiendaController::display()
     */
    function display()
    {
        $model  = $this->getModel( $this->get('suffix') );
        $this->_setModelState();
        if ($items = $model->getList())
        {
            foreach ($items as $item)
            {
                $row = $model->getTable();
                $row->bind( $item );
                $item->available = $row->isAvailable(); 
            }
        }
        
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = TiendaHelperBase::getInstance( 'Route' );
        $checkout_itemid = $router->findItemid( array('view'=>'checkout') );
        if (empty($checkout_itemid)) { $checkout_itemid = JRequest::getInt('Itemid'); }

        if ($return = JRequest::getVar('return', '', 'method', 'base64')) 
        {
            $return = base64_decode($return);
            if (!JURI::isInternal($return)) 
            {
                $return = '';
            }
        }
     
        $redirect = $return ? $return : JRoute::_( "index.php?option=com_tienda&view=products" );
        
        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
        $view->assign( 'return', $redirect );
        $view->assign( 'checkout_itemid', $checkout_itemid );
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
     * 
     * Enter description here ...
     * @return return_type
     */
    function update()
    {
	    // verify form submitted by user
		JRequest::checkToken( ) or jexit( 'Invalid Token' );
		
		$dispatcher = JDispatcher::getInstance();
		$model  = $this->getModel( $this->get('suffix') );
        $user =& JFactory::getUser();
        $cids = JRequest::getVar('cid', array(0), '', 'array');        
        
        foreach ($cids as $key=>$wishlist_id)
        {
            $row = $model->getTable();

            $ids = array('user_id'=>$user->id, 'wishlist_id'=>$wishlist_id);
	        $row->load( $ids );
	        if (!empty($row->wishlist_id))
	        {
	            $remove = JRequest::getVar('remove');
	            if ($remove)
	            {
	                $msg = JText::_( "COM_TIENDA_WISHLIST_UPDATED" );
	                
	            	$product_attributes = $row->product_attributes;
    	            $product_id = $row->product_id;
    	            
    	            if ($return = $row->delete())
                    {
                        $item = new JObject;
                        $item->product_id = $product_id;
                        $item->product_attributes = $product_attributes;
                        $item->vendor_id = '0';
                        $item->wishlist_id = $wishlist_id;
        
                        $dispatcher->trigger( 'onRemoveFromWishlist', array( $item ) );
                    }	                
	            }
	            
	            $addtocart = JRequest::getVar('addtocart');
	        	if ($addtocart)
	            {
	                $msg = JText::_( "COM_TIENDA_WISHLIST_ITEMS_ADDED_TO_CART" );
	                
	            	$product_attributes = $row->product_attributes;
    	            $product_id = $row->product_id;
    	            
    	            if ($cartitem = $row->addtocart())
                    {
                        $row->delete();
                        
                        $item = new JObject;
                        $item->product_id = $product_id;
                        $item->product_attributes = $product_attributes;
                        $item->vendor_id = '0';
                        $item->wishlist_id = $wishlist_id;
        
                        $dispatcher->trigger( 'onAddToCartFromWishlist', array( $item ) );
                    }
                        else
                    {
                        $msg = JText::_( "COM_TIENDA_NOT_ALL_WISHLIST_ITEMS_ADDED_TO_CART" );
                    }
	            }
	        }
        }
        
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute(); 
        $redirect = JRoute::_( "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') ), false );
       	$this->setRedirect( $redirect, $msg );
    }
}