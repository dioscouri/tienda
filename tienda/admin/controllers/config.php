<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerConfig extends TiendaController 
{
    /**
     * constructor
     */
    function __construct() 
    {
        parent::__construct();
        
        $this->set('suffix', 'config');
    }
    
    /**
     * save a record
     * @return void
     */
    function save() 
    {
        $error = false;
        $errorMsg = "";
        $model  = $this->getModel( $this->get('suffix') );
        $config = TiendaConfig::getInstance();
        $properties = $config->getProperties();

        foreach (@$properties as $key => $value ) 
        {
            unset($row);
            $row = $model->getTable( 'config' );
            $newvalue = JRequest::getVar( $key,'','post','string',JREQUEST_ALLOWRAW | JREQUEST_NOTRIM);
            $value_exists = array_key_exists( $key, $_POST );
            if ( $value_exists && !empty($key) ) 
            { 
                // proceed if newvalue present in request. prevents overwriting for non-existent values.
                $row->load( array('config_name'=>$key) );
                $row->config_name = $key;
                $row->value = $newvalue;
                
                if ( !$row->save() ) 
                {
                    $error = true;
                    $errorMsg .= JText::_('COM_TIENDA_COULD_NOT_STORE')." $key :: ".$row->getError()." - ";   
                }
            }
        }
        
        if ( !$error ) 
        {
            $this->messagetype  = 'message';
            $this->message      = JText::_('COM_TIENDA_SAVED');
            
            $dispatcher = JDispatcher::getInstance();
            $dispatcher->trigger( 'onAfterSave'.$this->get('suffix'), array( $row ) );
        } 
            else 
        {
            $this->messagetype  = 'notice';         
            $this->message      = JText::_('COM_TIENDA_SAVE_FAILED')." - ".$errorMsg;
        }
        
        $redirect = "index.php?option=com_tienda";
        $task = JRequest::getVar('task');
        switch ($task)
        {
            default:
                $redirect .= "&view=".$this->get('suffix');
              break;
        }

        $redirect = JRoute::_( $redirect, false );
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
    
}

?>