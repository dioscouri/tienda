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

class TiendaController extends DSCControllerAdmin
{
	
	  /**
     * default view
     */
    public $default_view = 'dashboard';
	 
	 /**
	 * @var array() instances of Models to be used by the controller
	 */
	public $_models = array();

	/**
	 * string url to perform a redirect with. Useful for child classes.
	 */
	protected $redirect;

	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();
		$this->set('suffix', 'dashboard');

		// Register Extra tasks
		
		$this->registerTask( 'add', 'add' );
		$this->registerTask( 'new', 'add' );
		
	}

	/**
	 * 	display the view
	 */
	function display($cachable=false, $urlparams = false)
	{
		/*completely extended from DSC Library, this can be removed just keeping for now to avoid confusion*/
		
		parent::display($cachable=false, $urlparams = false);
	}

	
	/**
	 * Sets the model's default state based on values in the request
	 *
	 * @return array()
	 */
	function _setModelState()
	{
		/* Completely extended from DSC Library*/
		parent::_setModelState();
	}

	

	

	/**
	 * Checks if an item is checked out, and if so, redirects to layout for viewing item
	 * Otherwise, displays a form for adding item
	 *
	 * @return void
	 */
	function add()
	{
		JRequest::setVar( 'cid', array( 0 ) );
		$this->edit();
	}
	
	

	/**
	 * Releases an item from being checked out for editing
	 * @return unknown_type
	 */
	 
	 /*This Function is completely replaced in DSC Library, However, the Language string is only in english. so keeping*/
	function release()
	{
		$model 	= $this->getModel( $this->get('suffix') );
		$row = $model->getTable();
		$row->load( $model->getId() );
		if (isset($row->checked_out) && !JTable::isCheckedOut( JFactory::getUser()->id, $row->checked_out) )
		{
			if ($row->checkin())
			{
				$this->message = JText::_('COM_TIENDA_ITEM_RELEASED');
			}
		}

		$redirect = "index.php?option=com_tienda&controller=".$this->get('suffix')."&view=".$this->get('suffix')."&task=view&id=".$model->getId()."&donotcheckout=1";
		$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Cancels operation and redirects to default page
	 * If item is checked out, releases it
	 * @return void
	 */
  /*This Function is completely replaced in DSC Library, However, the Language string is only in english. so keeping*/
	 
	function cancel()
	{
		if (!isset($this->redirect)) {
			$this->redirect = 'index.php?option=com_tienda&view='.$this->get('suffix');
		}

		$task = JRequest::getVar( 'task' );
		switch (strtolower($task))
		{
			case "cancel":
				$msg = JText::_('COM_TIENDA_OPERATION_CANCELLED');
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

		$this->setRedirect( $this->redirect, $msg, $type );
	}

	/**
	 * Saves an item and redirects based on task
	 * @return void
	 */
	 
	 /*This Function is completely replaced in DSC Library, However, the Language string is only in english. so keeping*/
	 
	function save()
	{
		$model 	= $this->getModel( $this->get('suffix') );
		$row = $model->getTable();
		$row->load( $model->getId() );
		//$row->bind( $_POST );
		$post = JRequest::get('post', '4');
		$row->bind( $post );
		$task = JRequest::getVar('task');

		if ($task=="save_as")
		{
			$pk=$row->getKeyName();
			$row->$pk= 0;
		}

		if ( $row->save() )
		{
			$model->setId( $row->id );
			$this->messagetype 	= 'message';
			$this->message  	= JText::_('COM_TIENDA_SAVED');

			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
		}
		else
		{
			$this->messagetype 	= 'notice';
			$this->message 		= JText::_('COM_TIENDA_SAVE_FAILED')." - ".$row->getError();
			
			$return = false;
		}

		$redirect = "index.php?option=com_tienda";
			
		switch ($task)
		{
			case "saveprev":
				$redirect .= '&view='.$this->get('suffix');
				// get prev in list
				$model->emptyState();
				$this->_setModelState();
				$surrounding = $model->getSurrounding( $model->getId() );
				if (!empty($surrounding['prev']))
				{
					$redirect .= '&task=edit&id='.$surrounding['prev'];
				}
				break;
			case "savenext":
				$redirect .= '&view='.$this->get('suffix');
				// get next in list
				$model->emptyState();
				$this->_setModelState();
				$surrounding = $model->getSurrounding( $model->getId() );
				if (!empty($surrounding['next']))
				{
					$redirect .= '&task=edit&id='.$surrounding['next'];
				}
				break;

			case "savenew":
				$redirect .= '&view='.$this->get('suffix').'&task=add';
				break;
			case "apply":
				$redirect .= '&view='.$this->get('suffix').'&task=edit&id='.$model->getId();
				break;
			case "save":
			default:
				$redirect .= "&view=".$this->get('suffix');
				break;
		}

		$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
		
	 return $return;
	}

	/**
	 * Deletes record(s) and redirects to default layout
	 */
	 /*This Function is completely replaced in DSC Library, However, the Language string is only in english. so keeping*/
	 
	function delete()
	{
		$error = false;
		$this->messagetype	= '';
		$this->message 		= '';
		if (!isset($this->redirect)) {
			$this->redirect = JRequest::getVar( 'return' )
			? base64_decode( JRequest::getVar( 'return' ) )
			: 'index.php?option=com_tienda&view='.$this->get('suffix');
			$this->redirect = JRoute::_( $this->redirect, false );
		}

		$model = $this->getModel($this->get('suffix'));
		$row = $model->getTable();

		$cids = JRequest::getVar('cid', array (0), 'request', 'array');
		foreach (@$cids as $cid)
		{
			if (!$row->delete($cid))
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
			$this->message = JText::_('COM_TIENDA_ITEMS_DELETED');
		}

		$this->setRedirect( $this->redirect, $this->message, $this->messagetype );
	}

	/**
	 * Reorders a single item either up or down (based on arrow-click in list) and redirects to default layout
	 * @return void
	 */
	 
	 /*This Function is completely replaced in DSC Library, However, the Language string is only in english. so keeping*/
	 
	function order()
	{
		$error = false;
		$this->messagetype	= '';
		$this->message 		= '';
		$redirect = 'index.php?option=com_tienda&view='.$this->get('suffix');
		$redirect = JRoute::_( $redirect, false );

		$model = $this->getModel($this->get('suffix'));
		$row = $model->getTable();
		$row->load( $model->getId() );

		$change	= JRequest::getVar('order_change', '0', 'post', 'int');

		if ( !$row->move( $change ) )
		{
			$this->messagetype 	= 'notice';
			$this->message 		= JText::_('COM_TIENDA_ORDERING_FAILED')." - ".$row->getError();
		}

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Reorders multiple items (based on form input from list) and redirects to default layout
	 * @return void
	 */
	 /*This Function is completely replaced in DSC Library, However, the Language string is only in english. so keeping*/
	 
	function ordering()
	{
		$error = false;
		$this->messagetype	= '';
		$this->message 		= '';
		$redirect = 'index.php?option=com_tienda&view='.$this->get('suffix');
		$redirect = JRoute::_( $redirect, false );

		$model = $this->getModel($this->get('suffix'));
		$row = $model->getTable();

		$ordering = JRequest::getVar('ordering', array(0), 'post', 'array');
		$cids = JRequest::getVar('cid', array (0), 'post', 'array');
		foreach (@$cids as $cid)
		{
			$row->load( $cid );
			$row->ordering = @$ordering[$cid];

			if (!$row->store())
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		$row->reorder();

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = JText::_('COM_TIENDA_ITEMS_ORDERED');
		}

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Changes the value of a boolean in the database
	 * Expects the task to be in the format: {field}_{action}
	 * where {field} = the name of the field in the database
	 * and {action} is either switch/enable/disable
	 *
	 * @return unknown_type
	 */
	 
	 /*This Function is completely replaced in DSC Library, However, the Language string is only in english. so keeping*/
	 
	function boolean()
	{
		$error = false;
		$this->messagetype	= '';
		$this->message 		= '';
		$redirect = 'index.php?option=com_tienda&view='.$this->get('suffix');
		$redirect = JRoute::_( $redirect, false );

		$model = $this->getModel($this->get('suffix'));
		$row = $model->getTable();

		$cids = JRequest::getVar('cid', array (0), 'post', 'array');
		$task = JRequest::getVar( 'task' );
		$vals = explode('.', $task);

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
				$this->messagetype 	= 'notice';
				$this->message 		= JText::_('COM_TIENDA_INVALID_TASK');
				$this->setRedirect( $redirect, $this->message, $this->messagetype );
				return;
				break;
		}

		if ( !in_array( $field, array_keys( $row->getProperties() ) ) )
		{
			$this->messagetype 	= 'notice';
			$this->message 		= JText::_('COM_TIENDA_INVALID_FIELD').": {$field}";
			$this->setRedirect( $redirect, $this->message, $this->messagetype );
			return;
		}

		foreach (@$cids as $cid)
		{
			unset($row);
			$row = $model->getTable();
			$row->load( $cid );

			switch ($switch)
			{
				case "1":
					$row->$field = $row->$field ? '0' : '1';
					break;
				case "0":
				default:
					$row->$field = $enable;
					break;
			}

			if ( !$row->save() )
			{
				$this->message .= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if ($error)
		{
			$this->message = JText::_('COM_TIENDA_ERROR') . ": " . $this->message;
		}
		else
		{
			$this->message = JText::_('COM_TIENDA_STATUS_CHANGED');
		}

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}


	

	/**
	 * Hides a tooltip message
	 * @return unknown_type
	 */
	function pagetooltip_switch()
	{
		$msg = new stdClass();
		$msg->type 		= '';
		$msg->message 	= '';
		$view = JRequest::getVar('view');
		$msg->link 		= 'index.php?option=com_tienda&view='.$view;

		$key = JRequest::getVar('key');
		$constant = 'page_tooltip_'.$key;
		$config_title = $constant."_disabled";

		$database = &JFactory::getDBO();
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables'.DS );
		unset($table);
		$table = JTable::getInstance( 'config', 'TiendaTable' );
		$table->load( array('config_name'=>$config_title) );
		$table->config_name = $config_title;
		$table->value = '1';

		if (!$table->save())
		{
			$msg->message = JText::_('COM_TIENDA_ERROR') . ": " . $table->getError();
		}

		$this->setRedirect( $msg->link, $msg->message, $msg->type );
	}

	


	/**
	 * For displaying a searchable list of products in a lightbox
	 * Usage:
	 */
	function elementProduct()
	{
		$model 	= $this->getModel( 'elementproduct' );
		$view	= $this->getView( 'elementproduct' );
		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * For displaying a searchable list of images in a lightbox
	 * Usage:
	 */
	function elementImage()
	{
		$model 	= $this->getModel( 'elementimage' );
		$view	= $this->getView( 'elementimage' );
		$view->setModel( $model, true );
		$view->display();
	}

}

?>