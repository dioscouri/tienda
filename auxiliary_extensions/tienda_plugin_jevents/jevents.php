<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );
class plgTiendaJEvents extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element    = 'jevents';
    
    function plgTiendaJEvents(& $subject, $config) 
    {
        parent::__construct($subject, $config);
        $this->loadLanguage( '', JPATH_ADMINISTRATOR );

        //Check the installation integrity
        $helper = Tienda::getClass( 'TiendaHelperDiagnosticsJevents', 'jevents.diagnostic', array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ) );
        $helper->checkInstallation();
        
        // include custom tables
        JTable::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'jevents'.DS.'tables' );
        JModel::addIncludePath( JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'jevents'.DS.'models' );
    }

    /**
     * will return the HTML of the event maping 
     * @param  object
     * @return string
     */
    function onAfterDisplayProductFormRightColumn( $product )
    {
        $product_id = '';
        if (!empty($product->product_id))
        {
            // this is a existing product
            $product_id = $product->product_id;           
        }
        
        // events
        $elementEventModel = JModel::getInstance( 'ElementEvent', 'TiendaModel' );
        $elementEvent_terms = $elementEventModel->_fetchElement( 'jevent_eventid', $product_id );
        $resetEvent_terms = $elementEventModel->_clearElement( 'jevent_eventid', '0' );
        
        $model = JModel::getInstance('JeventsEventsProducts', 'TiendaModel');
        $model->setId( $product_id );
        $event = $model->getItem();
        
        $vars->product = $product;
        $vars->elementEvent_terms = $elementEvent_terms;
        $vars->resetEvent_terms = $resetEvent_terms;
        $vars->event = $event;
        echo $this->_getLayout( 'product_form', $vars );
        return null;
    }

	/*
	 * will update or insert the mapping table on the saving of the product
	 *
	 * @return unknown_type
	 */
	function onAfterSaveProducts( $product )
	{
        jimport('joomla.application.component.helper');
        // check jevent is installed
        $isInstalled = JComponentHelper::isEnabled('com_jevents', false);

		// if JEvent is installed
		if ($isInstalled)
		{
			$event_id = JRequest::getInt('jevent_eventid');
            $product_id = JRequest::getInt('id');

			$row = JTable::getInstance('JEventsEventsProducts', 'TiendaTable');
			
			$row->load( array('product_id'=>$product_id) );
            $row->event_id = $event_id;

			if (!$row->save())
			{
				$this->messagetype  = 'notice';
				$this->message      = JText::_( 'Save Failed' )." - ".$row->getError();
				JFactory::getApplication()->enqueueMessage( $this->message, $this->messagetype );
			}
		}

	}
	
	/*
	 * to show the list of the events 
	 */
    function showEvents()
	{
        Tienda::load( 'TiendaUrl', 'library.url' );
        Tienda::load( 'TiendaSelect', 'library.select' );
        Tienda::load( 'TiendaGrid', 'library.grid' );
	    
		$this->includeCustomModel('JEventsEvents');
       	$model = JModel::getInstance( 'JEventsEvents', 'TiendaModel' );
       	
	  	$state = $this->_setModelState($model);
    	$app = JFactory::getApplication();
		
        $ns = $this->getNamespace($model);

      //	$state['filter_parentid'] 	= $app->getUserStateFromRequest($ns.'parentid', 'filter_parentid', '', '');
      	$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'evdet_id', 'cmd');

    	foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );	
		}
       	
       	$items = $model->getList();
       	// here you could loop thru the items if you wanted, to add an ->link to each one, for example
       	
       	$vars->state = $model->getState();
        $vars->items = $items;
        echo $this->_getLayout( 'list', $vars );
	}
	
	
	/**
	 * Sets the model's default state based on values in the request
	 *
	 * @return array()
	 */
    function _setModelState($model)
    {
		$app = JFactory::getApplication();
		$ns = $this->getNamespace($model);

		$state = array();

        $state['limit']  	= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $state['limitstart'] = $app->getUserStateFromRequest($ns.'limitstart', 'limitstart', 0, 'int');
        $state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.'.$model->getTable()->getKeyName(), 'cmd');
        $state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'ASC', 'word');
        $state['filter']    = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
        $state['filter_enabled'] 	= $app->getUserStateFromRequest($ns.'enabled', 'filter_enabled', '', '');
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
     * Gets the view's namespace for state variables
     * @return string
     */  
    function getNamespace($model)
    {
    	$app = JFactory::getApplication();
    	$ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');
    	return $ns;
    }
	
}
