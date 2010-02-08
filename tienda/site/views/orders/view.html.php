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

JLoader::import( 'com_tienda.views._base', JPATH_SITE.DS.'components' );

class TiendaViewOrders extends TiendaViewBase 
{
    /**
     * 
     * @param $tpl
     * @return unknown_type
     */
    function display($tpl=null) 
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
        parent::display($tpl);
    }
    
    /**
     * Basic methods for a form
     * @param $tpl
     * @return unknown_type
     */
    function _form($tpl='')
    {
        parent::_form($tpl);
        // TODO Would need this fixed to add prev/next buttons when viewing orders
        // helper method doesn't properly set filter_userid, so returned prev/next records are invalid
//        $model = $this->getModel();
//        JLoader::import( 'com_tienda.helpers.order', JPATH_ADMINISTRATOR.DS.'components' );
//        $surrounding = TiendaHelperOrder::getSurrounding( $model->getId() );
//        $this->assign('surrounding', $surrounding);	
    }
}
