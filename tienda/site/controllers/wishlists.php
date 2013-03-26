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

        $session = JFactory::getSession();
        $user = JFactory::getUser();
        
        $state['filter_user'] = $user->id;
        if (empty($user->id))
        {
             // redirect to login
            Tienda::load( "TiendaHelperRoute", 'helpers.route' );
            $router = new TiendaHelperRoute(); 
            $url = JRoute::_( "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') ), false );
            Tienda::load( "TiendaHelperUser", 'helpers.user' );
            $redirect = JRoute::_( TiendaHelperUser::getUserLoginUrl( $url ), false );
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
    function display( $cachable = false, $urlparams = '')
    {
        $model  = $this->getModel( $this->get('suffix') );
        $id = $model->getId();
        $user = JFactory::getUser();        
      
        
        $this->_setModelState();
        $items = $model->getList();
        
        foreach($items as $wishlist) {
            if($wishlist->wishlist_id == $id) {
               $wishlist->active = 1; 
               $active = $wishlist;
            }
            if(empty($id) && $wishlist->default) {
               $wishlist->active = 1; 
               $active = $wishlist;
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
        $view->assign( 'active', $active );       
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('default');
        $view->display();        
        $this->footer();
        return;
    }
    
    
    function save() {
        $model  = $this->getModel( $this->get('suffix') );
        $row = $model->getTable();

        $row->load( $model->getId() );
        $row->bind( JRequest::get('POST') );
        $row->user_id = JFactory::getUser()->id;
        if ( $row->save() ) {  
        
            $redirect = JRoute::_( "index.php?option=com_tienda&view=wishlists");
            } else {
                $redirect = JRoute::_( "index.php?option=com_tienda&view=wishlists");
        }

        $this->setRedirect( $redirect, $msg );
        return;
    }


    function edit() {
        $model  = $this->getModel( $this->get('suffix') );
        $id = $model->getId();
        $wishlist = $model->getItem($id);

        
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
        $view->assign( 'wishlist', $wishlist );      
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('form');
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
		$share = JRequest::getVar('share');
        
		if ($share) {
		    $this->share();
		    return;
		}

		JRequest::checkToken( ) or jexit( 'Invalid Token' );
		
		$dispatcher = JDispatcher::getInstance();
		$model  = $this->getModel( $this->get('suffix') );
        $user = JFactory::getUser();
        $cids = JRequest::getVar('cid', array(0), '', 'array');        
        $id = JRequest::getVar('id');     
       

       
        foreach ($cids as $key=>$wishlist_id)
        {   

            DSCTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );
            $row = DSCTable::getInstance('WishlistsItems', 'TiendaTable'); 

            $ids = array('user_id'=>$user->id, 'wishlist_item_id'=>$wishlist_id);
           
	        $row->load( $ids );
        
	        if (!empty($row->wishlist_id))
	        {
	            $remove = JRequest::getVar('remove');
	            if ($remove)
	            {
	                $msg = JText::_('COM_TIENDA_WISHLIST_UPDATED');
	                
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
	                $msg = JText::_('COM_TIENDA_WISHLIST_ITEMS_ADDED_TO_CART');
	                
                   FB::log('addingtocart');

	            	$product_attributes = $row->product_attributes;
    	            $product_id = $row->product_id;
    	            
    	            if ($cartitem = $row->addtocart())
                    {
                         var_dump($cartitem);
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
                        $msg = JText::_('COM_TIENDA_NOT_ALL_WISHLIST_ITEMS_ADDED_TO_CART');
                    }
	            }
	        }
        }
       
        $model->clearCache();
        
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute(); 
        $redirect = JRoute::_( "index.php?option=com_tienda&view=wishlists&task=view&id=".$id."&Itemid=".$router->findItemid( array('view'=>'wishlists') ), false );
       	$this->setRedirect( $redirect, $msg );
    }
    
    /**
     * 
     * Enter description here ...
     * @return return_type
     */
    public function share()
    {
		JRequest::checkToken( ) or jexit( 'Invalid Token' );
		
		$dispatcher = JDispatcher::getInstance();
		$model  = $this->getModel( $this->get('suffix') );
        $user = JFactory::getUser();
        $cids = JRequest::getVar('cid', array(0), '', 'array');

        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute(); 
        $redirect = JRoute::_( "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') ), false );
       	
        // get all the items to be shared from wishlist.  if not, redirect back to wishlist with message
        if (empty($cids))
        {
            $this->messagetype = 'notice';
            $this->message = JText::_('COM_TIENDA_PLEASE_SELECT_ITEM_TO_SHARE');
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;            
        }
        
        $model->setState( 'filter_ids', $cids );
        $items = $model->getList();

        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
      	$view->assign( 'items', $items );      
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('share');
        $view->display();        
        $this->footer();
        return;
    }
    
    /**
     * 
     * Enter description here ...
     * @return return_type
     */
    public function shareitems()
    {
        JRequest::checkToken( ) or jexit( 'Invalid Token' );
        
        // get the email addresses, cutting list off at 10 unique emails
        $addresses = JRequest::getVar('share_emails', '');
        
        // explode them to an array
        if ($nlsv = explode("\n", $addresses))
        {
            foreach ($nlsv as $email) 
            {
                $email = trim($email);
                if (!in_array($email, $recipients))
                {
                    $recipients[] = $email;
                }
            }
        }
        
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute(); 
        $redirect = JRoute::_( "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') ), false );

        // if no emails, fail
        if (empty($recipients))
        {
            $this->messagetype = 'notice';
            $this->message = JText::_('COM_TIENDA_PLEASE_PROVIDE_EMAIL_RECIPIENTS');
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;
        }
        
        // create the list of items to be shared, with name & link to each item's detail page
        $cids = JRequest::getVar('cid', array(0), '', 'array');
        $model  = $this->getModel( $this->get('suffix') );
        $model->setState( 'filter_ids', $cids );
        $items = $model->getList();
        
        $share_items_html = '';
        if (!empty($items))
        {
            $share_items_html .= '<ul>';
            foreach ($items as $item)
            {
                $item->link = "index.php?option=com_tienda&view=products&task=view&id=" . $item->product_id;
            	$item->filter_category = '';
    	        $categories = Tienda::getClass( 'TiendaHelperProduct', 'helpers.product' )->getCategories( $item->product_id );
                if (!empty($categories))
                {
                    $item->link .= "&filter_category=".$categories[0];
                    $item->filter_category = $categories[0];
                }
                $item->itemid = $router->category( $item->filter_category, true );
                if( empty( $item->itemid ) )
                {
                	$item->itemid = $router->findItemid( array('view'=>'products', 'filter_category'=>'1' ) );
                }
                    
                $item->link = substr( JURI::base(), 0, -1 ) . JRoute::_( $item->link. "&Itemid=" . $item->itemid );
                $share_items_html .= '<li>';
                $share_items_html .= '<a href="'.$item->link.'">';
                $share_items_html .= $item->product_name;
                $share_items_html .= '</a>';
                $share_items_html .= '</li>';
            }
            $share_items_html .= '</ul>';
        }
        
        // set the mailfrom & reply-to
        $mainframe = JFactory::getApplication();
        $sitename = $mainframe->getCfg('sitename');
        $siteurl = JURI::base();
        
        $user = JFactory::getUser();
        $replyto = $user->email; 
        $replytoname = $user->name;
        $mailfrom = $replyto;
        $fromname = $replytoname;
        
        // create body and subject of email
        $share_message = JRequest::getVar('share_message', '');
        $subject = JText::sprintf( "COM_TIENDA_SHARE_WISHLIST_EMAIL_SUBJECT", $sitename );
        $body = JText::sprintf( "COM_TIENDA_SHARE_WISHLIST_EMAIL_BODY", $replytoname, $siteurl );
        $body .= JText::sprintf( "COM_TIENDA_MESSAGE_FROM_SENDER", $share_message );
        $body .= $share_items_html; 
        
        $this->use_html = true;
        
        // foreach email address, send the email
        $max_recipients = '10';
        $count = (count($recipients) > $max_recipients) ? $max_recipients : count($recipients);
        for ($i=0; $i < $count; $i++) 
        {
            $recipient = $recipients[$i];
            if (empty($done[$recipient])) 
            {
                $done[$recipient] = $recipient;
                if ( $send = $this->_sendMail( $mailfrom, $fromname, $recipient, $subject, $body, null, null, null, null, null, $replyto, $replytoname ) ) 
                {
                    $success = true;
                    $done[$recipient] = $recipient;
                }
            }
        }
        
        // redirect to wishlist with message 
        $this->messagetype = 'message';
        $this->message = JText::_('COM_TIENDA_EMAILS_SENT');
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
        return;
    }
    
    private function _sendMail( $from, $fromname, $recipient, $subject, $body, $actions=NULL, $mode=NULL, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL ) 
    {
        $success = false;
        $mailer = JFactory::getMailer();
        $mailer->addRecipient( $recipient );
        $mailer->setSubject( $subject );
        
        // check user mail format type, default html
        $body = htmlspecialchars_decode( $body );
        if (!empty($this->use_html)) {
            $body = nl2br( $body );
            $mailer->IsHTML($this->use_html);
        }        
        $mailer->setBody( $body );

        if (!empty($replyto)) {
            $replytoname = empty($replytoname) ? $replyto : $replytoname;
            $reply = array( $replyto, $replytoname );
            $mailer->addReplyTo($reply);
        }        
        
        $sender = array( $from, $fromname );
        $mailer->setSender($sender);
        $sent = $mailer->send();
        if ($sent == '1') 
        {
            $success = true;
        }
        
        return $success;
    }

    public function addToWishlist()
    {
        // verify form submitted by user
        JRequest::checkToken( ) or jexit( 'Invalid Token' );

        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute();

        $product_id = JRequest::getInt( 'product_id' );
        $filter_category = JRequest::getInt( 'filter_category' );
        if ( !$itemid = $router->product( $product_id, $filter_category, true ) )
        {
            $itemid = $router->category( 1, true );
            if( !$itemid )
                $itemid = JRequest::getInt( 'Itemid', 0 );
        }

        // set the default redirect URL
        $redirect = "index.php?option=com_tienda&view=products&task=view&id=$product_id&filter_category=$filter_category&Itemid=" . $itemid;
        $redirect = JRoute::_( $redirect, false );

        JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/tables' );
        $product = JTable::getInstance( 'Products', 'TiendaTable' );
        $product->load( $product_id, true, false );

        if (empty($product->product_id))
        {
            // not a valid product, so return to product detail page with invalid-product message
            $this->messagetype = 'notice';
            $this->message = JText::_('COM_TIENDA_INVALID_PRODUCT');
            $this->setRedirect( $redirect, $this->message, $this->messagetype );
            return;
        }

        $values = JRequest::get('post');

        $attributes = array( );
        foreach ( $values as $key => $value )
        {
            if ( substr( $key, 0, 10 ) == 'attribute_' )
            {
                $attributes[] = $value;
            }
        }
        sort( $attributes );
        $attributes_csv = implode( ',', $attributes );

        $user_id = JFactory::getUser()->id;
        if (empty($user_id))
        {
            // if not logged in, add item to session wishlist, then redirect to login/registration page, and upon login (in user plugin) add item from session to wishlist
            $session = JFactory::getSession();
            $session_id = $session->getId();
            $session->set( 'old_sessionid', $session_id );
                
            $wishlist = JTable::getInstance( 'Wishlists', 'TiendaTable' );
            $wishlist->load(array('session_id'=>$session_id, 'product_id'=>$product->product_id, 'product_attributes'=>$attributes_csv));
            $wishlist->session_id = $session_id;
            $wishlist->product_id = $product->product_id;
            $wishlist->product_attributes = $attributes_csv;
            $wishlist->last_updated = JFactory::getDate()->toMySQL();
            $wishlist->store();

            JFactory::getApplication()->enqueueMessage( JText::_('COM_TIENDA_LOGIN_TO_ADD_ITEM_TO_WISHLIST') );

            Tienda::load( "TiendaHelperRoute", 'helpers.route' );
            $router = new TiendaHelperRoute();
            $url = $redirect; // set above
            $option_users_component = 'com_users';
            if(!version_compare(JVERSION,'1.6.0','ge')) {
                // Joomla! 1.5 code here
                $option_users_component = 'com_user';
            }
            $redirect = "index.php?option=".$option_users_component."&view=login&return=".base64_encode( $url );
            $redirect = JRoute::_( $redirect, false );
            JFactory::getApplication()->redirect( $redirect );
            return;
        }


        // add to wishlist
        $wishlist = JTable::getInstance( 'Wishlists', 'TiendaTable' );
        // load from db in case the item is in the wishlist already and should be updated
        $wishlist->load( array( 'user_id'=>$user_id, 'product_id'=>$product->product_id, 'product_attributes'=>$attributes_csv ) );
        // set the values
        $wishlist->user_id = $user_id;
        $wishlist->product_id = $product->product_id;
        $wishlist->product_attributes = $attributes_csv;
        $wishlist->last_updated = JFactory::getDate()->toMySQL();

        if (!$wishlist->save())
        {
            $this->messagetype = 'notice';
            $this->message = JText::_('COM_TIENDA_COULD_NOT_ADD_TO_WISHLIST');
        }
        else
        {
            $url = "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') );
            $this->messagetype = 'message';
            $this->message = JText::sprintf( JText::_('COM_TIENDA_ADDED_TO_WISHLIST'), $url );
        }

        // redirect back to product detail page, with message saying "item added, click here to view wishlist"
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
        return;
    }



    



    
}