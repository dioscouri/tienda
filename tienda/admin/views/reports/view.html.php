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

class TiendaViewReports extends TiendaViewBase 
{
    function getLayoutVars($tpl=null) 
    {
        $layout = $this->getLayout();
        switch(strtolower($layout))
        {
        	case "view":
            case "form":
                JRequest::setVar('hidemainmenu', '1');
                $this->_form($tpl);
              break;
            case "default":
            default:
                $this->set( 'leftMenu', 'leftmenu_tools' );
                $this->_default($tpl);
              break;
        }
    }
    
	function _form($tpl=null)
	{  
        JHTML::_('script', 'bootstrapped-advanced-ui.js', 'media/dioscouri/js/');
        JHTML::_('stylesheet', 'bootstrapped-advanced-ui.css', 'media/dioscouri/css/');
        JHTML::_('stylesheet', 'reports.css', 'media/com_tienda/css/');
        parent::_form($tpl);
        
        // load the plugin
		$row = $this->getModel()->getItem();
		$import = JPluginHelper::importPlugin( 'tienda', $row->element );
	}
	
	function _defaultToolbar()
	{
	}
	
	function _viewToolbar($isNew = null)
	{
		JToolBarHelper::custom( 'view', 'forward', 'forward', 'COM_TIENDA_SUBMIT', false );
		JToolBarHelper::cancel( 'close', 'COM_TIENDA_CLOSE' );
	}
}
