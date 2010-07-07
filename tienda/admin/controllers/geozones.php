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

        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', 'int');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', 'int');
        $state['filter_name']       = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', 'string');
        $state['filter_geozonetype'] = $app->getUserStateFromRequest($ns.'geozonetype', 'filter_geozonetype', '', 'string');
        
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
                $this->message      = JText::_( "Invalid Task" );
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
            $this->message = JText::_('Error') . ": " . $this->message;
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
}

?>