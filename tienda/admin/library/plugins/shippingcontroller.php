<?php
class TiendaControllerShippingPlugin extends TiendaController {
		
	// the same as the plugin's one!
	var $_element = '';
		
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Overrides the getView method, adding the plugin's layout path
	 */
	function getView( $name = '', $type = '', $prefix = '', $config = array() ){
    	$view = parent::getView( $name, $type, $prefix, $config ); 
    	$view->addTemplatePath(JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.$this->_element.DS.'tmpl'.DS);
    	
    	return $view;
    }
    
    /**
     * Overrides the delete method, to include the custom models and tables.
     */
    function delete(){
    	$this->includeCustomModel('ShippingRates');
    	$this->includeCustomTables();
    	parent::delete();
    }
    
    function includeCustomTables(){
   		// Include the custom table
    	$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('includeCustomTables', array() );
    }   
    
    function includeCustomModel( $name ){
    	$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('includeCustomModel', array($name) );
    }       
	
    function includeTiendaModel( $name ){
    	$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('includeTiendaModel', array($name) );
    }

    function baseLink(){
    	$id = JRequest::getInt('id', '');
    	return "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
    }
}