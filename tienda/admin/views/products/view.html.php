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

class TiendaViewProducts extends TiendaViewBase 
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
            case "gallery":
            case "setquantities":
            case "selectcategories":
                $this->_default($tpl);
              break;
            case "view":
                $this->_form($tpl);
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
		$item = $model->getItem();
		if (empty($item->product_id))
		{
		    // this is a new product
		    $item = JTable::getInstance('Products', 'TiendaTable');
            $item->product_parameters = new JParameter( $item->product_params );
            $this->assign( 'row', $item );
		}
		
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'onGetProductView', array( $model->getItem() ) );
		
		$shippingHtml = implode('<hr />', $results);
        
		$this->assign('shippingHtml', $shippingHtml);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/views/TiendaViewBase#_defaultToolbar()
	 */
	function _defaultToolbar()
	{
		JToolBarHelper::publishList( 'product_enabled.enable' );
		JToolBarHelper::unpublishList( 'product_enabled.disable' );
		JToolBarHelper::divider();
		parent::_defaultToolbar();
	}

	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/views/TiendaViewBase#_formToolbar($isNew)
	 */
    function _formToolbar( $isNew=null )
    {
    	$model = $this->getModel();
    	if ($model->getId())
    	{
    	    JToolBarHelper::custom( 'view', 'edit', 'edit', JText::_( 'Dashboard' ), false);
            JToolBarHelper::divider();	
    	}
        parent::_formToolbar($isNew);
    }
    
    /**
     * (non-PHPdoc)
     * @see tienda/admin/views/TiendaViewBase#_viewToolbar($isNew)
     */
	function _viewToolbar( $isNew=null )
	{
        JToolBarHelper::custom( 'edit', 'edit', 'edit', JText::_( 'Edit' ), false);
        JToolBarHelper::divider();
        parent::_viewToolbar($isNew);
	}
}
