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

class TiendaControllerGeozones extends TiendaController
{
	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->set('suffix', 'geozones');
		$this->registerTask( 'selected_enable', 'selected_switch' );
		$this->registerTask( 'selected_disable', 'selected_switch' );
		$this->registerTask( 'plugin_enable', 'plugin_switch' );
		$this->registerTask( 'plugin_disable', 'plugin_switch' );
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
		$state['filter_name']       = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
		$state['filter_geozonetype'] = $app->getUserStateFromRequest($ns.'geozonetype', 'filter_geozonetype', '', '');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}
		return $state;
	}
	 
	/**
	 * Loads view for assigning product to categories
	 *
	 * @return unknown_type
	 */
	function selectzones()
	{
		$this->set('suffix', 'zones');
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$row = $model->getTable( 'geozones' );
		$row->load( $id );

		$state['filter_associated']   = $app->getUserStateFromRequest($ns.'associated', 'filter_associated', '', '');
		if ($state['filter_associated'])
		{
			$state['filter_geozoneid']   = $id;
		}
		$state['filter_countryid']   = $app->getUserStateFromRequest($ns.'countryid', 'filter_countryid', '', '');
		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.zone_name', 'cmd');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$view   = $this->getView( 'geozones', 'html' );
		$view->set( '_controller', 'geozones' );
		$view->set( '_view', 'geozones' );
		$view->set( 'leftMenu', false );
		$view->set( '_action', "index.php?option=com_tienda&controller=geozones&task=selectzones&tmpl=component&id=".$model->getId() );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'selectzones' );
		$view->setTask(true);
		$view->display();
	}

	/**
	 *
	 * @return unknown_type
	 */
	function selected_switch()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';

		$model = $this->getModel($this->get('suffix'));
		$row = $model->getTable();

		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$cids = JRequest::getVar('cid', array (0), 'request', 'array');
		$task = JRequest::getVar( 'task' );
		$vals = explode('_', $task);

		$field = $vals['0'];
		$action = $vals['1'];

		switch (strtolower($action))
		{
			case "switch":
				$switch = '1';
				break;
			case "disable":
				$enable = '0';
				$switch = '0';
				break;
			case "enable":
				$enable = '1';
				$switch = '0';
				break;
			default:
				$this->messagetype  = 'notice';
				$this->message      = JText::_('COM_TIENDA_INVALID_TASK');
				$this->setRedirect( $redirect, $this->message, $this->messagetype );
				return;
				break;
		}

		$keynames = array();
		foreach (@$cids as $cid)
		{
			$table = JTable::getInstance('ZoneRelations', 'TiendaTable');
			$keynames["geozone_id"] = $id;
			$keynames["zone_id"] = $cid;
			$table->load( $keynames );
			if ($switch)
			{
				if (!empty($table->zone_id))
				{
					if (!$table->delete())
					{
						$this->message .= $cid.': '.$table->getError().'<br/>';
						$this->messagetype = 'notice';
						$error = true;
					}
				}
				else
				{
					$table->geozone_id = $id;
					$table->zone_id = $cid;
					if (!$table->save())
					{
						$this->message .= $cid.': '.$table->getError().'<br/>';
						$this->messagetype = 'notice';
						$error = true;
					}
				}
			}
			else
			{
				switch ($enable)
				{
					case "1":
						$table->geozone_id = $id;
						$table->zone_id = $cid;
						if (!$table->save())
						{
							$this->message .= $cid.': '.$table->getError().'<br/>';
							$this->messagetype = 'notice';
							$error = true;
						}
						break;
					case "0":
					default:
						if (!$table->delete())
						{
							$this->message .= $cid.': '.$table->getError().'<br/>';
							$this->messagetype = 'notice';
							$error = true;
						}
						break;
				}
			}
		}

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . ": " . $this->message;
		}
		else
		{
			$this->message = "";
		}

		$redirect = JRequest::getVar( 'return' ) ?
		base64_decode( JRequest::getVar( 'return' ) ) : "index.php?option=com_tienda&controller=geozones&task=selectzones&tmpl=component&id=".$id;
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves the zip code ranges for all the enabled zones in the list
	 *
	 * @return unknown_type
	 */
	function savezipranges()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';
		$model = $this->getModel('zonerelations');
		$row = $model->getTable();

		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$cids = JRequest::getVar('cid', array(0), 'request', 'array');
		$ranges = JRequest::getVar('zip_range', array(0), 'request', 'array');

		foreach($cids as $cid)
		{
			$keynames["geozone_id"] = $id;
			$keynames["zone_id"] = $cid;
			$row->load( $keynames );

			if (!empty($row->zone_id))
			{
				$row->zip_range = $ranges[$cid];
				if (!$row->save())
				{
					$this->message .= $cid.': '.$row->getError().'<br/>';
					$this->messagetype = 'notice';
					$error = true;
				}
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

		$redirect = "index.php?option=com_tienda&controller=geozones&task=selectzones&tmpl=component&id=".$id;
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Method to add/remove the geozone to plugin parameter
	 */
	function plugin_switch()
	{
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';

		$type = JRequest::getVar('type');
		Tienda::load( "TiendaHelperPlugin", 'helpers.plugin' );
		$suffix = TiendaHelperPlugin::getSuffix($type);

		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$cids = JRequest::getVar('cid', array (0), 'request', 'array');
		$task = JRequest::getVar( 'task' );
		$vals = explode('_', $task);

		$field = $vals['0'];
		$action = $vals['1'];

		switch (strtolower($action))
		{
			case "switch":
				$switch = '1';
				break;
			case "disable":
				$enable = '0';
				$switch = '0';
				break;
			case "enable":
				$enable = '1';
				$switch = '0';
				break;
			default:
				$this->messagetype  = 'notice';
				$this->message      = JText::_('COM_TIENDA_INVALID_TASK');
				$this->setRedirect( $redirect, $this->message, $this->messagetype );
				return;
				break;
		}

		$model = $this->getModel($suffix);

		$keynames = array();
		foreach (@$cids as $cid)
		{
			$row = $model->getTable();
			$keynames["id"] = $cid;
			$row->load( $keynames );
			 
			$params = new DSCParameter($row->params);
			 
			$geozones = explode(',',$params->get('geozones'));


			if ($switch)
			{
				if (in_array($id, $geozones))
				{
					$geozones = explode(',',$params->get('geozones'));
					$geozones = array_diff($geozones, array($id));
				}
				else
				{
					$geozones[] = $id;
				}
			}
			else
			{
				switch($enable)
				{
					case "1":
						$geozones[] = $id;
						break;
					case "0":
					default:
						$geozones = explode(',',$params->get('geozones'));
						$geozones = array_diff($geozones, array($id));
						break;
				}
			}
			 
			$geozones = array_filter($geozones, 'strlen'); //remove empty values
			$params->set( 'geozones', implode(',', array_unique($geozones)) );  //remove duplicate
			$row->params = trim( $params->toString() );

			if (!$row->save())
			{
				$this->message .= $cid.': '.$row->getError().'<br/>';
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . ": " . $this->message;
		}
		 
		$redirect = JRoute::_( "index.php?option=com_tienda&controller=geozones&task=selectplugins&type={$type}&tmpl=component&id=".$id, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Method to assign payment/shipping methods to the geozones
	 */
	function selectplugins()
	{
		$type = JRequest::getVar('type');
		Tienda::load( "TiendaHelperPlugin", 'helpers.plugin' );
		$suffix = TiendaHelperPlugin::getSuffix($type);

		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $suffix );
		$ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');

		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$row = $model->getTable( 'geozones' );
		$row->load( $id );

		$state['filter_enabled'] = '1';
		$state['filter_name']   = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
		$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.name', 'cmd');

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$view   = $this->getView( 'geozones', 'html' );
		$view->set( '_controller', 'geozones' );
		$view->set( '_view', 'geozones' );
		$view->set( 'leftMenu', false );
		$view->set( '_action', "index.php?option=com_tienda&controller=geozones&task=selectplugins&type={$type}&tmpl=component&id=".$model->getId() );
		$view->setModel( $model, true );

		$items = $model->getList();
		foreach($items as $item)
		{
			$params = new DSCParameter($item->params);
			$item->geozones = explode(',',$params->get('geozones'));
		}

		$view->assign( 'suffix', $suffix );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'selectplugins' );
		$view->display();
	}
}

?>