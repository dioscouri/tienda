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
        
        $user = JFactory::getUser();
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
        $state['filter_wishlist'] = $app->getUserStateFromRequest( $ns . 'filter_wishlist', 'filter_wishlist', '', '' );
        
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
        $items_model = $this->getModel( 'wishlistitems' );
        $products_model = $this->getModel( 'products' );
        
        // clear unecessary session ids
        $items_model->clearSessionIds();
        
        $user = JFactory::getUser();        
        $items_model->mergeUserItems( $user->id );
        
        $state = $this->_setModelState();
        
        $wishlists = $model->getAll();
        
        foreach (@$state as $key=>$value)
        {
            $items_model->setState( $key, $value );
        }
        $items_model->setState('limit', '10');
        $items_model->setState('order', 'p.product_name');
        $items_model->setState('direction', 'ASC');

        $pagination = null;
        if ($items = $items_model->getList(true))
        {
            $pagination = $items_model->getPagination();
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
        $view->assign( 'wishlists', $wishlists );
      	$view->assign( 'items', $items );
      	$view->assign( 'pagination', $pagination );
      	$view->set('no_items', true);
      	$view->set('no_pagination', true);
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setModel( $items_model );
        $view->setModel( $products_model );
        $view->setLayout('default');

        JRequest::setVar('layout', 'default');
		if( JRequest::getWord( 'email', '' ) == 'true' ) {
			$this->messagetype = 'message';
			$this->message = JText::_('COM_TIENDA_EMAILS_SENT');
			JFactory::getApplication()->enqueueMessage( $this->message, $this->messagetype );
		}
        parent::display( $cachable, $urlparams );
        return;
    }
    
    /**
     * (non-PHPdoc)
     * @see TiendaController::display()
     */
    function view( $cachable = false, $urlparams = '')
    {
        $model  = $this->getModel( $this->get('suffix') );
        $items_model = $this->getModel( 'wishlistitems' );
        $products_model = $this->getModel( 'products' );
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = TiendaHelperBase::getInstance( 'Route' );
    
        // clear unecessary session ids
        $items_model->clearSessionIds();
    
        $user = JFactory::getUser();
        $items_model->mergeUserItems( $user->id );
    
        $this->_setModelState();
        
        $wishlist_id = JRequest::getInt('id');
        $item = $model->getItem( $wishlist_id );
        $redirect = JRoute::_( "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') ), false );		
		if( $item == null ) { // this item does not exist
			$this->messagetype = 'error';
			$this->message = JText::_('COM_TIENDA_WISHLIST_DOEST_EXIST');
			$this->setRedirect( $redirect, $this->message, $this->messagetype );			
			return;
		}
		
        // TODO Is this a public or the user's wishlist?  if no, fail.
        
        $items_model->setState('filter_wishlist', $wishlist_id);
        if ($items = $items_model->getList(true))
        {
    
        }
        
        $checkout_itemid = $router->findItemid( array('view'=>'checkout') );
        if (empty($checkout_itemid)) {
            $checkout_itemid = JRequest::getInt('Itemid');
        }
    
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
        $view->assign( 'row', $item );
        $view->assign( 'items', $items );
        $view->set('no_items', true);
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setModel( $items_model );
        $view->setModel( $products_model );
        $view->setLayout('view');

        JRequest::setVar('layout', 'view');
        
        parent::display( $cachable, $urlparams );
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
		
		$dispatcher = JDispatcher::getInstance();
		$model  = $this->getModel( $this->get('suffix') );
		$items_model = $this->getModel( 'wishlistitems' );
		
        $user = JFactory::getUser();
        $cids = JRequest::getVar('cid', array(0), '', 'array');        
        
        foreach ($cids as $key=>$wishlistitem_id)
        {
            $row = $items_model->getTable();

            // TO edit wishlists, visitors must first login
            $ids = array('user_id'=>$user->id, 'wishlistitem_id'=>$wishlistitem_id);
	        $row->load( $ids );
	        if (!empty($row->wishlistitem_id))
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
	                
	            	$product_attributes = $row->product_attributes;
    	            $product_id = $row->product_id;
    	            
    	            if ($cartitem = $row->addToCart())
                    {
                        $row->delete();
                        
                        $item = new JObject;
                        $item->product_id = $product_id;
                        $item->product_attributes = $product_attributes;
                        $item->vendor_id = '0';
                        $item->wishlistitem_id = $wishlistitem_id;
                        $item->cartitem = $cartitem;
        
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
        $items_model->clearCache();
        
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute(); 
        $redirect = JRoute::_( "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') ), false );
       	$this->setRedirect( $redirect, $msg );
    }
    
    /**
     * 
     * Enter description here ...
     * @return return_type
     */
    public function share()
    {
		$dispatcher = JDispatcher::getInstance();
		$model  = $this->getModel( $this->get('suffix') );
        $user = JFactory::getUser();
        $cids = JRequest::getVar('cid', array(0), '', 'array');

        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute(); 
        $redirect = JRoute::_( "index.php?option=com_tienda&view=wishlists&tmpl=component&Itemid=".$router->findItemid( array('view'=>'wishlists') ), false );
       	
        // get all the items to be shared from wishlist.  if not, redirect back to wishlist with message
        if (empty($cids))
        {
			echo JText::_('COM_TIENDA_PLEASE_SELECT_ITEM_TO_SHARE');
			return;            
        }
        
        $model->setState( 'filter_ids', $cids );
		$model->setState( 'filter_privacy', array( 1, 2 ) );
		$items = $model->getList( true );
		if( !count( $items ) ) {
			$this->messagetype = 'error';
			$this->message = JText::_('COM_TIENDA_CANT_SHARE_PRIVATE_WISHLIST');
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;      
		}
		$model_items = $this->getModel( 'wishlistitems' );
		$wishlist_items = array();
		
		foreach( $items as $wishlist ) {
			$model_items->setState( 'filter_wishlist', $wishlist->wishlist_id );
			$w_items = $model_items->getList( true );
			
			$wishlist_items = array_merge( $wishlist_items, $w_items );
		}
 
        $view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
      	$view->assign( 'items', $wishlist_items );      
        $view->set('hidemenu', true);
        $view->set('_doTask', true);
        $view->setModel( $model, true );
        $view->setLayout('share');
		$view->assign( 'only_redirect', false );
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
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_tienda/tables' );        
        
        // get the email addresses, cutting list off at 10 unique emails
        $addresses = JRequest::getVar('share_emails', '');
        $cids = JRequest::getVar('cid', array(0), '', 'array');
        
        // explode them to an array
        $recipients = array();
        if ($nlsv = explode("\n", $addresses))
        {
            foreach ($nlsv as $email) 
            {
                $email = trim($email);
                if (!empty( $email ) && !in_array($email, $recipients))
                {
                    $recipients[] = $email;
                }
            }
        }
        
        Tienda::load( "TiendaHelperRoute", 'helpers.route' );
        $router = new TiendaHelperRoute(); 
        $redirect = JRoute::_( "index.php?option=com_tienda&view=wishlists&tmpl=component&task=share&cid[]=".implode(',', $cids), false );

        // if no emails, fail
        if (empty($recipients))
        {
            $this->messagetype = 'notice';
            $this->message = JText::_('COM_TIENDA_PLEASE_PROVIDE_EMAIL_RECIPIENTS');
			$this->setRedirect(  $redirect, $this->message, $this->messagetype );
            return;
        }
        
        // create the list of items to be shared, with name & link to each item's detail page
        $model  = $this->getModel( $this->get('suffix') );
        $model->setState( 'filter_ids', $cids );
        $model->setState( 'filter_privacy', array( 1, 2 ) );
		$items = $model->getList( true );
		if( !count( $items ) ) {
			$this->messagetype = 'error';
			$this->message = JText::_('COM_TIENDA_CANT_SHARE_PRIVATE_WISHLIST');
			$this->setRedirect(  JRoute::_( "index.php?option=com_tienda&view=wishlists&Itemid=".$router->findItemid( array('view'=>'wishlists') ), false ), $this->message, $this->messagetype );
			return;      
		}
		$model_items = $this->getModel( 'wishlistitems' );
		$wishlist_items = array();

		foreach( $items as $wishlist ) {
			$model_items->setState( 'filter_wishlist', $wishlist->wishlist_id );
			$w_items = $model_items->getList( true );
	
			$wishlist_items = array_merge( $wishlist_items, $w_items );
		}
        
        $share_items_html = '';
        if (!empty($wishlist_items))
        {
			Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
			$products_model = JModel::getInstance('Products', 'TiendaModel');
 			$share_items_html .= JText::_( 'COM_TIENDA_WISHLIST_EMAIL_LIST_ITEMS' ).'<ul>';
            foreach ($wishlist_items as $item)
            {
                $item->link = "index.php?option=com_tienda&view=products&task=view&id=" . $item->product_id;
				$attributes = explode( ',', $item->product_attributes );
				$tbl = JTable::getInstance('ProductAttributes', 'TiendaTable');
				$tbl_opt = JTable::getInstance( 'ProductAttributeOptions', 'TiendaTable' );
				$product_name = $item->product_name;
				$attr_list = array();
				for( $i = 0, $c = count( $attributes ); $i < $c; $i++ )
				{
					$tbl_opt->load( $attributes[$i] );
					$tbl->load( $tbl_opt->productattribute_id );
					$item->link .= '&attribute_'.$tbl_opt->productattribute_id.'='.$attributes[$i];
					$attr_list []= $tbl->productattribute_name.': '.$tbl_opt->productattributeoption_name;
				}
				if( count( $attr_list ) ) {
					$product_name .= ' ('.implode( '; ', $attr_list ).')';
				}
				$item->itemid = $products_model->getItemid( $item->product_id );            	
				
                if( empty( $item->itemid ) )
                {
                	$item->itemid = $router->findItemid( array('view'=>'products', 'filter_category'=>'1' ) );
                }
                    
                $item->link = substr( JURI::base(), 0, -1 ) . JRoute::_( $item->link. "&Itemid=" . $item->itemid );
                $share_items_html .= '<li>';
                $share_items_html .= '<a href="'.$item->link.'">';
                $share_items_html .= $product_name;
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
        $site_email = '<a href="'.$siteurl.'" target="_blank">'.$sitename.'</a>';
        $share_message = JRequest::getVar('share_message', '');
        $subject = JText::sprintf( "COM_TIENDA_SHARE_WISHLIST_EMAIL_SUBJECT", $sitename );
        $body = JText::sprintf( "COM_TIENDA_SHARE_WISHLIST_EMAIL_BODY", $replytoname, $site_email );
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
		$view   = $this->getView( $this->get('suffix'), JFactory::getDocument()->getType() );
		$view->set('hidemenu', true);
		$view->set('_doTask', true);
		$view->setModel( $model, true );
		$view->assign( 'only_redirect', true );
		$view->setLayout('share');
		$view->display(); 
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
    
    public function addWishlistItemToWishlist()
    {
        $response = new stdClass();
        $response->html = '';
        $response->error = false;
                
        $user = JFactory::getUser();
        $wishlist_model = $this->getModel( 'wishlists' );
        $items_model = $this->getModel( 'wishlistitems' );
        
        $wishlist = $wishlist_model->getTable();
        $row = $items_model->getTable();
        
        $wishlistitem_id = JRequest::getInt('wishlistitem_id');
        $wishlist_id = JRequest::getInt('wishlist_id');
        
        $ids = array('user_id'=>$user->id, 'wishlistitem_id'=>$wishlistitem_id);
        $row->load( $ids );
        
        $wishlist_ids = array('user_id'=>$user->id, 'wishlist_id'=>$wishlist_id);
        $wishlist->load( $wishlist_ids );
        
        if (!empty($row->wishlistitem_id) && !empty($wishlist->wishlist_id)) 
        {
            $row->wishlist_id = $wishlist_id;
            $row->save();
            $response->html = JText::_('COM_TIENDA_WISHLISTITEM_ADDED_TO_WISHLIST');
        } else {
            $response->html = JText::_('COM_TIENDA_INVALID_REQUEST');
            $response->error = true;
        }
        
        echo json_encode($response);
        return;
    }
    
    public function createWishlist()
    {
        $response = new stdClass();
        $response->html = '';
        $response->error = false;
        
        $user = JFactory::getUser();
        $wishlist_model = $this->getModel( 'wishlists' );
        $wishlist_name = JRequest::getVar('wishlist_name');
        
        $wishlist = $wishlist_model->getTable();
        $wishlist->wishlist_name = $wishlist_name;
        $wishlist->user_id = $user->id;
        
        if ($wishlist->save()) {
            $wishlist_model->clearCache();
            $response->html = JText::_('COM_TIENDA_WISHLIST_CREATED');
            $response->wishlist_id = $wishlist->wishlist_id;
        } else {
            $response->html = JText::_('COM_TIENDA_INVALID_REQUEST');
            $response->error = true;
        }

        echo json_encode($response);
        return;        
    }
    
    public function deleteWishlist()
    {
        $response = new stdClass();
        $response->html = '';
        $response->error = false;
    
        $user = JFactory::getUser();
        $wishlist_model = $this->getModel( 'wishlists' );
    
        $wishlist = $wishlist_model->getTable();
        $wishlist_id = JRequest::getInt('wishlist_id');
        $wishlist_ids = array('user_id'=>$user->id, 'wishlist_id'=>$wishlist_id);
        $wishlist->load( $wishlist_ids );
    
        if (!empty($wishlist->wishlist_id))
        {
            if ($wishlist->delete()) {
                $response->html = JText::_('COM_TIENDA_WISHLIST_DELETED');
            } else {
                $response->html = JText::_('COM_TIENDA_DELETE_FAILED');
                $response->error = true;                
            }
            
        } else {
            $response->html = JText::_('COM_TIENDA_INVALID_REQUEST');
            $response->error = true;
        }
    
        echo json_encode($response);
        return;
    }

    public function renameWishlist()
    {
        $response = new stdClass();
        $response->html = '';
        $response->error = false;
    
        $user = JFactory::getUser();
        $wishlist_model = $this->getModel( 'wishlists' );
        $wishlist_name = JRequest::getVar('wishlist_name');
    
        $wishlist = $wishlist_model->getTable();
        $wishlist_id = JRequest::getInt('wishlist_id');
        $wishlist_ids = array('user_id'=>$user->id, 'wishlist_id'=>$wishlist_id);
        $wishlist->load( $wishlist_ids );
        
        if (!empty($wishlist->wishlist_id))
        {
            $wishlist->wishlist_name = $wishlist_name;
            if ($wishlist->save()) {
                $response->html = JText::_('COM_TIENDA_WISHLIST_UPDATED');
                $response->wishlist_name = $wishlist_name;
            } else {
                $response->html = JText::_('COM_TIENDA_UPDATE_FAILED');
                $response->error = true;
            }
        
        } else {
            $response->html = JText::_('COM_TIENDA_INVALID_REQUEST');
            $response->error = true;
        }        
    
        echo json_encode($response);
        return;
    }

    public function deleteWishlistItem()
    {
        $response = new stdClass();
        $response->html = '';
        $response->error = false;
    
        $user = JFactory::getUser();
        $model = $this->getModel( 'wishlistitems' );
    
        $table = $model->getTable();
        $id = JRequest::getInt('wishlistitem_id');
        $keys = array('user_id'=>$user->id, 'wishlistitem_id'=>$id);
        $table->load( $keys );
    
        if (!empty($table->wishlistitem_id))
        {
            if ($table->delete()) {
                $response->html = JText::_('COM_TIENDA_WISHLISTITEM_DELETED');
            } else {
                $response->html = JText::_('COM_TIENDA_DELETE_FAILED');
                $response->error = true;
            }
    
        } else {
            $response->html = JText::_('COM_TIENDA_INVALID_REQUEST');
            $response->error = true;
        }
    
        echo json_encode($response);
        return;
    }

    public function privatizeWishlist()
    {
        $response = new stdClass();
        $response->html = '';
        $response->error = false;
    
        $user = JFactory::getUser();
        $wishlist_model = $this->getModel( 'wishlists' );
        $privacy = JRequest::getInt('privacy');
    
        $wishlist = $wishlist_model->getTable();
        $wishlist_id = JRequest::getInt('wishlist_id');
        $wishlist_ids = array('user_id'=>$user->id, 'wishlist_id'=>$wishlist_id);
        $wishlist->load( $wishlist_ids );
    
        if (!empty($wishlist->wishlist_id))
        {
            $wishlist->privacy = $privacy;
            if ($wishlist->save()) {
                $response->html = JText::_('COM_TIENDA_WISHLIST_UPDATED');
            } else {
                $response->html = JText::_('COM_TIENDA_UPDATE_FAILED');
                $response->error = true;
            }
    
        } else {
            $response->html = JText::_('COM_TIENDA_INVALID_REQUEST');
            $response->error = true;
        }
    
        echo json_encode($response);
        return;
    }    
}