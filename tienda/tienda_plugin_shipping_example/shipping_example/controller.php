<?php
class TiendaControllerShippingExample extends TiendaController {
		
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
	}
	
	function save(){
		
		$id = JRequest::getInt('id', '0');
		$values = JRequest::get('post');
		
    	// Include the custom table
    	$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('includeCustomTables', array() );    
    	$table = JTable::getInstance('ShippingMethods', 'TiendaTable');
    	
    	$table->bind($values);
    	
    	$success =  $table->store($values);
		if($success){
        	$this->messagetype 	= 'message';
			$this->message  	= JText::_( 'Saved' );
        }
        else{
        	$this->messagetype 	= 'notice';
			$this->message 		= JText::_( 'Save Failed' )." - ".$row->getError();
        }
        
        $redirect = "index.php?option=com_tienda&view=shipping&task=view&id=".$id;    	

    	$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
    
    function setRates(){
    	
    	JLoader::import( 'com_tienda.library.grid', JPATH_ADMINISTRATOR.DS.'components' );
    	JLoader::import( 'com_tienda.library.select', JPATH_ADMINISTRATOR.DS.'components' );
    	$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('includeCustomModel', array('ShippingRates') );       
        $sid = JRequest::getVar('sid');
        
        $dispatcher->trigger('includeCustomTables', array() );  
        $row = JTable::getInstance('ShippingMethods', 'TiendaTable');
        $row->load($sid);
        
        $model = JModel::getInstance('ShippingRates', 'TiendaModel');
        $model->setState('filter_shippingmethod', $sid);
        $items = $model->getList();
        
        // view
        $view = $this->getView( 'Shipping_Example', 'html' ); 
		$view->hidemenu = true;
		$view->hidestats = true;
		$view->setModel( $model, true );
		$view->assign('row', $row);
		$view->assign('items', $items);
		$view->addTemplatePath(dirname(__FILE__).DS.'tmpl'.DS);// Important!! Do not change this!
		$view->setLayout('setrates');
		$view->display();
    }
    
    function view(){
		
    	$id = JRequest::getInt('id', '0');
    	$sid = TiendaShippingPlugin::getShippingId();
    	$dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('includeCustomModel', array('ShippingMethods') );  

        $model = JModel::getInstance('ShippingMethods', 'TiendaModel');
        $model->setId((int)$sid);
        
        $item = $model->getItem();
        
        // Form
        $form = array();
        $form['action'] = "index.php?option=com_tienda&view=shipping&task=view&id={$id}&shippingTask=save";
		$view = $this->getView( 'Shipping_Example', 'html' ); 
		$view->hidemenu = true;
		$view->hidestats = true;
		$view->setModel( $model, true );
		$view->assign('item', $item);
		$view->assign('form2', $form);
		$view->addTemplatePath(dirname(__FILE__).DS.'tmpl'.DS);// Important!! Do not change this!
		$view->setLayout('view');
		$view->display();
        
    }
} 