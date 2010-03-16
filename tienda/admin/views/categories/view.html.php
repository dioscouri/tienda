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

JLoader::import( 'com_tienda.views._base', JPATH_ADMINISTRATOR.DS.'components' );

class TiendaViewCategories extends TiendaViewBase 
{
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
	 * @see tienda/admin/views/TiendaViewBase#_formToolbar($isNew)
	 */
    function _formToolbar( $isNew=null )
    {
        $divider = false;
        $model = $this->getModel();
        JLoader::import( 'com_tienda.helpers.category', JPATH_ADMINISTRATOR.DS.'components' );
        $surrounding = TiendaHelperCategory::getSurrounding( $model->getId() );
        if (!empty($surrounding['prev']))
        {
            $divider = true;
            JToolBarHelper::custom('saveprev', "saveprev", "saveprev", JText::_( 'Save + Prev' ), false);
        }
        if (!empty($surrounding['next']))
        {
            $divider = true;
            JToolBarHelper::custom('savenext', "savenext", "savenext", JText::_( 'Save + Next' ), false);  
        }
        if ($divider)
        {
            JToolBarHelper::divider();
        }
        parent::_formToolbar($isNew);
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
