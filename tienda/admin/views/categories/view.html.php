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

class TiendaViewCategories extends TiendaViewBase 
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
            case "default":
            default:
                $this->set( 'leftMenu', 'leftmenu_catalog' );
                $this->_default($tpl);
              break;
        }
    }
    
	function _form($tpl=null)
	{
		parent::_form($tpl);
		
		$model = $this->getModel();
		
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'onGetCategoryView', array( $model->getItem()  ) );
		
		$shippingHtml = implode('<hr />', $results);
        
		$this->assign('shippingHtml', $shippingHtml);
	}
	
    /**
     * (non-PHPdoc)
     * @see tienda/admin/views/TiendaViewBase#_defaultToolbar()
     */
	function _defaultToolbar()
	{
		JToolBarHelper::custom('rebuild', 'refresh', 'refresh', JText::_( 'Rebuild Tree' ), false);
		JToolBarHelper::publishList( 'category_enabled.enable' );
		JToolBarHelper::unpublishList( 'category_enabled.disable' );
		JToolBarHelper::divider();
		parent::_defaultToolbar();
	}
}
