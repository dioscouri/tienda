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

class TiendaControllerShippingWeightbased extends TiendaControllerShippingPlugin
{

	var $_element   = 'shipping_weightbased';

	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();
		JModel::addIncludePath( JPATH_SITE.'/plugins/tienda/shipping_weightbased/models' );
		JTable::addIncludePath( JPATH_SITE.'/plugins/tienda/shipping_weightbased/tables' );
	}

	/**
	 * Gets the plugin's namespace for state variables
	 * @return string
	 */
	function getNamespace()
	{
		$app = JFactory::getApplication();
		$ns = $app->getName().'::'.'com.tienda.plugin.shipping.weightbased';
		return $ns;
	}
	
	function newMethod(){
		return $this->view();
	}

	function save(){

		$values = JRequest::get('post');

		$this->includeCustomTables();
		$table = JTable::getInstance('ShippingMethodsWeightbased', 'TiendaTable');
		 
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

	function setRates()
	{
		Tienda::load( 'TiendaGrid', 'library.grid' );
		Tienda::load( 'TiendaSelect', 'library.select' );
		$this->includeCustomModel('ShippingRatesWeightbased');
		$sid = JRequest::getVar('sid');

		$this->includeCustomTables();
		$row = JTable::getInstance('ShippingMethodsWeightbased', 'TiendaTable');
		$row->load($sid);

		$model = JModel::getInstance('ShippingRatesWeightbased', 'TiendaModel');
		$model->setState('filter_shippingmethod', $sid);
		$app = JFactory::getApplication();
		$ns = $this->getNamespace();
		$state = array();
		$state['limit']  	= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$state['limitstart'] = $app->getUserStateFromRequest($ns.'limitstart', 'limitstart', 0, 'int');
		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}
		$items = $model->getList();

		//form
		$form = array();
		$form['action'] = $this->baseLink();

		// view
		$view = $this->getView( 'shipping_weightbased', 'html' );
		$view->hidemenu = true;
		$view->hidestats = true;
		$view->setModel( $model, true );
		$view->assign('row', $row);
		$view->assign('items', $items);
		$view->assign('form2', $form);
		$view->assign('baseLink', $this->baseLink());
		$view->setLayout('setrates');
		$view->display();
	}

	function cancel(){
		$redirect = $this->baseLink();
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, '', '' );
	}

	function view()
	{
		JLoader::import( 'com_tienda.library.button', JPATH_ADMINISTRATOR.'/components' );
		TiendaToolBarHelper::custom( 'save', 'save', 'save', 'COM_TIENDA_SAVE', false, 'shippingTask' );
		TiendaToolBarHelper::custom( 'cancel', 'cancel', 'cancel', 'COM_TIENDA_CLOSE', false, 'shippingTask' );
		 
		$id = JRequest::getInt('id', '0');
		$sid = TiendaShippingPlugin::getShippingId();
		$this->includeCustomModel('ShippingMethodsWeightbased');

		$model = JModel::getInstance('ShippingMethodsWeightbased', 'TiendaModel');
		$model->setId( (int)$sid );

		$item = $model->getItem();

		// Form
		$form = array();
		$form['action'] = $this->baseLink();
		$form['shippingTask'] = 'save';
		$view = $this->getView( 'shipping_weightbased', 'html' );
		$view->hidemenu = true;
		$view->hidestats = true;
		$view->setModel( $model, true );
		$view->assign('item', $item);
		$view->assign('form2', $form);
		$view->setLayout('view');
		$view->display();

	}

	/**
	 * Creates a shipping rate and redirects
	 *
	 * @return unknown_type
	 */
	function createrate()
	{
		$this->includeCustomModel('shippingratesweightbased');
		$this->includeCustomTables();

		$this->set('suffix', 'shippingratesweightbased');
		$model  = $this->getModel( $this->get('suffix') );

		$row = $model->getTable();
		$row->bind(JRequest::get('post'));
		if ( $row->save() )
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
		}

		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$row->shipping_method_weightbased_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves the properties for all prices in list
	 *
	 * @return unknown_type
	 */
	function saverates()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';

		$this->includeCustomModel('ShippingRatesWeightbased');
		$this->includeCustomTables();
		$model = $this->getModel('shippingratesweightbased');
		$row = $model->getTable();

		$cids = JRequest::getVar('cid', array(0), 'request', 'array');
		$geozones = JRequest::getVar('geozones', array(0), 'request', 'array');
		$base_prices = JRequest::getVar('base_prices', array(0), 'request', 'array');
		$weight_starts = JRequest::getVar('weight_starts', array(0), 'request', 'array');
		$weight_ends = JRequest::getVar('weight_ends', array(0), 'request', 'array');
		$weight_steps_size = JRequest::getVar('weight_steps_size', array(0), 'request', 'array');
		$price_steps = JRequest::getVar('price_steps', array(0), 'request', 'array');
		$handlings = JRequest::getVar('handlings', array(0), 'request', 'array');

		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->geozone_id = $geozones[$cid];
			$row->base_price = $base_prices[$cid];
			$row->price_step = $price_steps[$cid];
			$row->weight_step_size = $weight_steps_size[$cid];
			$row->weight_start = $weight_starts[$cid];
			$row->weight_end = $weight_ends[$cid];
			$row->shipping_handling = $handlings[$cid];

			if (!$row->save())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$row->shipping_method_weightbased_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}
	 
	/**
	 * Deletes a shipping rate and redirects
	 *
	 * @return unknown_type
	 */
	function deleterate()
	{
		$this->set('suffix', 'shippingratesweightbased');
		$model  = $this->getModel( $this->get('suffix') );

		$cids = JRequest::getVar('cid', array(0), 'request', 'array');

		foreach (@$cids as $cid)
		{
			$row = $model->getTable();
			$row->load( $cid );

			if (!$row->delete())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$row->shipping_method_weightbased_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}
}