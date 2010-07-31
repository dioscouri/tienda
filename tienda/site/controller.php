<?php
/**
 * @version	0.1
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class TiendaController extends JController 
{	
	var $_models = array();
	var $message = "";
	var $messagetype = "";
		
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		$this->set('suffix', 'products');
		
		// Register Extra tasks
		$this->registerTask( 'list', 'display' );
		$this->registerTask( 'close', 'cancel' );
		$this->registerTask( 'add', 'edit' );
		$this->registerTask( 'new', 'edit' );
		$this->registerTask( 'apply', 'save' );
	}
	
	/**
	 * 
	 * @return unknown_type
	 */
    function _setModelState()
    {
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		$state = array();
		
    	// limitstart isn't working for some reason when using getUserStateFromRequest -- cannot go back to page 1
		$limit  = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getVar('limitstart', '0', 'request', 'int');
		// If limit has been changed, adjust offset accordingly
		$state['limitstart'] = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
        $state['limit']  	= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $state['filter_enabled'] = 1;
        $state['filter_category'] = '0';
        $state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.'.$model->getTable()->getKeyName(), 'cmd');
        $state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'ASC', 'word');
        $state['filter']    = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
        $state['id']        = JRequest::getVar('id', JRequest::getVar('id', '', 'get', 'int'), 'post', 'int');

        // TODO santize the filter
        // $state['filter']   	= 

    	foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );	
		}
  		return $state;
    }

    /**
     * 
     * @return unknown_type
     */
    function getNamespace()
    {
    	$app = JFactory::getApplication();
    	$model = $this->getModel( $this->get('suffix') );
		$ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');
    	return $ns;
    }
    
    /**
     * We override parent::getModel because parent::getModel was always creating a new Model instance
     *
     */
	function getModel( $name = '', $prefix = '', $config = array() )
	{
		if ( empty( $name ) ) {
			$name = $this->getName();
		}

		if ( empty( $prefix ) ) {
			$prefix = $this->getName() . 'Model';
		}
		
		$fullname = strtolower( $prefix.$name ); 
		if (empty($this->_models[$fullname]))
		{
			if ( $model = & $this->_createModel( $name, $prefix, $config ) )
			{
				// task is a reserved state
				$model->setState( 'task', $this->_task );
	
				// Lets get the application object and set menu information if its available
				$app	= &JFactory::getApplication();
				$menu	= &$app->getMenu();
				if (is_object( $menu ))
				{
					if ($item = $menu->getActive())
					{
						$params	=& $menu->getParams($item->id);
						// Set Default State Data
						$model->setState( 'parameters.menu', $params );
					}
				}
			}
				else 
			{
				$model = new JModel();
			}
			$this->_models[$fullname] = $model;
		}

		return $this->_models[$fullname];
	}
	
	/**
	* 	display the view
	*/
	function display($cachable=false)
	{
		// this sets the default view
		JRequest::setVar( 'view', JRequest::getVar( 'view', 'products' ) );
		
		$document =& JFactory::getDocument();

		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$viewLayout	= JRequest::getCmd( 'layout', 'default' );

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
		$view->setLayout($viewLayout);

        // Set the task in the view, so the view knows it is a valid task 
        if (in_array($this->_task, array_keys($this->_taskMap) ))
        {
            $view->setTask($this->_doTask);	
        }
		
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeDisplayComponentTienda', array() );
		
		// Display the view
		if ($cachable && $viewType != 'feed') {
			global $option;
			$cache =& JFactory::getCache($option, 'view');
			$cache->get($view, 'display');
		} else {
			$view->display();
		}

		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterDisplayComponentTienda', array() );
		
        $this->footer();		
	}

	/**
	 * cancel and redirect to main page
	 * @return void
	 */
	function cancel() 
	{
		$link = 'index.php?option=com_tienda&view='.$this->get('suffix');
		
		$task = JRequest::getVar( 'task' );
		switch (strtolower($task))
		{
			case "cancel":
				$msg = JText::_( 'Operation Cancelled' );
				$type = "notice";
			  break;
			case "close":
			default:
				$model 	= $this->getModel( $this->get('suffix') );
			    $row = $model->getTable();
			    $row->load( $model->getId() );
				if (isset($row->checked_out) && !JTable::isCheckedOut( JFactory::getUser()->id, $row->checked_out) )
				{
					$row->checkin();
				}
				$msg = "";
				$type = "";				
			  break;
		}
	    
	    $this->setRedirect( $link, $msg, $type );		
	}

    /**
     * Verifies the fields in a submitted form.  Uses the table's check() method.
     * Will often be overridden. Is expected to be called via Ajax 
     * 
     * @return unknown_type
     */
    function validate()
    {
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
                $response['msg'] = '
                    <dl id="system-message">
                    <dt class="notice">notice</dt>
                    <dd class="notice message fade">
                        <ul style="padding: 10px;">'.
                        JText::_("Could not process form")                        
                        .'</ul>
                    </dd>
                    </dl>
                    ';
                echo ( json_encode( $response ) );
                return;
            }
            
        // convert elements to array that can be binded             
            Tienda::load( 'TiendaHelperBase', 'helpers._base' );
            $values = TiendaHelperBase::elementsToArray( $elements );

        // get table object
            $table = $this->getModel( $this->get('suffix') )->getTable();
        
        // bind to values
            $table->bind( $values );
        
        // validate it using table's ->check() method
            if (!$table->check())
            {
                // if it fails check, return message
                $response['error'] = '1';
                $response['msg'] = '
                    <dl id="system-message">
                    <dt class="notice">notice</dt>
                    <dd class="notice message fade">
                        <ul style="padding: 10px;">'.
                        $table->getError()                      
                        .'</ul>
                    </dd>
                    </dl>
                    ';
            }

        echo ( json_encode( $response ) );
        return;
    }

    /**
     * Displays the footer
     * 
     * @return unknown_type
     */
    function footer()
    {
    	$show_linkback = TiendaConfig::getInstance()->get('show_linkback', '1');
    	$format = JRequest::getVar('format');
        if ($show_linkback == '1' && $format != 'raw') 
        {
        	// show a generous linkback, TIA
	        $model  = $this->getModel( 'dashboard' );
	        $view   = $this->getView( 'dashboard', 'html' );
	        $view->hidemenu = true;
	        $view->setTask('footer');
	        $view->setModel( $model, true );
	        $view->setLayout('footer');
	        $view->display();
        }

        return;
    }
    
	/**
	 * 
	 * @return 
	 */
	function doTask()
	{
		$success = true;
		$msg = new stdClass();
		$msg->message = '';
		$msg->error = '';
				
		// expects $element in URL and $elementTask
		$element = JRequest::getVar( 'element', '', 'request', 'string' );
		$elementTask = JRequest::getVar( 'elementTask', '', 'request', 'string' );

		$msg->error = '1';
		// $msg->message = "element: $element, elementTask: $elementTask";
		
		// gets the plugin named $element
		$import 	= JPluginHelper::importPlugin( 'tienda', $element );
		$dispatcher	=& JDispatcher::getInstance();
		// executes the event $elementTask for the $element plugin
		// returns the html from the plugin
		// passing the element name allows the plugin to check if it's being called (protects against same-task-name issues)
		$result 	= $dispatcher->trigger( $elementTask, array( $element ) );
		// This should be a concatenated string of all the results, 
			// in case there are many plugins with this eventname 
			// that return null b/c their filename != element) 
		$msg->message = implode( '', $result );
			// $msg->message = @$result['0'];
						
		// encode and echo (need to echo to send back to browser)		
		echo $msg->message;
		$success = $msg->message;

		return $success;
	}
	
	/**
	 * 
	 * @return 
	 */
	function doTaskAjax()
	{
		$success = true;
		$msg = new stdClass();
		$msg->message = '';
				
		// get elements $element and $elementTask in URL 
			$element = JRequest::getVar( 'element', '', 'request', 'string' );
			$elementTask = JRequest::getVar( 'elementTask', '', 'request', 'string' );
			
		// get elements from post
			// $elements = json_decode( preg_replace('/[\n\r]+/', '\n', JRequest::getVar( 'elements', '', 'post', 'string' ) ) );
			
		// for debugging
			// $msg->message = "element: $element, elementTask: $elementTask";

		// gets the plugin named $element
			$import 	= JPluginHelper::importPlugin( 'tienda', $element );
			$dispatcher	=& JDispatcher::getInstance();
			
		// executes the event $elementTask for the $element plugin
		// returns the html from the plugin
		// passing the element name allows the plugin to check if it's being called (protects against same-task-name issues)
			$result 	= $dispatcher->trigger( $elementTask, array( $element ) );
		// This should be a concatenated string of all the results, 
			// in case there are many plugins with this eventname 
			// that return null b/c their filename != element)
			$msg->message = implode( '', $result );
			// $msg->message = @$result['0'];

		// set response array
			$response = array();
			$response['msg'] = $msg->message;
			
		// encode and echo (need to echo to send back to browser)
			echo ( json_encode( $response ) );

		return $success;
	}
	
    /*
     * Set the currency to a different value
     */
    function setCurrency()
    {
        $currency_id = JRequest::getVar('currency_id', 0);
        
        if($currency_id)
        {
            $helper = Tienda::getClass('TiendaHelperBase', 'helpers._base');
            $helper->setSessionVariable( 'currency_id', $currency_id );
        }
        
        $return = JRequest::getVar( 'return', '' );
        if( $return )
        {
            $url = base64_decode($return);
        }
        else
        {
            $url = 'index.php?option=com_tienda&view_products';
        }
        
        $this->setRedirect(JRoute::_($url));
        $this->redirect();
        return;
    }
	
	
}

?>