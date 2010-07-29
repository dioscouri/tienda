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

class TiendaControllerUsers extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		
		parent::__construct();
		//$this->registerTask( 'listuser', 'displayusers' );
		$this->set('suffix', 'users');
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
        $state['filter_username']         = $app->getUserStateFromRequest($ns.'username', 'filter_username', '', '');
        $state['filter_email']         = $app->getUserStateFromRequest($ns.'email', 'filter_email', '', '');
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }
    
    
    /**
	* 	display the view
	*/
	function displayusers($cachable=false)
	{
		// this sets the default view
		JRequest::setVar( 'view', JRequest::getVar( 'view', 'dashboard' ) );

		$document =& JFactory::getDocument();

		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		
		$view = & $this->getView( $viewName, $viewType, '', array( 'base_path'=>$this->_basePath));
       
		
        // Get/Create the model
		if ($model = & $this->getModel($viewName))
		{
			// controller sets the model's state - this is why we override parent::display()
			$this->_setModelState();
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		 $view->setLayout( 'userlists' );
		
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeDisplayAdminComponentTienda', array() );

		// Display the view
		if ($cachable && $viewType != 'feed') {
			global $option;
			$cache =& JFactory::getCache($option, 'view');
			$cache->get($view, 'display');
		} else {
			$view->display();
		}

		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterDisplayAdminComponentTienda', array() );

		$this->footer();
	}
    
    
}

?>