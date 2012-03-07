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

class TiendaControllerPayment extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		
		$this->set('suffix', 'payment');
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
    
    /**
     * saves the editing in payment plugin
     */
    function save()
    {
        $model  = $this->getModel( $this->get('suffix') );
        $row  =& JTable::getInstance('plugin');
        $row->bind( $_POST );
        $task = JRequest::getVar('task');

        if ($task == "save_as")
        {
            $pk = $row->getKeyName();
            $row->$pk = 0;
        }

        if ( $row->store() )
        {
            $model->setId( $row->id );
            $this->messagetype  = 'message';
            $this->message      = JText::_( "COM_TIENDA_SAVED");

            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
        }
        else
        {
            $this->messagetype  = 'notice';
            $this->message      = JText::_( "COM_TIENDA_SAVE_FAILED")." - ".$row->getError();
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
    }
}

?>
