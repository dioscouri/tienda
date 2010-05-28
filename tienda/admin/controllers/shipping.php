<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerShipping extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'shipping');
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

        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_name']         = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }
    
    function execute( $task ){
    	
    	$shippingTask = JRequest::getWord('shippingTask', '');
    	
    	// Check if we are in a shipping method view. If it is so, 
    	// Try lo load the shipping plugin controller (if any)
    	if( $task  == "view" && $shippingTask != '' ){
    		$model = $this->getModel('Shipping', 'TiendaModel');

    		$id = JRequest::getInt('id', '0');
    		
    		if(!$id)
    			parent::execute($task);
    			
    		$model->setId($id);
	
			// get the data
			// not using getItem here to enable ->checkout (which requires JTable object)
			$row = $model->getTable();
			$row->load( (int) $model->getId() );
    		$element = $row->element;
    		
			// The name of the Shipping Controller should be the same of the $_element name, 
			// without the shipping_ prefix and with the first letter Uppercase, and should 
			// be placed into a controller.php file inside the root of the plugin
			// Ex: shipping_standard => TiendaControllerShippingStandard in shipping_standard/controller.php
			$controllerName = str_ireplace('shipping_', '', $element);
			$controllerName = ucfirst($controllerName);
			
	    	$path = JPATH_SITE.DS.'plugins'.DS.'tienda'.DS;
	    	$controllerPath = $path.$element.DS.'controller.php';
	    	
			if (file_exists($controllerPath)) {
				require_once $controllerPath;
			} else {
				$controllerName = '';
			}
			
			$className    = 'TiendaControllerShipping'.$controllerName;

			if ($controllerName != '' && class_exists($className)){
				
	    		// Create the controller
				$controller   = new $className( );
				
				// Add the view Path
				$controller->addViewPath($path);
				
				// Perform the requested task
				$controller->execute( $shippingTask );
				
				// Redirect if set by the controller
				$controller->redirect();
				
			} else{
				parent::execute($task);
			}
    	} else{
    		parent::execute($task);
    	}
    }
    
}

?>