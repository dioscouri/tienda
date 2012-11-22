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
        $config = Tienda::getInstance();
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
        
        $model->clearCache();
        
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
        
        $redirect = "index.php?option=com_tienda&view=".$this->get('suffix');
        $group = JRequest::getVar('group');
        switch ($group)
        {
            default:
                if ($group) {
                    $redirect .= "&task=" . $group;
                }                
                break;
        }

        $format = JRequest::getVar('format');
        if ($format == 'raw') 
        {
            $response = array();
            $response['error'] = $error;
            $response['msg'] = $this->message;
            echo json_encode($response);
            return;
        }
        
        $redirect = JRoute::_( $redirect, false );
        $this->setRedirect( $redirect, $this->message, $this->messagetype );
    }
    
    public function all($cachable=false, $urlparams = false) 
    {
        JRequest::setVar('layout', 'all');        
        parent::display($cachable, $urlparams);
    }
    
    public function displaysettings($cachable=false, $urlparams = false)
    {
        JRequest::setVar('layout', 'displaysettings');
        parent::display($cachable, $urlparams);
    }
    
    public function orders($cachable=false, $urlparams = false)
    {
        JRequest::setVar('layout', 'orders');
        parent::display($cachable, $urlparams);
    }
    
    public function products($cachable=false, $urlparams = false)
    {
        JRequest::setVar('layout', 'products');
        parent::display($cachable, $urlparams);
    }
    
    public function emails($cachable=false, $urlparams = false)
    {
        JRequest::setVar('layout', 'emails');
        parent::display($cachable, $urlparams);
    }
    
    public function admin($cachable=false, $urlparams = false)
    {
        JRequest::setVar('layout', 'admin');
        parent::display($cachable, $urlparams);
    }
    
    public function advanced($cachable=false, $urlparams = false)
    {
        JRequest::setVar('layout', 'advanced');
        parent::display($cachable, $urlparams);
    }
    
}

?>