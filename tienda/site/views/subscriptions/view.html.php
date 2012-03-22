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

Tienda::load( 'TiendaViewBase', 'views._base', array( 'site'=>'site', 'type'=>'components', 'ext'=>'com_tienda' ) );

class TiendaViewSubscriptions extends TiendaViewBase 
{
    function getLayoutVars($tpl=null) 
    {
        $layout = $this->getLayout();
        switch(strtolower($layout))
        {
        	case "print":
            case "view":
                $this->_form($tpl);
              break;
            case "form":
                JRequest::setVar('hidemainmenu', '1');
                $this->_form($tpl);
              break;
            case "default":
            default:
                $this->set( 'leftMenu', 'leftmenu_orders' );
                $this->_default($tpl);
              break;
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/admin/views/TiendaViewBase#_viewToolbar($isNew)
     */
    function _viewToolbar( $isNew=null )
    {
        JToolBarHelper::custom( 'edit', 'edit', 'edit', JText::_('COM_TIENDA_EDIT'), false);
        JToolBarHelper::divider();
        parent::_viewToolbar($isNew);
    }
}
