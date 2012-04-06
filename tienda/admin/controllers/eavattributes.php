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

class TiendaControllerEavAttributes extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'eavattributes');
        $this->registerTask( 'enabled.enable', 'boolean' );
        $this->registerTask( 'enabled.disable', 'boolean' );
        $this->registerTask( 'selected_enable', 'selected_switch' );
		$this->registerTask( 'selected_disable', 'selected_switch' );
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
        $state['filter_entitytype']         = $app->getUserStateFromRequest($ns.'entitytype', 'filter_entitytype', '', '');
				$state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.ordering', 'cmd');
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        return $state;
    }
    
	/**
	 * Loads view for assigning entities to attributes
	 *
	 * @return unknown_type
	 */
	function selectentities()
	{
		$type = JRequest::getVar('eaventity_type', 'products');
		
		$this->set('suffix', $type);
		$state = parent::_setModelState();
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
		$ns = $this->getNamespace();

		foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );
		}

		$id = JRequest::getVar( 'id', JRequest::getVar( 'id', '0', 'post', 'int' ), 'get', 'int' );
		$row = $model->getTable( 'eavattributes' );
		$row->load( $id );

		$view   = $this->getView( 'eavattributes', 'html' );
		$view->set( '_controller', 'eavattributes' );
		$view->set( '_view', 'eavattributes' );
		$view->set( '_action', "index.php?option=com_tienda&controller=eavattributes&task=selectentities&tmpl=component&eaventity_type=$type&id=".$model->getId() );
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->setLayout( 'select'.$type );
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

		$type = JRequest::getVar('eaventity_type', 'products');
		
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
			$table = JTable::getInstance('EavAttributeEntities', 'TiendaTable');
			$keynames["eavattribute_id"] = $id;
			$keynames["eaventity_id"] = $cid;
			$keynames["eaventity_type"] = $type;
			
			$table->load( $keynames );
			if ($switch)
			{
				if (isset($table->eaventity_id))
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
					$table->eaventity_id = $cid;
					$table->eavattribute_id = $id;
					$table->eaventity_type = $type;
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
						$table->eaventity_id = $cid;
						$table->eavattribute_id = $id;
						$table->eaventity_type = $type;
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
		base64_decode( JRequest::getVar( 'return' ) ) : "index.php?option=com_tienda&controller=eavattributes&task=selectentities&tmpl=component&eaventity_type={$type}&id=".$id;
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}
	
}

?>