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
            case "selectproducts":
                $this->_default($tpl);
              break;
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
		if( !isset( $this->row->display_name_subcategory ) )
			$this->row->display_name_subcategory = 1;
		if( !isset( $this->row->display_name_category ) )
			$this->row->display_name_category = 1;
        
		$this->assign('shippingHtml', $shippingHtml);
	}
	
    /**
     * (non-PHPdoc)
     * @see tienda/admin/views/TiendaViewBase#_defaultToolbar()
     */
	function _defaultToolbar()
	{
		JToolBarHelper::custom('rebuild', 'refresh', 'refresh', JText::_('COM_TIENDA_REBUILD_TREE'), false);
		JToolBarHelper::publishList( 'category_enabled.enable' );
		JToolBarHelper::unpublishList( 'category_enabled.disable' );
		JToolBarHelper::divider();
		parent::_defaultToolbar();
	}
	
/**
	 * (non-PHPdoc)
	 * @see tienda/admin/views/TiendaViewBase#_formToolbar($isNew)
	 */
    function _formToolbar( $isNew=null )
    {
    	if (!$isNew)
    	{
        	JToolBarHelper::custom('save_as', 'refresh', 'refresh', JText::_('COM_TIENDA_SAVE_AS'), false);
    	}
        parent::_formToolbar($isNew);
    }
	
}
