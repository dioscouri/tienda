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

class TiendaViewCountries extends TiendaViewBase 
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
			case "view":
				$this->_form($tpl);
			  break;
			case "form":
				JRequest::setVar('hidemainmenu', '1');
				$this->_form($tpl);
			  break;
			case "default":
			default:
				$this->set( 'leftMenu', 'leftmenu_localization' );
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
        JToolBarHelper::publishList( 'country_enabled.enable' );
        JToolBarHelper::unpublishList( 'country_enabled.disable' );
        JToolBarHelper::divider();
        parent::_defaultToolbar();
    }
    
	function _default($tpl=null)
	{		
		$model = $this->getModel();
		$model->setState( 'order', 'tbl.ordering' );
		$model->setState( 'direction', 'ASC' );		
		
		parent::_default($tpl);
	}
}
