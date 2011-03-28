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

class TiendaControllerPOS extends TiendaController
{
    /**
     * Default redirect URL
     */
    var $redirect = 'index.php?option=com_tienda&view=orders';
    var $validation_url = 'index.php?option=com_tienda&view=pos&task=validate&format=raw';
    
    function display( $cachable=false )
    {
        $step = JRequest::getVar('nextstep', 'step1');
        if (empty($step))
        {
            $step = 'step1';
        }
        
        JModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
        $elementUserModel = JModel::getInstance( 'ElementUser', 'TiendaModel' );

        $session = JFactory::getSession();
        
        $view = $this->getView( 'pos', 'html' );
        $view->assign('session', $session);
        $view->assign('step', $step);
        $view->assign('validation_url', $this->validation_url);
        $view->setModel( $elementUserModel );
        
        $method_name = 'do' . $step;
        if (method_exists($this, $method_name ))
        {
            $this->$method_name();
        }
        
        parent::display();
    }
    
    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
    function saveStep1()
    {
        $post = JRequest::get( 'post' );
        
        // store the values in the session
        $session = JFactory::getSession();
        
        $session->set( 'user_type', $post['user_type'], 'tienda_pos' );
        $session->set( 'user_id', $post['user_id'], 'tienda_pos' );
        $session->set( 'new_email', $post['new_email'], 'tienda_pos' );
        $session->set( 'new_name', $post['new_name'], 'tienda_pos' );
        $session->set( 'new_username_create', !empty($post['new_username_create']), 'tienda_pos' );
        $session->set( 'new_username', $post['new_username'], 'tienda_pos' );
        $session->set( 'anon_emails', !empty($post['anon_emails']), 'tienda_pos' );
        $session->set( 'anon_email', $post['anon_email'], 'tienda_pos' );
        
        $this->setRedirect( "index.php?option=com_tienda&view=pos&nextstep=step2" );
    }

    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
    function doStep2()
    {
        $session = JFactory::getSession();
        switch ($session->get( 'user_type', '', 'tienda_pos' ))
        {
            case "existing":
                $user = JFactory::getUser( $session->get( 'user_id', '', 'tienda_pos' ) );
                $step1_inactive = JText::_( "Existing user" ) . ": " . $user->name . " - " . $user->email . " [" . $user->id . "]";
                break;
            case "new":
                $step1_inactive = JText::_( "New User" ) . ": " . $session->get( 'new_name', '', 'tienda_pos' ) . " - " . $session->get( 'new_email', '', 'tienda_pos' );
                break;
            case "anonymous":
                $step1_inactive = JText::_( "Anonymous user" );
                break;
            default:
                $step1_inactive = JText::_( "Name and email of user" );
                break;
        }
        
        $view = $this->getView( 'pos', 'html' );
        $view->assign('step1_inactive', $step1_inactive);
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/site/TiendaController::validate()
     */
    function validate()
    {
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $helper = new TiendaHelperBase();
            
        $response = array();
        $response['msg'] = '';
        $response['error'] = '';
            
        // get elements from post
            $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );

            // validate it using table's ->check() method
            if (empty($elements))
            {
                // if it fails check, return message
                $response['error'] = '1';
                $response['msg'] = $helper->generateMessage(JText::_("Could not process form"));
                echo ( json_encode( $response ) );
                return;
            }
            
        // convert elements to array that can be binded             
            $values = $helper->elementsToArray( $elements );
            
        // validate it based on the step
            switch ( $values['step'] )
            {
                case "step1":
                    $response = $this->validateStep1( $values );
                    break;
                case "step2":
                    break;
                case "step3":
                    break;
                case "step4":
                    break;
            }
            
        echo ( json_encode( $response ) );
        return;
    }
    
    /**
     * 
     * Enter description here ...
     * @param $values
     * @return unknown_type
     */
    function validateStep1( $values )
    {
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $helper = new TiendaHelperBase();
        
        $msg = array();
        $response = array();
        $response['msg'] = '';
        $response['error'] = '';
        
        switch ( $values['_checked']['user_type'] )
        {
            case "existing":
                if (empty($values['user_id']))
                {
                    $response['error'] = '1';
                    $msg[] = JText::_( "Please Select a User" );
                    
                }
                break;
            case "new":
                if (empty($values['new_email']) || $values['new_email'] == JText::_( 'Email' ) )
                {
                    $response['error'] = '1';
                    $msg[] = JText::_( "Please provide an email" );
                }

                if (empty($values['new_name']) || $values['new_name'] == JText::_( 'Full Name' ) )
                {
                    $response['error'] = '1';
                    $msg[] = JText::_( "Please provide a name" );
                }
                
                if (empty($values['_checked']['new_username_create']) && (empty($values['new_username']) || $values['new_username'] == JText::_( 'Username' )) )
                {
                    $response['error'] = '1';
                    $msg[] = JText::_( "Please provide a username" );
                }
                
                $userhelper = $helper->getInstance( 'User' );
                
                // Is this email already used?
                if ($userhelper->emailExists( $values['new_email'] ))
                {
                    $response['error'] = '1';
                    $msg[] = JText::_( "This email already exists" );
                }
                
                // Is this username already used?
                if (empty($values['_checked']['new_username_create']) && $userhelper->usernameExists( $values['new_username'] ))
                {
                    $response['error'] = '1';
                    $msg[] = JText::_( "This username already exists" );
                }
                break;
            case "anonymous":
                if (!empty($values['_checked']['anon_emails']) && (empty($values['anon_email']) || $values['anon_email'] == JText::_( "Email" ) ) )
                {
                    $response['error'] = '1';
                    $msg[] = JText::_( "Please provide an email" );
                }
                break;
        }
        
        $response['msg'] = $helper->generateMessage( "<li>" . implode( "</li><li>", $msg ) . "</li>", false );
        return $response;
    }
    
    /**
     * 
     * @return unknown_type
     */
    function addProducts()
    {
        $this->set('suffix', 'products');
        $state = parent::_setModelState();
        $app = JFactory::getApplication();
        $model = $this->getModel( $this->get('suffix') );
        $ns = $this->getNamespace();

        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );
        }
        
        $view   = $this->getView( 'pos', 'html' );
        $view->setModel( $model, true );
        $view->assign( 'state', $model->getState() );
        $view->assign( 'items', $model->getList() );
        $view->setLayout( 'addproduct' );
        $view->display();
    }

    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
    function viewProduct()
    {
        $model = $this->getModel( 'Products' );
        $model->setId( $model->getId() );
        $row = $model->getItem();
        
        $view   = $this->getView( 'pos', 'html' );
        $view->setModel( $model, true );
        $view->assign( 'product', $row );
        $view->setLayout( 'viewproduct' );
        $view->display();
    }
    
    /**
     * 
     * Enter description here ...
     * @param $product_id
     * @param $values
     * @return unknown_type
     */
    function getAddToCart( $product_id, $values = array( ) )
    {
        $html = '';
        JModel::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_tienda/models' );
        $model = JModel::getInstance( 'Products', 'TiendaModel' );
        $model->setId( $product_id );

        // TODO Make user group a factor
        //Tienda::load( 'TiendaHelperUser', 'helpers.user' );
        //$user_id = JFactory::getUser( )->id;
        //$filter_group = TiendaHelperUser::getUserGroup( $user_id, $product_id );
        //$model->setState( 'filter_group', $filter_group );
        
        $row = $model->getItem( false );
        
        $view   = $this->getView( 'pos', 'html' );
        $view->setModel( $model, true );
        $view->assign( 'product', $row );
        $view->setLayout( 'viewproduct' );
        
        $dispatcher = &JDispatcher::getInstance( );
        
        ob_start( );
        $dispatcher->trigger( 'onDisplayProductAttributeOptions', array(
                    $row->product_id
                ) );
        $view->assign( 'onDisplayProductAttributeOptions', ob_get_contents( ) );
        ob_end_clean( );
        
        ob_start( );
        $view->display( );
        $html = ob_get_contents( );
        ob_end_clean( );
        
        return $html;
    }
    
    /**
     * Used whenever an attribute selection is changed,
     * to update the price and/or attribute selectlists
     * 
     * @return unknown_type
     */
    function updateAddToCart( )
    {
        $response = array( );
        $response['msg'] = '';
        $response['error'] = '';
        
        // get elements from post
        $elements = json_decode( preg_replace( '/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
        
        // convert elements to array that can be binded
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $values = TiendaHelperBase::elementsToArray( $elements );
        
        if ( empty( $values['product_id'] ) )
        {
            $values['product_id'] = JRequest::getInt( 'product_id', 0 );
        }
        
        // now get the summary
        $this->display_cartbutton = true;
        $html = $this->getAddToCart( $values['product_id'], $values );
        
        $response['msg'] = $html;
        // encode and echo (need to echo to send back to browser)
        echo json_encode( $response );
        return;
    }
    
    function addToCart()
    {
        $post = JRequest::get('post');
        echo Tienda::dump($post);
        
        $values = JRequest::get( 'post' );
        $files = JRequest::get( 'files' );
        
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
        
        $product_id = JRequest::getInt( 'product_id' );
        $product_qty = JRequest::getInt( 'product_qty' );
        

        $session =& JFactory::getSession();
        $user =& JFactory::getUser();
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $table = JTable::getInstance( 'Carts', 'TiendaTable' );

        // first, determine if this product+attribute+vendor(+additonal_keys) exists in the cart
        // if so, update quantity
        // otherwise, add as new item
        // return the cart object with cart_id (to be used by plugins, etc)
        
        $keynames = array();
        $item->user_id = (empty($item->user_id)) ? $user->id : $item->user_id;
        $keynames['user_id'] = $item->user_id;
        if (empty($item->user_id))
        {
            $keynames['session_id'] = $session->getId();
        }
        $keynames['product_id'] = $item->product_id;
        $keynames['product_attributes'] = $item->product_attributes;

        // fire plugin event: onGetAdditionalCartKeyValues
        // this event allows plugins to extend the multiple-column primary key of the carts table
        $additionalKeyValues = TiendaHelperCarts::getAdditionalKeyValues( $item, null, null );
        if (!empty($additionalKeyValues))
        {
            $keynames = array_merge($keynames, $additionalKeyValues);
        }

        if ($table->load($keynames))
        {
            $table->product_qty = $table->product_qty + $item->product_qty;
        }
            else
        {
            foreach($item as $key=>$value)
            {
                if(property_exists($table, $key))
                {
                    $table->set($key, $value);
                }
            }
        }
        
        // Now for Eavs!!
        $eavs = TiendaHelperEav::getAttributes('products', $item->product_id);
        
        if(count($eavs))
        {
            foreach($eavs as $eav)
            {
                // Search for user edtable fields & user submitted value
                if($eav->editable_by == 2 && array_key_exists($eav->eavattribute_alias, $item))
                {
                    $key = $eav->eavattribute_alias;
                    $table->set($key, $item->$key);
                }
            }
        }        

        $date = JFactory::getDate();
        $table->last_updated = $date->toMysql();
        $table->session_id = $session->getId();
        
        if (!$table->save())
        {
            JError::raiseNotice('updateCart', $table->getError());
        }
            else
        {
            TiendaHelperCarts::fixQuantities();
        }

        return $table;
        
    }

}