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

class TiendaViewManufacturers extends TiendaViewBase 
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
    
	/**
	 * (non-PHPdoc)
	 * @see tienda/admin/views/TiendaViewBase#_defaultToolbar()
	 */
	function _defaultToolbar()
	{
		JToolBarHelper::publishList( 'manufacturer_enabled.enable' );
		JToolBarHelper::unpublishList( 'manufacturer_enabled.disable' );
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
        	JToolBarHelper::custom('save_as', 'refresh', 'refresh', JText::_( 'Save As' ), false);
    	}
        parent::_formToolbar($isNew);
    }	
}
