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

Tienda::load( 'TiendaViewBase', 'views._base' );

class TiendaViewUsers extends TiendaViewBase 
{
	/**
     * 
     * @param $tpl
     * @return unknown_type
     */
    function getLayoutVars($tpl=null) 
    {
        $layout = $this->getLayout();
        switch(strtolower($layout))
        {
            case "form":
                JRequest::setVar('hidemainmenu', '1');
                $this->_form($tpl);
              break;
            case "view";
            	$this->_form($tpl);
            case "default":
            default:
                $this->set( 'leftMenu', 'leftmenu_users' );
                $this->_default($tpl);
              break;
        }
    }
    
	function _default($tpl=null)
	{
		Tienda::load( 'TiendaUrl', 'library.url' );
		parent::_default($tpl);
	}
	
	function _defaultToolbar()
	{
	}
}
