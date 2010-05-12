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

/** Import library dependencies */
jimport('joomla.event.plugin');

class plgK2Tienda extends JPlugin 
{   
    function plgK2Tienda(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }
    
    /**
     * Checks the extension is installed
     * 
     * @return boolean
     */
    function _isInstalled()
    {
        $success = false;
        
        jimport('joomla.filesystem.file');
        if (JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'defines.php')) 
        {
            $success = true;
            if ( !class_exists('Tienda') ) 
                JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
        }
        return $success;
    }
    
    /**
     * Method for after saving a K2 Item.  Probably needs to be renamed
     * 
     * @return unknown_type
     */
    function onAfterSaveK2Item()
    {
        if (!$this->_isInstalled())
        {
            return null;
        }
        
        // do something
        return $something;
    }
    
    /**
     * Method for after displaying a K2 Item.  Probably needs to be renamed
     * 
     * @return unknown_type
     */
    function onAfterDisplayK2Item()
    {
        if (!$this->_isInstalled())
        {
            return null;
        }
        
        // do something
        return $something;
    }
}
?>