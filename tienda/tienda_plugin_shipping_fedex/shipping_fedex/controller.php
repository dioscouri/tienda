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

JLoader::import( 'com_tienda.library.plugins.shippingcontroller', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaControllerShippingFedex extends TiendaControllerShippingPlugin 
{

    var $_element   = 'shipping_fedex';
		
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
        $this->registerTask( 'enabled.enable', 'boolean' );
        $this->registerTask( 'enabled.disable', 'boolean' );
	}

    /**
     * Changes the value of a boolean in the database
     * Expects the task to be in the format: {field}_{action}
     * where {field} = the name of the field in the database
     * and {action} is either switch/enable/disable
     *
     * @return unknown_type
     */
    function boolean()
    {
        $error = false;
        $this->messagetype  = '';
        $this->message      = '';
        $redirect = 'index.php?option=com_tienda&view='.$this->get('suffix');
        $redirect = JRoute::_( $redirect, false );

        $model = $this->getModel($this->get('suffix'));
        $row = $model->getTable();

        $cids = JRequest::getVar('cid', array (0), 'post', 'array');
        $task = JRequest::getVar( 'shippingTask' );
        $id = JRequest::getInt( 'id' );
        $vals = explode('.', $task);

        $field = $vals['0'];
        $action = $vals['1'];

        $database =& JFactory::getDBO();
        $query = "SELECT `params` FROM `#__plugins` WHERE `id` = '$id'";
        $database->setQuery($query);
        $jparams = new JParameter($database->loadObject()->params);
        
        foreach (@$params_arr as $param => $val) { $jparams->set($param, $val); }
        
        $query = "UPDATE `#__plugins` SET `params`= '".$jparams->toString()."' WHERE `id`= '$plg_id'";
        $database->setQuery($query);
            
        
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

        if ( !in_array( $field, array_keys( $row->getProperties() ) ) )
        {
            $this->messagetype  = 'notice';
            $this->message      = JText::_('Invalid Field').": {$field}";
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
            $this->message = JText::_('Status Changed');
        }

        $this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
   
    
} 