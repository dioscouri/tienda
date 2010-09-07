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
            $this->message      = JText::_( 'Saved' );

            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
        }
        else
        {
            $this->messagetype  = 'notice';
            $this->message      = JText::_( 'Save Failed' )." - ".$row->getError();
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
