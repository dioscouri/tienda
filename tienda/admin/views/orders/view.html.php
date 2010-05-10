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

class TiendaViewOrders extends TiendaViewBase 
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
                $this->_default($tpl);
              break;
        }
    }
    
    /**
     * The default toolbar for a list
     * @return unknown_type
     */
    function _defaultToolbar()
    {
        // JToolBarHelper::editList();
        //JToolBarHelper::deleteList( JText::_( 'VALIDDELETEITEMS' ) );
        //JToolBarHelper::addnew();
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/admin/views/TiendaViewBase#_viewToolbar($isNew)
     */
    function _viewToolbar( $isNew=null )
    {
        $divider = false;
        $model = $this->getModel();
        Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
        $surrounding = TiendaHelperOrder::getSurrounding( $model->getId() );
        if (!empty($surrounding['prev']))
        {
            $divider = true;
            JToolBarHelper::custom('prev', "prev", "prev", JText::_( 'Prev' ), false);
        }
        if (!empty($surrounding['next']))
        {
            $divider = true;
            JToolBarHelper::custom('next', "next", "next", JText::_( 'Next' ), false);  
        }
        if ($divider)
        {
            JToolBarHelper::divider();
        }
        $this->assign('surrounding', $surrounding);
        parent::_viewToolbar($isNew);
    }
}
