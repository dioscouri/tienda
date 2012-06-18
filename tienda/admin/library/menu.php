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
defined('_JEXEC') or die('Restricted access');


class TiendaMenu extends DSCMenu
{
    public $_name;
    public $_menu;
    
   
    
    /**
     * 
     * @param string $name
     * @return mixed
     * 
     * Returns a reference to a TiendaMenu object or false if submenus have been disabled by an admin
     */
  /*  function & getInstance($name = 'submenu')
    {
    	//refactor as it causes a php notice "Only variable references should be returned by reference" in PHP5
        // Check the config to see if the admin has disabled submenus
    
  
    
        //if (!Tienda::getInstance()->get('display_submenu', '1')) 
		//{
		//    return false;
		//}
				
        static $instances;
        
        if (!isset($instances)) 
        {
            $instances = array();
        }
        
		$display = true;
		//TODO: are we going to add it in the config view?
		if (!Tienda::getInstance()->get('display_submenu', '1')) 
		{
		    $display = false;
		}
		
		$app = JFactory::getApplication();		
		if (!$app->isAdmin() && !Tienda::getInstance()->get('show_submenu_fe', '1')) 
		{
		    $display = false;
		}
						
        if (empty ($instances[$name])) 
        {
            $instances[$name] = $display ? new TiendaMenu($name) : '';
        }
		        
        return $instances[$name];
    }*/
    
	
	
	/**
	 * Displays the menu according to view.
	 * 
	 * @return unknown_type
	 */
	/*function display($layout='submenu', $hidemainmenu='')
	{
	    jimport( 'joomla.application.component.view' );
	    
	    // TODO This should be passed as an argument
		$hide = JRequest::getInt('hidemainmenu');
        		
		// Load the named template, if there are links to display.				
		if (!empty($this->_menu->_bar)) 
		{
		    $view = new JView(array('name'=>'dashboard'));
		    $view->set('items', $this->_menu->_bar);
		    $view->set('name', 'COM_TIENDA_'.$this->_name);
		    $view->set('hide', $hide);
    		$view->setLayout($layout);
    		$view->display();		    
		}
	}*/
}