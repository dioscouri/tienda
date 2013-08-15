<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class TiendaHelperMenu extends TiendaHelperBase
{
    /**
     * Determines if the Tienda admin-side submenu module is enabled 
     * @return unknown_type
     */
    function isSubmenuEnabled()
    {
    	if(version_compare(JVERSION,'1.6.0','ge')) {
        $query = "SELECT `enabled` FROM #__extensions WHERE `element` = 'mod_tienda_admin_submenu';";
		} else {
		 $query = "SELECT `published` FROM #__modules WHERE `module` = 'mod_tienda_admin_submenu';";	
		}
        $db = JFactory::getDBO();
        $db->setQuery( $query );
        $result = $db->loadResult();
        return $result;
		
    }
    
    /**
     * Whether using the admin-side submenu module or just using the menu tmpl (from the dashboard view),
     * tells Tienda to display the submenu
     * 
     * @param $menu
     * @return unknown_type
     */
    function display( $menu_name='submenu' )
    {
        if (!$this->isSubmenuEnabled())
        {
            $menu = TiendaMenu::getInstance( $menu_name );
        }
            else
        {
            JRequest::setVar('tienda_display_submenu', '1'); // tells the tienda_admin_submenu module to display
        }
    }
}