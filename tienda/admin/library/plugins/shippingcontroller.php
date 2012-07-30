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
defined('_JEXEC') or die('Restricted access');

class TiendaControllerShippingPlugin extends TiendaController {
		
	// the same as the plugin's one!
	var $_element = '';
		
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Overrides the getView method, adding the plugin's layout path
	 */
	public function getView( $name = '', $type = '', $prefix = '', $config = array() ){
    	$view = parent::getView( $name, $type, $prefix, $config ); 
		  if(version_compare(JVERSION,'1.6.0','ge')) { 
			   // Joomla! 1.6+ code
    	   $view->addTemplatePath(JPATH_SITE.'/plugins/tienda/'.$this->_element.'/'.$this->_element.'/tmpl/');
      }  
      else {
    	   $view->addTemplatePath(JPATH_SITE.'/plugins/tienda/'.$this->_element.'/tmpl/');      
      }  	
    	return $view;
    }
    
    /**
     * Overrides the delete method, to include the custom models and tables.
     */
    public function delete()
    {
    	$this->includeCustomModel('ShippingRates');
    	$this->includeCustomTables();
    	parent::delete();
    }
    
    protected function includeCustomTables(){
   		// Include the custom table
    	$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('includeCustomTables', array() );
    }   
    
    protected function includeCustomModel( $name ){
    	$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('includeCustomModel', array($name, $this->_element) );
    }       
	
    protected function includeTiendaModel( $name ){
    	$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('includeTiendaModel', array($name) );
    }

    protected function baseLink(){
    	$id = JRequest::getInt('id', '');
    	return "index.php?option=com_tienda&view=shipping&task=view&id={$id}";
    }
}