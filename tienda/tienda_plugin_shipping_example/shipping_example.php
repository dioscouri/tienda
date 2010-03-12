<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Daniele Rosario
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

JLoader::import( 'com_tienda.library.plugins.shipping', JPATH_ADMINISTRATOR.DS.'components' );

class plgTiendaShipping_Example extends TiendaShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'shipping_example';
    
    
	function plgTiendaTool_shipping_example(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}
	
    /**
     * Overriding 
     * 
     * @param $options
     * @return unknown_type
     */
    function onGetShippingView( $row )
    {
        if (!$this->_isMe($row)) 
        {
            return null;
        }
        $task = $this->getShippingTask();
        $html = $this->_processTask( $task );       

        return $html;
    }
    
 /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     * 
     * @param $task
     * @return html
     */
    function _processTask( $task ='list' )
    {
        $html = "";
        
        switch($task)
        {
        	case "save":
        		$values = JRequest::get('post');
        		
        		if($this->_save($values)){
        			$vars = new JObject();
        			$html .= $this->_renderView($vars, 'messageok');
        		}
        		else{
        			$vars = new JObject();
        			$html .= $this->_renderView($vars, 'messagefailed');
        		}
        		break;
        		
        	case "view":
        		$sid = $this->getShippingId();
                $this->includeCustomModel('ShippingMethods');
		
		        $model = JModel::getInstance('ShippingMethods', 'TiendaModel');
		        $model->setId((int)$sid);
		        
		        $item = $model->getItem();
		        
		        $vars = new JObject();
		        $vars->item = $item;
		        
		        $validate = JUtility::getToken();
				$form = array();
				$view = JRequest::getVar('view');
				$task = JRequest::getVar('task');
				$id = JRequest::getVar('id');
				$sid = JRequest::getVar('sid');
				$form['action'] = "index.php?option=com_tienda&view={$view}&task={$task}&id={$id}&shippingTask=save";
				
				$vars->form = $form;
		        
		        $html .= $this->_renderView($vars, 'form');
		        
                break;
                
            case "list":
            default:
            	
            	$vars = new JObject();
		        $vars->state = $this->_getState();        
		        
		        /*
		        $this->includeCustomTables();
		        $table = JTable::getInstance('ShippingExample', 'TiendaTable');
		        */
		        $this->includeCustomModel('ShippingMethods');
		
		        $model = JModel::getInstance('ShippingMethods', 'TiendaModel');
		        $list = $model->getList();
		        
				$vars->list = $list;
				
                $html .= $this->_renderView($vars, 'default');
                
            
        }
        
        return $html;
    }
    
	
    /**
     * Prepares the 'view' tmpl layout
     *  
     * @return unknown_type
     */
    function _renderView( $vars, $view='default' )
    {
        $layout =  $view;
        $html = $this->_getLayout($layout, $vars);
        
        return $html;
    }

    /**
     * Gets the appropriate values from the request
     * 
     * @return unknown_type
     */
    function _getState()
    {
        $state = new JObject();
        
        foreach ($state->getProperties() as $key => $value)
        {
            $new_value = JRequest::getVar( $key );
            $value_exists = array_key_exists( $key, $_POST );
            if ( $value_exists && !empty($key) )
            {
                $state->$key = $new_value;
            }
        }
        return $state;
    }
    
    function _save($values){
    	// Include the custom table
    	$this->includeCustomTables();
    	$table = JTable::getInstance('ShippingMethods', 'TiendaTable');
    	
    	$table->bind($values);
    	
    	return $table->store($values);
    }
    
    function setRates(){
    	
    	JLoader::import( 'com_tienda.library.grid', JPATH_ADMINISTRATOR.DS.'components' );
    	JLoader::import( 'com_tienda.library.select', JPATH_ADMINISTRATOR.DS.'components' );
    	$this->includeCustomModel('ShippingRates');        
        $sid = JRequest::getVar('sid');
        
        $this->includeCustomTables();
        $row = JTable::getInstance('ShippingMethods', 'TiendaTable');
        $row->load($sid);
        
        $model = JModel::getInstance('ShippingRates', 'TiendaModel');
        $model->setState('filter_shippingmethod', $sid);
                
        $vars = new JObject();
        $vars->row = $row;
        $vars->items = $model->getList();
    	
    	echo $this->_renderView($vars, 'setrates');
    }
   
}
