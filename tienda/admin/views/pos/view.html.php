<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaViewBase', 'views._base' );
JHTML::_('script', 'core.js', 'media/system/js/');

class TiendaViewPOS extends TiendaViewBase 
{
	/**
	 * Displays a layout file
	 *
	 * @param unknown_type $tpl
	 * @return unknown_type
	 */
	function display($tpl = null) 
	{
		parent::display($tpl);
	}
	
	
    function getLayoutVars($tpl=null) 
    {
        $layout = $this->getLayout();
        switch(strtolower($layout))
        {
            case "addproduct":
                $this->_default($tpl);
              break;
			case "ordersummary":
			case "payment_options":
			case "form_address":
			case "shipping":
			case "cart":
				break;
            case "view":
            case "form":
			case "default":		
            default:
                JRequest::setVar('hidemainmenu', '1');
                $this->_form($tpl);
              break;
        }
    }
	
    /**
     * Basic methods for displaying an item from a list
     * @param $tpl
     * @return unknown_type
     */
    function _form($tpl='')
    {
        $model = $this->getModel();
        $state = $model->getState();
        JFilterOutput::objectHTMLSafe( $state );
        $this->assign( 'state', $state );

        $row = $model->getTable();
        $this->assign('row', $row );
        
        $this->set( 'title', 'Create a New Order' );
        JToolBarHelper::cancel();
    }
	
	function _formToolbar($isNew = null) 
	{
		if ($isNew) {
			JToolBarHelper::cancel();
		} else {
			JToolBarHelper::cancel('close', 'COM_TIENDA_CLOSE');
		}
	}


	/**
	 * The default toolbar for viewing an item
	 * @param $isNew
	 * @return unknown_type
	 */
	function _viewToolbar($isNew = null) 
	{
		JToolBarHelper::cancel('close', JText::_('COM_TIENDA_CLOSE'));
	}
}