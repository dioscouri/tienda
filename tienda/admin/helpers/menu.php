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
    function isSubmenuEnabled()
    {
        $query = "SELECT `published` FROM #__modules WHERE `module` = 'mod_tienda_admin_submenu';";
        $db = JFactory::getDBO();
        $db->setQuery( $query );
        $result = $db->loadResult();
        return $result;
    }
    
    function display( $menu='submenu' )
    {
        if (!$this->isSubmenuEnabled())
        {
            $menu =& TiendaMenu::getInstance();
        }
            else
        {
            JRequest::setVar('tienda_display_submenu', '1'); // tells the tienda_admin_submenu module to display
        }
    }
}