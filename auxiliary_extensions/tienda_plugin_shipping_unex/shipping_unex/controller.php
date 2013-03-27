<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.library.plugins.shippingcontroller', JPATH_ADMINISTRATOR.'/components' );

class TiendaControllerShippingUnex extends TiendaControllerShippingPlugin 
{

    var $_element   = 'shipping_UNEX';
		
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
	}
	
    function edit()
    {
		JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR.'/components' );
		TiendaToolBarHelper::custom( 'save', 'save', 'save', 'COM_TIENDA_SAVE', false, 'shippingTask' );
		TiendaToolBarHelper::custom( 'cancel', 'cancel', 'cancel', 'COM_TIENDA_CLOSE', false, 'shippingTask' );
    	
    	$id = JRequest::getInt('id', '0');
    	$sid = TiendaShippingPlugin::getShippingId();
    	$this->includeCustomModel('UnexServices'); 
    	$this->includeCustomTables();

    	$model = DSCModel::getInstance('UnexServices', 'TiendaModel');
    	if($sid)
    	{
	        $model->setId((int)$sid);
	        
	        $item = $model->getItem();
    	}
        else
        {
        	$item = new JObject();
        }
        
        // Form
        $form = array();
        $form['action'] = $this->baseLink();
        $form['shippingTask'] = 'save';
		$view = $this->getView( 'shipping_unex', 'html' ); 
		$view->hidemenu = true;
		$view->hidestats = true;
		$view->setModel( $model, true );
		$view->assign('item', $item);
		$view->assign('form2', $form);
		$view->setLayout('edit');
		$view->display();
        
    }
    
	function save(){
		
		$values = JRequest::get('post');
		
    	$this->includeCustomTables(); 
    	$table = DSCTable::getInstance('UnexServices', 'TiendaTable');
    	
    	$table->bind($values);
    	
    	$success =  $table->store($values);
		if($success){
        	$this->messagetype 	= 'message';
			$this->message  	= JText::_('COM_TIENDA_SAVED');
        }
        else{
        	$this->messagetype 	= 'notice';
			$this->message 		= JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
        }
        
        $redirect = $this->baseLink();    	

    	$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
    
	function cancel(){
    	$redirect = $this->baseLink();
        $redirect = JRoute::_( $redirect, false );
        
        $this->setRedirect( $redirect, '', '' );
    }
   
    
} 